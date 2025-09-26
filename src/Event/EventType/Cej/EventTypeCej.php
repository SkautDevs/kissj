<?php

declare(strict_types=1);

namespace kissj\Event\EventType\Cej;

use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\Event\EventType\EventType;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Payment\Payment;
use kissj\User\UserRole;

class EventTypeCej extends EventType
{
    public const string CONTINGENT_CZECHIA = 'detail.contingent.czechia';
    public const string CONTINGENT_SLOVAKIA = 'detail.contingent.slovakia';
    public const string CONTINGENT_POLAND = 'detail.contingent.poland';
    public const string CONTINGENT_HUNGARY = 'detail.contingent.hungary';
    public const string CONTINGENT_EUROPEAN = 'detail.contingent.european';
    public const string CONTINGENT_ROMANIA = 'detail.contingent.romania';
    public const string CONTINGENT_TEAM = 'detail.contingent.team';

    public function transformPaymentPrice(Payment $payment, Participant $participant): Payment
    {
        return match ((string)$participant->contingent) {
            self::CONTINGENT_CZECHIA => $this->transformPaymentPriceForCzechia($payment, $participant),
            self::CONTINGENT_SLOVAKIA => $this->transformPaymentPriceForSlovakia($payment, $participant),
            self::CONTINGENT_POLAND => $this->transformPaymentPriceForPoland($payment, $participant),
            self::CONTINGENT_HUNGARY => $this->transformPaymentPriceForHungary($payment, $participant),
            self::CONTINGENT_ROMANIA => $this->transformPaymentPriceForRomania($payment, $participant),
            self::CONTINGENT_EUROPEAN => $this->transformPaymentPriceForEurope($payment, $participant),
        };
    }

    private function transformPaymentPriceForCzechia(Payment $payment, Participant $participant): Payment
    {
        $price = match (true) {
            $participant instanceof PatrolLeader => ($participant->getPatrolParticipantsCount() + 1) * 7700,
            $participant instanceof Ist => 3300,
        };

        $payment->price = (string)$price;
        $payment->currency = 'CZK';
        $payment->accountNumber = '2302084720/2010';
        $payment->iban = 'CZ31 2010 0000 0023 0208 4720';
        $payment->swift = 'FIOBCZPP';

        return $payment;
    }

    private function transformPaymentPriceForSlovakia(Payment $payment, Participant $participant): Payment
    {
        $price = match (true) {
            $participant instanceof PatrolLeader => ($participant->getPatrolParticipantsCount() + 1) * 350,
            $participant instanceof Ist => 180,
        };

        $payment->price = (string)$price;
        $payment->currency = 'EUR';
        $payment->accountNumber = '2660080180/1100';
        $payment->iban = 'SK98 1100 0000 0026 6008 0180';
        $payment->swift = 'TATRSKBX';

        return $payment;
    }

    private function transformPaymentPriceForPoland(Payment $payment, Participant $participant): Payment
    {
        $price = match (true) {
            $participant instanceof PatrolLeader => ($participant->getPatrolParticipantsCount() + 1) * 1400,
            $participant instanceof Ist => 700,
        };

        $payment->price = (string)$price;
        $payment->currency = 'PLN';
        $payment->accountNumber = 'TODO';
        $payment->iban = 'PL44 1140 1010 0000 5392 2900 1106';
        $payment->swift = 'BREXPLPWXXX';
        $payment->note = $payment->variableSymbol . ' ' . $payment->note;

        return $payment;
    }

    private function transformPaymentPriceForHungary(Payment $payment, Participant $participant): Payment
    {
        $price = match (true) {
            $participant instanceof PatrolLeader => ($participant->getPatrolParticipantsCount() + 1) * 155_500,
            $participant instanceof Ist => 105_000,
        };

        $payment->price = (string)$price;
        $payment->currency = 'HUF';
        $payment->accountNumber = '10918001-00000071-76940552';
        $payment->iban = 'HU60 10918001-00000071-76940552';
        $payment->swift = 'BACXHUHB';
        $payment->note = 'Adomány ' . $payment->variableSymbol . ' ' . $payment->note;

        return $payment;
    }

