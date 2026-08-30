<?php

declare(strict_types=1);

namespace kissj\Middleware;

class TicketTransferCsrfMiddleware extends CsrfMiddleware
{
    #[\Override]
    protected function getFailureRedirectRouteName(): string
    {
        return 'showTransferTicket';
    }
}
