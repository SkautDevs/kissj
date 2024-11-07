<?php

namespace kissj\Application;

use DI\Container;
use kissj\Command\UpdatePaymentsCommand;
use Symfony\Component\Console\Application;

readonly class Command
{
    public function addCommandsInto(Application $app, Container $container): Application
    {
        /** @var UpdatePaymentsCommand $updatePaymentsCommand */
        $updatePaymentsCommand = $container->get(UpdatePaymentsCommand::class);
        $app->add($updatePaymentsCommand);

        return $app;
    }
}
