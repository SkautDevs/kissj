<?php

declare(strict_types=1);

namespace kissj\Payment;

enum PaymentSource: string
{
    case ManualAdmin = 'manual_admin';
    case ManualAdminApi = 'manual_admin_api';

    case AutoBankMatch = 'auto_bank_match';
    case AutoDue = 'auto_due';

    case ChangePrice = 'change_price';
    case Transfer = 'transfer';

    case Unknown = 'unknown';
}
