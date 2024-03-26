<?php

declare(strict_types=1);

namespace kissj\Import;

use DateTimeImmutable;
use kissj\Application\CsvParser;
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
use League\Csv\Exception as LeagueCsvException;
use Slim\Psr7\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class ImportSrs
{
    public function __construct(
        private IstRepository $istRepository,
        private UserService $userService,
        private PaymentService $paymentService,
        private CsvParser $csvParser,
        private FlashMessagesInterface $flashMessages,
        private TranslatorInterface $translator,
    ) {
    }

    public function importIst(UploadedFile $istsDataFile, Event $event): void
    {
        $istsData = [];
        try {
            /** @var array<array<string,string>> $istsData */
            $istsData = $this->csvParser->parseCsv($istsDataFile);
        } catch (\UnexpectedValueException | LeagueCsvException) {
            $this->flashMessages->error($this->translator->trans('flash.error.importSrs.invalidCsv'));
        }

        $existingCount = 0;
        $importedCount = 0;
        $errorCount = 0;

        foreach ($istsData as $istData) {
            if (in_array($istData['E-mail'], ['""', '', null, 'NULL'], true)) {
                continue;
            }

            $istData = array_map(
                fn (string $value): string => substr($value, 1, -1),
                $istData,
            );
            $dbIst = $this->istRepository->findOneBy(['email' => $istData['E-mail']]);
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
            'flash.info.istsImported',
            [
                '%existingCount%' => $existingCount,
                '%importedCount%' => $importedCount,
                '%errorCount%' => $errorCount,
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
            $data['Chci pro své dítě/děti akcí zajištěné jídlo (budeš kontaktován v rámci doplacení poplatku za jídlo 130 Kč/dítě/den). Děti do 1,5 roku neplatí, předpokládáme zajištěné jídlo vlastní (ve školce bude k dispozici mikrovlnka a rychlovarná konvice).'],
            $data['Jsem zákonným zástupcem výše vypsaných dětí.'],
            $data['Potvrzuji, že věk výše vypsaných dětí v době konání akce nepřesáhne 10 let.'],
        ] as $key => $value) {
            if ($value !== '') {
                $notes['deti.' . $key] = $value;
            }
        }

        $userStatus = UserStatus::Approved;
        $paymentStatus = PaymentStatus::Waiting;
        $registrationPayDate = null;
        if (in_array($data['last_payment_date'], ['0', '', null, 'NULL'], true) === false) {
            $registrationPayDate = DateTimeUtils::getDateTime($data['last_payment_date']);
            $paymentStatus = PaymentStatus::Paid;
            $userStatus = UserStatus::Paid;
        }

        $continget = EventTypeObrok::CONTINGENT_VOLUNTEER;
        if ($data['role'] === 'Organizační tým - registruj se, pokud jsi v týmu Obroku 24') {
            $continget = EventTypeObrok::CONTINGENT_ORG;
        }


        $skautisUserId = $data['skautis_user_id'];
        if (is_numeric($skautisUserId) === false) {
            $this->flashMessages->warning('flash.warning.importSrs.skautisUserIdNotNumeric');

            return null;
        }

        $email = $data['E-mail'];
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
            $data['phone'] === 'NULL' ? '' : $data['phone'],
            $data['unit'] === 'NULL' ? '' : $data['unit'],
            DateTimeUtils::getDateTime($data['birthdate']),
            $data['Alergie (konkrétní jídlo, hmyz, pyly apod.)'],
            $data['Léky, které užíváš'],
            $data['Jiné dlouhodobé zdravotní problémy a psychická onemocnění'],
            $data['Stravovací omezení'],
            $this->getArrivalDate($data['Datum příjezdu na akci']),
            $data['sekce, ve které trávíš nejvíce času'],
            [$data['podsekce, konkrétní pozice']],
            $handbook,
            serialize($notes),
            DateTimeUtils::getDateTime(),
            DateTimeUtils::getDateTime(),
            $registrationPayDate,
            $this->getVariableSymbol($data['variable_symbol'], $event),
            500,
            $paymentStatus,
            $event->accountNumber,
            $event->iban,
            $event->swift,
            DateTimeUtils::getDateTime('now + 14 days'),
        );

        return $user;
    }

    private function getArrivalDate(string $arrivaaDate): DateTimeImmutable
    {
        return DateTimeUtils::getDateTime(match ($arrivaaDate) {
            'sobota 25. května' => '2024-05-25',
            'neděle 26. května' => '2024-05-26',
            'úterý 28. května do 16:00' => '2024-05-28',
            default => 'now',
        });
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