    private function transformPaymentPriceForRomania(Payment $payment, Participant $participant): Payment
    {
        $price = match (true) {
            $participant instanceof PatrolLeader => ($participant->getPatrolParticipantsCount() + 1) * 1506,
            $participant instanceof Ist => 668,
        };

        $payment->price = (string)$price;
        $payment->currency = 'RON';
        $payment->accountNumber = 'TODO';
        $payment->iban = 'RO49BTRLRONCRT033228121B';
        $payment->swift = 'BTRLRO22';
        $payment->note = $payment->variableSymbol . ' ' . $payment->note;

        return $payment;
    }

    private function transformPaymentPriceForEurope(Payment $payment, Participant $participant): Payment
    {
        $price = match (true) {
            $participant instanceof PatrolLeader => ($participant->getPatrolParticipantsCount() + 1) * 135_000,
            $participant instanceof Ist => 135_000,
        };

        $payment->price = (string)$price;
        $payment->currency = 'HUF';
        $payment->accountNumber = '1091 8001 0000 0071 7694 0301';
        $payment->iban = 'HU47 1091 8001 0000 0071 7694 0301';
        $payment->swift = 'BACXHUHB';
        $payment->note = $payment->variableSymbol . ' ' . $payment->note;

        return $payment;
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
        $ca->phone = true;
        $ca->country = true;
        $ca->email = true;
        $ca->languages = true;
        $ca->birthPlace = true;
        $ca->emergencyContact = true;
        $ca->food = true;
        $ca->idNumber = true;
        $ca->swimming = true;
        $ca->tshirt = true;

        return $ca;
    }

    public function getContentArbiterPatrolLeader(): ContentArbiterPatrolLeader
    {
        $ca = parent::getContentArbiterPatrolLeader();
        $ca->contingent = true;
        $ca->phone = true;
        $ca->country = true;
        $ca->email = true;
        $ca->languages = true;
        $ca->birthPlace = true;
        $ca->emergencyContact = true;
        $ca->food = true;
        $ca->idNumber = true;
        $ca->swimming = true;
        $ca->tshirt = true;

        return $ca;
    }

    public function getContentArbiterPatrolParticipant(): ContentArbiterPatrolParticipant
    {
        $ca = parent::getContentArbiterPatrolParticipant();
        $ca->phone = true;
        $ca->country = true;
        $ca->email = true;
        $ca->languages = true;
        $ca->birthPlace = true;
        $ca->emergencyContact = true;
        $ca->food = true;
        $ca->idNumber = true;
        $ca->swimming = true;
        $ca->tshirt = true;

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
            'detail.countryRomania',
            'detail.countryOther',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getContingents(): array
    {
        return [
            self::CONTINGENT_HUNGARY,
            self::CONTINGENT_POLAND,
            self::CONTINGENT_SLOVAKIA,
            self::CONTINGENT_CZECHIA,
            self::CONTINGENT_EUROPEAN,
            self::CONTINGENT_TEAM,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getContingentsForAdmin(UserRole $userRole): array
    {
        return match ($userRole) {
            UserRole::Admin, UserRole::IstAdmin => $this->getContingents(),
            UserRole::ContingentAdminCs => [self::CONTINGENT_CZECHIA],
            UserRole::ContingentAdminSk => [self::CONTINGENT_SLOVAKIA],
            UserRole::ContingentAdminPl => [self::CONTINGENT_POLAND],
            UserRole::ContingentAdminHu => [self::CONTINGENT_HUNGARY],
            UserRole::ContingentAdminRo => [self::CONTINGENT_ROMANIA],
            UserRole::ContingentAdminEu => [self::CONTINGENT_EUROPEAN],
            UserRole::Participant, UserRole::ContingentAdminGb, UserRole::ContingentAdminSw => [],
        };
    }

    public function showContingentPatrolStats(): bool
    {
        return true;
    }

    #[\Override]
    public function getStylesheetNameWithoutLeadingSlash(): string
    {
        return 'eventSpecificCss/stylesCej26.css';
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
        return false;
    }

    public function getReceiptTemplateName(Participant $participant): string
    {
        return match ($participant->contingent) {
            self::CONTINGENT_CZECHIA => 'receipt/receiptCejCs.twig',
            default => 'receipt/receiptCejSk.twig',
        };
    }

    public function showIban(): bool
    {
        return true;
    }

    public function showPaymentQrCode(Participant $participant): bool
    {
        if ($participant->contingent === self::CONTINGENT_CZECHIA) {
            return true;
        }

        return false;
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
}
