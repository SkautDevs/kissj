# Kissj Azure Infra
## Deploy this potato stack 

To deploy the infrastructure, follow the below steps.

### Prerequisites

1. [Install Pulumi](https://www.pulumi.com/docs/get-started/install/)
1. [Configure Azure Credentials](https://www.pulumi.com/docs/intro/cloud-providers/azure/setup/)

### Steps
1. Create a new stack, which is an isolated deployment target:

    ```bash
    $ pulumi stack init production
    ```
1. Setup the config to use:
    
    ```bash
    $ pulumi config set azure-native:location germanywestcentral
   $ pulumi config set kissj:kubernetesVersion: 1.27.7
   $ pulumi config set kissj:nodeVmSize: Standard_B8pls_v2
   $ pulumi config set kissj:numWorkerNodes: "2"
   $ pulumi config set kissj:dbVmSize: Standard_B1ms
   $ pulumi config setkissj:dbTier: Burstable
    ```
   or copy it from the example(Pulumi.production.yaml.example) to Pulumi.production.yaml and run:
   
2. ```bash

1. Stand up the cluster by invoking pulumi
    ```bash
    $ pulumi up
    ```

1. After 10 minutes, Azure will burn down, and we will have our infrastructure. 
You get and save the kubeconfig yaml to a file like so:

    ```bash
    $ pulumi stack output kubeconfig --show-secrets > kubeconfig.yaml
    ```

    Once you have this file in hand, you can interact with your new cluster as usual via `kubectl`:

    ```bash
    $ KUBECONFIG=./kubeconfig.yaml kubectl get nodes
    ```

1. Tear it all down by destroying resources and removing the stack:

    ```bash
    $ pulumi destroy --yes
    $ pulumi stack rm --yes
    ```
