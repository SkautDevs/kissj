<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Cej;

use kissj\Deal\EventDeal;
use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\Event\Event;
use kissj\Event\EventType\EventType;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Payment\Payment;

class EventTypeCej extends EventType
{
    public const string CONTINGENT_CZECHIA = 'detail.contingent.czechia';
    public const string CONTINGENT_SLOVAKIA = 'detail.contingent.slovakia';
    public const string CONTINGENT_POLAND = 'detail.contingent.poland';
    public const string CONTINGENT_HUNGARY = 'detail.contingent.hungary';
    public const string CONTINGENT_EUROPEAN = 'detail.contingent.european';
    public const string CONTINGENT_ROMANIA = 'detail.contingent.romania';
    public const string CONTINGENT_ISRAEL = 'detail.contingent.israel';
    public const string CONTINGENT_BRITAIN = 'detail.contingent.britain';
    public const string CONTINGENT_SWEDEN = 'detail.contingent.sweden';
    public const string CONTINGENT_TEAM = 'detail.contingent.team';

    public function transformPayment(Payment $payment, Participant $participant): Payment
    {
        if ($participant->contingent === self::CONTINGENT_CZECHIA) {
            $payment->accountNumber = '2302084720/2010';
            $payment->iban = 'CZ3120100000002302084720';
            $payment->swift = 'FIOBCZPPXXX';
            $payment->price = (string)$this->getPriceForCzechia($participant);
            $payment->currency = 'Kč';
            $payment->variableSymbol = 42438 . substr($payment->variableSymbol, 5);
            $payment->constantSymbol = '';

            return $payment;
        }

        $payment->price = (string)$this->getPrice($participant);
        $payment->swift = 'TATRSKBX';
        $payment->constantSymbol = '0558';

        return $payment;
    }

    protected function getPrice(Participant $participant): int
    {
        if ($participant->contingent === self::CONTINGENT_TEAM) {
            return 150;
        }

        $price = match (true) {
            $participant instanceof PatrolLeader => ($participant->getPatrolParticipantsCount() * 250) + 250,
            $participant instanceof Ist => 150,
            default => throw new \Exception('Unknown participant class'),
        };

        return $price;
    }

    private function getPriceForCzechia(Participant $participant): int
    {
        return match (true) {
            $participant instanceof PatrolLeader => ($participant->getPatrolParticipantsCount() + 1) * 6600,
            $participant instanceof Ist => 4100,
            default => throw new \Exception('Unknown participant class'),
        };
    }

    public function getMaximumClosedParticipants(Participant $participant): int
    {
        if ($participant instanceof PatrolLeader) {
            return match ($participant->contingent) {
                self::CONTINGENT_CZECHIA => 35,
                self::CONTINGENT_SLOVAKIA => 25,
                self::CONTINGENT_POLAND => 6,
                self::CONTINGENT_HUNGARY => 10,
                self::CONTINGENT_EUROPEAN => 13,
                self::CONTINGENT_BRITAIN => 9,
                self::CONTINGENT_SWEDEN => 10,
                default => 0,
            };
        }

        return parent::getMaximumClosedParticipants($participant);
    }

    /**
     * @inheritDoc
     */
    public function getTranslationFilePaths(): array
    {
        return [
            'en' => __DIR__ . '/en_cej.yaml',
        ];
    }

    public function getContentArbiterIst(): ContentArbiterIst
    {
        $ca = parent::getContentArbiterIst();
        $ca->contingent = true;
        $ca->country = true;
        $ca->idNumber = true;
        $ca->languages = true;
        $ca->food = true;
        $ca->phone = true;
        $ca->email = true;
        $ca->swimming = true;
        $ca->tshirt = true;
        $ca->skills = true;
        $ca->unit = true;

        return $ca;
    }

    public function getContentArbiterPatrolLeader(): ContentArbiterPatrolLeader
    {
        $ca = parent::getContentArbiterPatrolLeader();
        $ca->contingent = true;
        $ca->country = true;
        $ca->idNumber = true;
        $ca->languages = true;
        $ca->food = true;
        $ca->phone = true;
        $ca->email = true;
        $ca->swimming = true;
        $ca->unit = true;

        return $ca;
    }

