<?php

declare(strict_types=1);

namespace kissj\Skautis;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Logging\Sentry\SentryCollector;
use kissj\Participant\Gender;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\User\User;
use kissj\User\UserRegeneration;
use kissj\User\UserRepository;
use kissj\User\UserService;
use Psr\Log\LoggerInterface;
use Skautis\Exception as SkautisException;
use Skautis\Skautis;
use Throwable;

/**
 * uses library for communication with Skautis service, library uses SOAP internally
 * docs: https://test-is.skaut.cz/JunakWebservice/
 */
class SkautisService
{
    private Skautis $skautis;

    public function __construct(
        private readonly SkautisFactory $skautisFactory,
        private readonly ParticipantRepository $participantRepository,
        private readonly UserService $userService,
        private readonly UserRegeneration $userRegeneration,
        private readonly UserRepository $userRepository,
        private readonly FlashMessagesInterface $flashMessages,
        private readonly LoggerInterface $logger,
        private readonly SentryCollector $sentryCollector,
    ) {
    }

    public function initSkautis(string $appId): void
    {
        $this->skautis = $this->skautisFactory->getSkautis($appId);

        // should call LoginUpdateRefresh to refresh 30 minutes granted for Skautis login
        // docs: https://napoveda.skaut.cz/programatori/uzivatel#obnoveni-aktivity-uzivatele-ve-skautisu
        $loginId = $this->skautis->getUser()->getLoginId();
        if ($loginId !== null) {
            try {
                $this->skautis->UserManagement->LoginUpdateRefresh([
                    'ID' => $loginId,
                ]);
            } catch (SkautisException $t) {
                $this->logger->warning('Failed to refresh Skautis login: ' . $t->getMessage());
            }
        }
    }

    public function getLoginUri(string $backlink): string
    {
        return $this->skautis->getLoginUrl($backlink);
    }

    /**
     * @param array<string,string> $parsedBody
     */
    public function saveDataFromPost(array $parsedBody): void
    {
        $this->skautis->setLoginData($parsedBody);
    }

    public function isUserLoggedIn(): bool
    {
        try {
            return $this->skautis->getUser()->isLoggedIn();
        } catch (SkautisException) {
            return false;
        }
    }

    public function getUserDetailsFromLoggedSkautisUser(): ?SkautisUserData
    {
        try {
            /** @var object{ID_Person: int|string, HasMembership: bool} $skautisUserDetail */
            $skautisUserDetail = $this->skautis->UserManagement->UserDetail();
            /** @var object{ID: int, UserName: string, FirstName: string, LastName: string, NickName: string, Birthday: string, Email: string, Phone: string, Street: string, City: string, PostCode: string} $skautisUserDetailExternal */
            $skautisUserDetailExternal = $this->skautis->UserManagement->UserDetailExternal();
            $idPersonFromSkautis = $skautisUserDetail->ID_Person;
            if ($skautisUserDetail->HasMembership === true && is_numeric($idPersonFromSkautis)) {
                $skautisUnitName = $this->requestForUnitNameFromActiveMembership((int)$idPersonFromSkautis);
            } else {
                // suppress call to skautis if user has no membership, because it will throw exception
                $skautisUnitName = '';
            }

            $gender = is_numeric($idPersonFromSkautis)
                ? $this->requestGenderFromPerson((int)$idPersonFromSkautis)
                : Gender::Other;

            return new SkautisUserData(
                $skautisUserDetailExternal->ID,
                $skautisUserDetailExternal->UserName,
                (int)$idPersonFromSkautis,
                $skautisUserDetailExternal->FirstName ?? '',
                $skautisUserDetailExternal->LastName ?? '',
                $skautisUserDetailExternal->NickName ?? '',
                DateTimeUtils::getDateTime($skautisUserDetailExternal->Birthday),
                $skautisUserDetailExternal->Email,
                $skautisUserDetailExternal->Phone ?? '',
                $skautisUserDetailExternal->Street ?? '',
                $skautisUserDetailExternal->City ?? '',
                $skautisUserDetailExternal->PostCode ?? '',
                $skautisUserDetail->HasMembership,
                $skautisUnitName,
                $gender,
            );
        } catch (Throwable $t) {
            $this->sentryCollector->collect($t);
            $this->logger->error('Error while getting user details from skautis: ' . $t->getMessage(), [
                'throwable' => $t,
            ]);

            return null;
        }
    }

    public function getOrCreateAndLogInSkautisUser(SkautisUserData $skautisUserData, Event $event): User
    {
        $user = $this->userRepository->findSkautisUser($skautisUserData->skautisId, $event);
        if ($user === null) {
            $user = $this->userService->registerSkautisUser(
                $skautisUserData->skautisId,
                $skautisUserData->hasMembership,
                $skautisUserData->email,
                $event,
            );
        }

        $this->userRegeneration->saveUserIdIntoSession($user);

        return $user;
    }

