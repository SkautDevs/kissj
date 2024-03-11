<?php

declare(strict_types=1);

namespace kissj\Import;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\ParticipantRole;
use kissj\Payment\PaymentService;
use kissj\Payment\PaymentStatus;
use kissj\User\User;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class ImportSrs
{
    public function __construct(
        private IstRepository $istRepository,
        private UserService $userService,
        private PaymentService $paymentService,
        private FlashMessagesInterface $flashMessages,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @param array<array<string,string>> $istsData
     */
    public function importIst(array $istsData, Event $event): void
    {
        $existingCount = 0;
        $importedCount = 0;
        $errorCount = 0;

        foreach ($istsData as $istData) {
            $dbIst = $this->istRepository->findOneBy(['email' => $istData['email']]);
            if ($dbIst instanceof Ist) {
                $existingCount++;
                continue;
            }

            $newIst = $this->mapDataIntoNewIst($istData, $event);
            if ($newIst === null) {
                $errorCount++;
            } else {
                $importedCount++;
            }
        }

        $this->flashMessages->info($this->translator->trans(
            'flash.info.importSrs.istsImported',
            [
                'existingCount' => $existingCount,
                'importedCount' => $importedCount,
                'errorCount' => $errorCount,
            ],
        ));
    }

    /**
     * @param array<string,string> $data
     */
    private function mapDataIntoNewIst(array $data, Event $event): ?User
    {
        $notes = [];
        $notes['id'] = $data['id'];
        $notes['poznamky'] = $data['Prostor na tvoje poznámky, které jsou důležité a jinam se nevešly'];
        foreach ([
            $data['Jména a věk dětí, které bereš s sebou, ve formátu „Jan Novák – 5 let“ (více dětí odděl čárkou)'],
            $data['Chci pro své děti využít Obrok školku (do 5 let, více info na webu).'],
            $data['Chci pro své dítě/děti akcí zajištěné jídlo (budeš kontaktován v rámci doplacení poplatku za jídlo 130 Kč/dítě/den).Děti do 1,5 roku neplatí, předpokládáme zajištěné jídlo vlastní (ve školce bude k dispozici mikrovlnka a rychlovarná konvice).'],
            $data['Jsem zákonným zástupcem výše vypsaných dětí.'],
            $data['Potvrzuji, že věk výše vypsaných dětí v době konání akce nepřesáhne 10 let.'],
        ] as $key => $value) {
            $notes['deti.' . $key] = $value;
        }

        $userStatus = UserStatus::Approved;
        $paymentStatus = PaymentStatus::Waiting;
        $registrationPayDate = null;
        if (in_array($data['last_payment_date'], ['0', '', null], true) === false) {
            $registrationPayDate = DateTimeUtils::getDateTime($data['last_payment_date']);
            $paymentStatus = PaymentStatus::Paid;
            $userStatus = UserStatus::Paid;
        }

        $continget = EventTypeObrok::CONTINGENT_VOLUNTEER;
        if ($data['Role'] === 'Organizační tým - registruj se, pokud jsi v týmu Obroku 24') {
            $continget = EventTypeObrok::CONTINGENT_ORG;
        }


        $skautisUserId = $data['skautis_user_id'];
        if (is_numeric($skautisUserId) === false) {
            $this->flashMessages->warning('flash.warning.importSrs.skautisUserIdNotNumeric');

            return null;
        }

        $email = $data['email'];
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->flashMessages->warning('flash.warning.importSrs.emailInvalid');

            return null;
        }

        $address = sprintf('%s %s, %s, %s', $data['street'], $data['city'], $data['postcode'], $data['state']);
        $handbook = match ($data['Chci na akci dostat handbook v tištěné formě.']) {
            'Ano' => true,
            default => false,
        };

        $user = $this->userService->createSkautisUserParticipantPayment(
            $event,
            (int)$skautisUserId,
            $email,
            $userStatus,
            ParticipantRole::Ist,
            $continget,
            $data['first_name'],
            $data['last_name'],
            $data['nick_name'],
            $address,
            $data['phone'],
            $data['unit'],
            DateTimeUtils::getDateTime($data['birthdate']),
            $data['Alergie (konkrétní jídlo, hmyz, pyly apod.)'],
            $data['Léky, které užíváš'],
            $data['Jiné dlouhodobé zdravotní problémy a psychická onemocnění'],
            $data['Stravovací omezení'],
            DateTimeUtils::getDateTime($data['Datum příjezdu na akci']),
            $data['sekce, ve které trávíš nejvíce času'],
            [$data['podsekce, konkrétní pozice']],
            $handbook,
            serialize($notes),
            DateTimeUtils::getDateTime(),
            DateTimeUtils::getDateTime(),
            $registrationPayDate,
            $this->getVariableSymbol($data['Variabilní symbol'], $event),
            500,
            $paymentStatus,
            $event->accountNumber,
            $event->iban,
            $event->swift,
            DateTimeUtils::getDateTime('now + 14 days'),
        );

        return $user;
    }

    private function getVariableSymbol(string $variableSymbolRaw, Event $event): string
    {
        if (in_array($variableSymbolRaw, ['0', '', null], true)) {
            return $this->paymentService->getVariableNumber($event);
        }

        $pieces = explode(', ', $variableSymbolRaw);
        if (count($pieces) !== 2) {
            throw new \RuntimeException('Unexpected value in variable symbol column');
        }

        return $pieces[1];
    }
}
