<?php

declare(strict_types=1);

namespace kissj\Telemetry;

enum MetricName: string
{
    case EmailsSent = 'emails.sent';

    case PairingsApproved = 'pairings.approved';
    case PairingsDenied = 'pairings.denied';
    case PairingsPurged = 'pairings.purged';
    case PairingsRefused = 'pairings.refused';
    case PairingsStarted = 'pairings.started';

    case ParticipantsAdminEdited = 'participants.admin_edited';

    case PaymentsBankFetchFailed = 'payments.bank_fetch_failed';
    case PaymentsCancelled = 'payments.cancelled';
    case PaymentsConfirmed = 'payments.confirmed';
    case PaymentsMarkedPaired = 'payments.marked_paired';
    case PaymentsMarkedUnrelated = 'payments.marked_unrelated';
    case PaymentsMatched = 'payments.matched';
    case PaymentsPriceChanged = 'payments.price_changed';
    case PaymentsTransferred = 'payments.transferred';

    case PdfsGenerated = 'pdfs.generated';
    case PdfsGenerationTime = 'pdfs.generation_time';

    case ProgrammesRegistered = 'programmes.registered';
    case ProgrammesRegistrationRefused = 'programmes.registration_refused';
    case ProgrammesUnregistered = 'programmes.unregistered';

    case RegistrationsCreated = 'registrations.created';
    case RegistrationsDenied = 'registrations.denied';
    case RegistrationsEditedAfterLock = 'registrations.edited_after_lock';
    case RegistrationsLocked = 'registrations.locked';
}
