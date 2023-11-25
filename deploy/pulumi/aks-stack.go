package main

import (
	"encoding/base64"
	"fmt"
	"github.com/pulumi/pulumi-azure-native-sdk/containerservice/v2"
	"github.com/pulumi/pulumi-azure-native-sdk/dbforpostgresql/v2"
	"github.com/pulumi/pulumi-azure-native-sdk/operationalinsights/v2"
	"github.com/pulumi/pulumi-azure-native-sdk/resources/v2"
	"github.com/pulumi/pulumi-azuread/sdk/v4/go/azuread"
	"github.com/pulumi/pulumi-random/sdk/v4/go/random"
	"github.com/pulumi/pulumi-tls/sdk/v4/go/tls"
	"github.com/pulumi/pulumi/sdk/v3/go/pulumi"
	"github.com/pulumi/pulumi/sdk/v3/go/pulumi/config"
)

func aksCluster(ctx *pulumi.Context) (*containerservice.ManagedCluster, pulumi.StringOutput, error) {
	// Get some configuration values or set default values
	cfg := config.New(ctx, "")
	kubernetesVersion, err := cfg.Try("kubernetesVersion")
	if err != nil {
		kubernetesVersion = "1.27.7"
	}
	numWorkerNodes, err := cfg.TryInt("numWorkerNodes")
	if err != nil {
		numWorkerNodes = 3
	}
	nodeVmSize, err := cfg.Try("nodeVmSize")
	if err != nil {
		nodeVmSize = "Standard_B8pls_v2"
	}

	dbVmSize, err := cfg.Try("dbVmSize")
	if err != nil {
		nodeVmSize = "Standard_B1ms"
	}

	dbTier, err := cfg.Try("dbTier")
	if err != nil {
		dbTier = "Burstable"
	}

	prefix := fmt.Sprintf("%s-%s", ctx.Project(), ctx.Stack())

	// Create an Azure Resource Group
	resourceGroup, err := resources.NewResourceGroup(ctx, prefix, nil)
	if err != nil {
		return nil, pulumi.StringOutput{}, err
	}

	// Create an AD service principal.
	adApp, err := azuread.NewApplication(ctx, prefix+"-aks", &azuread.ApplicationArgs{
		DisplayName: pulumi.String(prefix + "-aks"),
	})
	if err != nil {
		return nil, pulumi.StringOutput{}, err
	}

	adSp, err := azuread.NewServicePrincipal(ctx, prefix+"-aks-SP", &azuread.ServicePrincipalArgs{
		ApplicationId: adApp.ApplicationId,
	})
	if err != nil {
		return nil, pulumi.StringOutput{}, err
	}

	// Create the Service Principal Password.
	adSpPassword, err := azuread.NewServicePrincipalPassword(ctx, prefix+"-aks-SP-Password", &azuread.ServicePrincipalPasswordArgs{
		ServicePrincipalId: adSp.ID(),
		EndDate:            pulumi.String("2099-01-01T00:00:00Z"),
	})
	if err != nil {
		return nil, pulumi.StringOutput{}, err
	}
	logAnalytics, err := operationalinsights.NewWorkspace(ctx, prefix+"-loganalytics", &operationalinsights.WorkspaceArgs{
		ResourceGroupName: resourceGroup.Name,
		RetentionInDays:   pulumi.Int(30),
	})
	if err != nil {
		return nil, pulumi.StringOutput{}, err
	}
	// Create an SSH public key that will be used by the cluster to authenticate with nodes.
	sshKey, err := tls.NewPrivateKey(ctx, "ssh-key", &tls.PrivateKeyArgs{
		Algorithm: pulumi.String("RSA"),
		RsaBits:   pulumi.Int(4096),
	})
	if err != nil {
		return nil, pulumi.StringOutput{}, err
	}

	pulumi.AdditionalSecretOutputs([]string{"privateKey"})
	ctx.Export("privatekey", sshKey.PrivateKeyOpenssh)

	// Create the Azure Kubernetes Service cluster.
	cluster, err := containerservice.NewManagedCluster(ctx, prefix+"-cluster", &containerservice.ManagedClusterArgs{
		ResourceGroupName: resourceGroup.Name,
		AgentPoolProfiles: containerservice.ManagedClusterAgentPoolProfileArray{
			&containerservice.ManagedClusterAgentPoolProfileArgs{
				Name:              pulumi.String("agentpool"),
				Mode:              pulumi.String("System"),
				OsDiskSizeGB:      pulumi.Int(30),
				Count:             pulumi.Int(numWorkerNodes),
				VmSize:            pulumi.String(nodeVmSize),
				OsType:            pulumi.String("Linux"),
				EnableAutoScaling: pulumi.Bool(true),
				MinCount:          pulumi.Int(numWorkerNodes),
				MaxCount:          pulumi.Int(8),
			},
		},
		LinuxProfile: &containerservice.ContainerServiceLinuxProfileArgs{
			AdminUsername: pulumi.String(prefix + "user"),
			Ssh: containerservice.ContainerServiceSshConfigurationArgs{
				PublicKeys: containerservice.ContainerServiceSshPublicKeyArray{
					containerservice.ContainerServiceSshPublicKeyArgs{
						KeyData: sshKey.PublicKeyOpenssh.ToStringOutput(),
					},
				},
			},
		},
		DnsPrefix: resourceGroup.Name,
		ServicePrincipalProfile: &containerservice.ManagedClusterServicePrincipalProfileArgs{
			ClientId: adApp.ApplicationId,
			Secret:   adSpPassword.Value,
		},
		KubernetesVersion: pulumi.String(kubernetesVersion),
		EnableRBAC:        pulumi.Bool(true),
		AddonProfiles: &containerservice.ManagedClusterAddonProfileMap{
			"omsagent": &containerservice.ManagedClusterAddonProfileArgs{
				Enabled: pulumi.Bool(true),
				Config: pulumi.StringMap{
					"loganalyticsworkspaceresourceid": pulumi.StringInput(logAnalytics.ID()),
				},
			},
		},
	})
	if err != nil {
		return nil, pulumi.StringOutput{}, err
	}

	creds := containerservice.ListManagedClusterUserCredentialsOutput(ctx,
		containerservice.ListManagedClusterUserCredentialsOutputArgs{
			ResourceGroupName: resourceGroup.Name,
			ResourceName:      cluster.Name,
		})

	kubeconfig := creds.Kubeconfigs().Index(pulumi.Int(0)).Value().
		ApplyT(func(encoded string) string {
			kubeconfig, err := base64.StdEncoding.DecodeString(encoded)
			if err != nil {
				return ""
			}
			ctx.Export("kubeconfig", pulumi.String(kubeconfig))
			return string(kubeconfig)
		}).(pulumi.StringOutput)
	pulumi.AdditionalSecretOutputs([]string{"kubeconfig"})
	ctx.Export("kubeconfig", kubeconfig)

	postgresPassword, err := random.NewRandomPassword(ctx, "postgresPassword", &random.RandomPasswordArgs{
		Length:  pulumi.Int(32),
		Special: pulumi.Bool(true),
	})
	if err != nil {
		return nil, pulumi.StringOutput{}, err
	}
	ctx.Export("postgresPassword", postgresPassword.Result)

	// Create an Azure PostgreSQL Server with a Burstable SKU Tier
	server, err := dbforpostgresql.NewServer(ctx, prefix+"-pgServer", &dbforpostgresql.ServerArgs{
		ServerName:        pulumi.String(prefix + "-pg"),
		ResourceGroupName: resourceGroup.Name,
		Location:          resourceGroup.Location,
		Sku: &dbforpostgresql.SkuArgs{
			Name: pulumi.String(dbVmSize),
			Tier: pulumi.String(dbTier),
		},
		Version: pulumi.String("14"),
		Storage: &dbforpostgresql.StorageArgs{
			StorageSizeGB: pulumi.Int(64),
		},
		Backup: &dbforpostgresql.BackupArgs{
			BackupRetentionDays: pulumi.Int(14),
			GeoRedundantBackup:  pulumi.String("Disabled"),
		},
		AdministratorLogin:         pulumi.String("kissj"),
		AdministratorLoginPassword: postgresPassword.Result,
	})
	if err != nil {
		return nil, pulumi.StringOutput{}, err
	}

	ctx.Export("postgresHost", server.FullyQualifiedDomainName)
	// Create a Database in the PostgreSQL Server
	_, err = dbforpostgresql.NewDatabase(ctx, prefix, &dbforpostgresql.DatabaseArgs{
		ResourceGroupName: resourceGroup.Name,
		ServerName:        server.Name,
		DatabaseName:      pulumi.String(prefix),
	})
	if err != nil {
		return nil, pulumi.StringOutput{}, err
	}

	return cluster, kubeconfig, nil
}