    public function getContentArbiterPatrolParticipant(): ContentArbiterPatrolParticipant
    {
        $ca = parent::getContentArbiterPatrolParticipant();
        $ca->country = true;
        $ca->idNumber = true;
        $ca->languages = true;
        $ca->food = true;
        $ca->phone = true;
        $ca->email = true;
        $ca->swimming = true;
        $ca->unit = true;
        $ca->uploadFile = true;

        return $ca;
    }

    /**
     * @inheritDoc
     */
    public function getFoodOptions(): array
    {
        return [
            'detail.foodWithout',
            'detail.foodVegetarian',
            'detail.foodVegan',
            'detail.foodLactoseFree',
            'detail.foodGlutenFree',
            'detail.foodLactoseAndGlutenFree',
            'detail.foodGlutenFreeVegetarian',
            'detail.foodOther',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getParticipantCountries(): array
    {
        return [
            'detail.countrySlovakia',
            'detail.countryCzechRepublic',
            'detail.countryPoland',
            'detail.countryHungary',
            'detail.countryOther',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getContingents(): array
    {
        return [
            self::CONTINGENT_SLOVAKIA,
            self::CONTINGENT_CZECHIA,
            self::CONTINGENT_HUNGARY,
            self::CONTINGENT_POLAND,
            self::CONTINGENT_EUROPEAN,
            self::CONTINGENT_BRITAIN,
            self::CONTINGENT_SWEDEN,
            self::CONTINGENT_TEAM,
        ];
    }

    public function showContingentPatrolStats(): bool
    {
        return true;
    }

    public function getStylesheetNameWithoutLeadingSlash(): string
    {
        return 'eventSpecificCss/stylesCej24.css';
    }

    /**
     * @inheritDoc
     */
    public function getLanguages(): array
    {
        return [
            'en' => '🇬🇧 English',
        ];
    }

    public function isReceiptAllowed(): bool
    {
        return true;
    }

    public function getReceiptTemplateName(Participant $participant): string
    {
        return match ($participant->contingent) {
            self::CONTINGENT_CZECHIA => 'receipt/receiptCejCs.twig',
            default => 'receipt/receiptCejSk.twig',
        };
    }

    public function getMinimalPpCount(Event $event, Participant $participant): int
    {
        return match ($participant->contingent) {
            self::CONTINGENT_CZECHIA,
            self::CONTINGENT_SLOVAKIA,
            self::CONTINGENT_POLAND,
            => 9,
            self::CONTINGENT_HUNGARY,
            => 6,

            default => $event->minimalPatrolParticipantsCount ?? 0,
        };
    }

    public function getMaximalPpCount(Event $param, Participant $participant): int
    {
        return match ($participant->contingent) {
            self::CONTINGENT_HUNGARY => 11,
            default => 10,
        };
    }

    public function showIban(): bool
    {
        return true;
    }

    public function getSkautLogoPath(Participant $participant): string
    {
        return match ($participant->contingent) {
            self::CONTINGENT_CZECHIA => parent::getSkautLogoPath($participant),
            default => '/ZNAK_EN_Scouting_Slovakia_500.png',
        };
    }

    public function getSkautStampSignPath(Participant $participant): string
    {
        return match ($participant->contingent) {
            self::CONTINGENT_CZECHIA => parent::getSkautStampSignPath($participant),
            default => '/SkSkautingSignStamp.png',
        };
    }

    #[\Override]
    public function getEventDeals(Participant $participant): array
    {
        $eventDeals = [];

        if ($participant instanceof Ist) {
            $eventDeals[] = new EventDeal(
                self::SLUG_SFH,
                sprintf(
                    'https://docs.google.com/forms/d/e/1FAIpQLSe3FPRiN7o9nupa-sLs0w6xf6IFFEW5kJVMJSPSExfHn0qCtw/viewform?usp=pp_url&entry.499691302=%s',
                    $participant->tieCode,
                ),
            );
        }

        return $eventDeals;
    }
}
