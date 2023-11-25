package main

import (
	"github.com/pulumi/pulumi/sdk/v3/go/pulumi"
)

func main() {
	pulumi.Run(func(ctx *pulumi.Context) error {
		aksCluster, kubeconfig, err := aksCluster(ctx)
		if err != nil {
			return err
		}
		err = services(ctx, aksCluster, kubeconfig)
		if err != nil {
			return err
		}
		return nil
	})
}
