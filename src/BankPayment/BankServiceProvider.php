<?php

declare(strict_types=1);

namespace kissj\BankPayment;

use Psr\Container\ContainerInterface;

readonly class BankServiceProvider
{
    public function __construct(
        private Banks $banks,
        private ContainerInterface $container,
    ) {
    }

    public function provideBankService(string $bankSlug): IBankPaymentService
    {
        /** @var IBankPaymentService $bankService */
        $bankService = $this->container->get(
            $this->banks->getBankBySlug($bankSlug)->serviceClass
        );

        return $bankService;
    }
}
