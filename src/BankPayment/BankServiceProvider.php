<?php

declare(strict_types=1);

namespace kissj\BankPayment;

use Psr\Container\ContainerInterface;

class BankServiceProvider
{
    public function __construct(
        private readonly Banks $banks,
        private readonly ContainerInterface $container,
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
