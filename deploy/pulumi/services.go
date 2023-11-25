package main

import (
	"fmt"
	"github.com/pulumi/pulumi-azure-native-sdk/containerservice/v2"
	"github.com/pulumi/pulumi-kubernetes/sdk/v4/go/kubernetes"
	"github.com/pulumi/pulumi-kubernetes/sdk/v4/go/kubernetes/apiextensions"
	corev1 "github.com/pulumi/pulumi-kubernetes/sdk/v4/go/kubernetes/core/v1"
	"github.com/pulumi/pulumi-kubernetes/sdk/v4/go/kubernetes/helm/v3"
	v1 "github.com/pulumi/pulumi-kubernetes/sdk/v4/go/kubernetes/meta/v1"
	"github.com/pulumi/pulumi/sdk/v3/go/pulumi"
)

func createK8sProvider(ctx *pulumi.Context, kubeconfig pulumi.StringOutput, aksCluster *containerservice.ManagedCluster) pulumi.Output {
	return pulumi.All(kubeconfig, aksCluster.URN()).ApplyT(func(args []interface{}) (*kubernetes.Provider, error) {
		kcfg := args[0].(string)
		return kubernetes.NewProvider(ctx, "k8sProvider", &kubernetes.ProviderArgs{
			Kubeconfig: pulumi.StringPtr(kcfg),
		})
	}).(pulumi.Output)
}

func services(ctx *pulumi.Context, aksCluster *containerservice.ManagedCluster, kubeconfig pulumi.StringOutput) error {

	k8sProvider := createK8sProvider(ctx, kubeconfig, aksCluster)

	k8sProvider.ApplyT(func(provider *kubernetes.Provider) error {
		// Deploy haproxy ingress chart with Helm
		haproxyChartArgs := helm.ReleaseArgs{
			Name:            pulumi.String("haproxy-ingress-controller"),
			Chart:           pulumi.String("kubernetes-ingress"),
			Namespace:       pulumi.String("haproxy-controller"),
			CreateNamespace: pulumi.Bool(true),
			RepositoryOpts: helm.RepositoryOptsArgs{
				Repo: pulumi.String("https://haproxytech.github.io/helm-charts"),
			},
			Version: pulumi.String("1.35.3"),
			Values: pulumi.Map{
				"controller": pulumi.Map{
					"kind": pulumi.String("DaemonSet"),
					"ingressClassResource": pulumi.Map{
						"default": pulumi.Bool(true),
					},
					"service": pulumi.Map{
						"type": pulumi.String("LoadBalancer"),
						"annotations": pulumi.Map{
							"service.beta.kubernetes.io/azure-load-balancer-health-probe-request-path": pulumi.String("/healthz"),
						},
					},
				},
			},
		}

		haproxyChart, err := helm.NewRelease(ctx, "haproxy-ingress-controller", &haproxyChartArgs, pulumi.Provider(provider))
		if err != nil {
			return err
		}

		haproxyChart.ID().ApplyT(func(_ pulumi.ID) error {
			service, err := corev1.GetService(ctx, "haproxy-ingress-controller",
				pulumi.ID("haproxy-controller/haproxy-ingress-controller-kubernetes-ingress"), nil, pulumi.Provider(provider),
				pulumi.DependsOn([]pulumi.Resource{haproxyChart}))
			if err != nil {
				return err
			}
			service.Status.ApplyT(func(status *corev1.ServiceStatus) error {
				if status.LoadBalancer.Ingress != nil {
					ctx.Export("LoadBalancerIp", pulumi.String(*status.LoadBalancer.Ingress[0].Ip))
					fmt.Println(*status.LoadBalancer.Ingress[0].Ip)
				} else {
					ctx.Export("LoadBalancerIp", pulumi.String("Unknown, Check cluster"))
				}
				return nil
			})
			return nil
		})
		return nil
	})

	k8sProvider.ApplyT(func(provider *kubernetes.Provider) error {
		// Deploy cert manager chart with Helm
		certManagerChartArgs := helm.ReleaseArgs{
			Name:            pulumi.String("cert-manager"),
			Chart:           pulumi.String("cert-manager"),
			Namespace:       pulumi.String("cert-manager"),
			CreateNamespace: pulumi.Bool(true),
			RepositoryOpts: helm.RepositoryOptsArgs{
				Repo: pulumi.String("https://charts.jetstack.io"),
			},
			Version: pulumi.String("v1.13.2"),
			Values: pulumi.Map{
				"installCRDs": pulumi.Bool(true),
			},
		}

		certManagerChart, err := helm.NewRelease(ctx, "cert-manager", &certManagerChartArgs, pulumi.Provider(provider))
		if err != nil {
			return err
		}

		_, err = apiextensions.NewCustomResource(ctx, "letsencrypt-production",
			&apiextensions.CustomResourceArgs{
				ApiVersion: pulumi.String("cert-manager.io/v1"),
				Kind:       pulumi.String("ClusterIssuer"),
				Metadata: v1.ObjectMetaArgs{
					Name: pulumi.StringPtr("letsencrypt-production"),
				},
				OtherFields: kubernetes.UntypedArgs{
					"spec": pulumi.Map{
						"acme": pulumi.Map{
							"email":  pulumi.String("dev@kaplan.sh"),
							"server": pulumi.String("https://acme-v02.api.letsencrypt.org/directory"),
							"privateKeySecretRef": pulumi.Map{
								"name": pulumi.String("letsencrypt-production"),
							},
							"solvers": pulumi.Array{
								pulumi.Map{
									"http01": pulumi.Map{
										"ingress": pulumi.Map{
											"class": pulumi.String("haproxy"),
										},
									},
								},
							},
						},
					},
				},
			}, pulumi.Provider(provider), pulumi.DependsOn([]pulumi.Resource{certManagerChart}))
		if err != nil {
			return err
		}
		return nil
	})

	return nil

}
