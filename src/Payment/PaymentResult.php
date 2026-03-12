<?php

declare(strict_types=1);

namespace kissj\Payment;

readonly class PaymentResult
{
    /**
     * @param PaymentResultMessage[] $messages
     */
    public function __construct(
        public ?Payment $payment = null,
        public array $messages = [],
    ) {
    }

    public static function success(Payment $payment, string $messageKey): self
    {
        return new self($payment, [new PaymentResultMessage(PaymentMessageSeverity::Success, $messageKey)]);
    }

    public static function warning(Payment $payment, string $messageKey): self
    {
        return new self($payment, [new PaymentResultMessage(PaymentMessageSeverity::Warning, $messageKey)]);
    }

    /**
     * @param PaymentResultMessage[] $messages
     */
    public static function withMessages(array $messages): self
    {
        return new self(null, $messages);
    }
}