    public function prefillDataFromSkautis(Participant $participant): Participant
    {
        $skautisUserData = $this->getUserDetailsFromLoggedSkautisUser();
        if ($skautisUserData === null) {
            $this->flashMessages->error('flash.error.skautisUserError');

            return $participant;
        }

        $participant->firstName = $skautisUserData->firstName;
        $participant->lastName = $skautisUserData->lastName;
        $participant->nickname = $skautisUserData->nickName;
        $participant->birthDate = $skautisUserData->birthday;
        $participant->email = $skautisUserData->email;
        $participant->telephoneNumber = $skautisUserData->phone;
        $participant->permanentResidence = $skautisUserData->getPermanentResidence();
        $participant->scoutUnit = $skautisUserData->unitName;
        $participant->gender = $skautisUserData->gender->value;
        $this->participantRepository->persist($participant);

        $this->flashMessages->info('flash.info.skautisDataPrefilled');

        return $participant;
    }

    public function updateSkautisUserMembership(User $user, SkautisUserData $skautisUserData): User
    {
        $user->skautisHasMembership = $skautisUserData->hasMembership;
        $this->userRepository->persist($user);

        return $user;
    }

    public function getLeaderPersonId(): int
    {
        /** @var object{ID_Person: int|string} $userDetail */
        $userDetail = $this->skautis->UserManagement->UserDetail();

        return (int)$userDetail->ID_Person;
    }

    /**
     * @return list<int>
     */
    public function getLeaderUnitIds(int $idPerson): array
    {
        /** @var object{MembershipAllOutput?: object{ID_Unit: int}|array<object{ID_Unit: int}>} $membershipResult */
        $membershipResult = $this->skautis->OrganizationUnit->MembershipAllPerson([
            'ID_Person' => $idPerson,
            'ID_MembershipType' => 'radne',
            'IsValid' => true,
        ]);

        if (!isset($membershipResult->MembershipAllOutput)) {
            return [];
        }

        $membershipRaw = $membershipResult->MembershipAllOutput;
        /** @var array<object{ID_Unit: int}> $memberships */
        $memberships = is_array($membershipRaw) ? $membershipRaw : [$membershipRaw];

        $unitIds = [];
        foreach ($memberships as $membership) {
            $unitIds[] = (int)$membership->ID_Unit;
        }

        return array_slice(array_unique($unitIds), 0, 3);
    }

    /**
     * @param list<int> $unitIds
     * @return list<SkautisMemberData>
     */
    public function getUnitMembers(array $unitIds): array
    {
        $members = [];
        foreach ($unitIds as $unitId) {
            /** @var list<object{ID: int, FirstName: string, LastName: string, NickName: string, Birthday: string, Street: string, City: string, Postcode: string, State: string, ID_Sex: string}> $persons */
            $persons = $this->skautis->OrganizationUnit->PersonAll([
                'ID_Unit' => $unitId,
                'OnlyDirectMember' => true,
            ]);

            if (!is_iterable($persons)) {
                continue;
            }

            foreach ($persons as $person) {
                $members[] = new SkautisMemberData(
                    id: $person->ID,
                    firstName: $person->FirstName,
                    lastName: $person->LastName,
                    nickName: $person->NickName,
                    birthday: DateTimeUtils::getDateTime($person->Birthday),
                    street: $person->Street,
                    city: $person->City,
                    postcode: $person->Postcode,
                    state: $person->State,
                    sex: $person->ID_Sex,
                );
            }

            if (count($members) >= 100) {
                break;
            }
        }

        $members = array_slice($members, 0, 100);
        usort($members, fn (SkautisMemberData $a, SkautisMemberData $b) => $a->lastName <=> $b->lastName);

        return $members;
    }

    private function requestGenderFromPerson(int $idPersonFromSkautis): Gender
    {
        try {
            /** @var object{PersonAllOutput: object{ID_Sex?: string, Sex?: string}} $personResult */
            $personResult = $this->skautis->OrganizationUnit->PersonAll([
                'ID' => $idPersonFromSkautis,
            ]);

            $sexDisplayName = $personResult->PersonAllOutput->Sex ?? null;

            return Gender::fromSkautisDisplayName($sexDisplayName);
        } catch (Throwable) {
            return Gender::Other;
        }
    }

    private function requestForUnitNameFromActiveMembership(int $idPersonFromSkautis): string
    {
        /** @var object{MembershipAllOutput: object{RegistrationNumber: string, Unit: string}} $membershipResult */
        $membershipResult = $this->skautis->OrganizationUnit->MembershipAllPerson([
            'ID_Person' => $idPersonFromSkautis,
            'ID_MembershipType' => 'radne',
            'IsValid' => true,
        ]);
        $skautisMemberships = $membershipResult->MembershipAllOutput;

        $skautisUnitName = sprintf(
            '%s - %s',
            $skautisMemberships->RegistrationNumber,
            $skautisMemberships->Unit,
        );

        return $skautisUnitName;
    }
}
