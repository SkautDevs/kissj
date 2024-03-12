<?php

declare(strict_types=1);

namespace kissj\Skautis;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Logging\Sentry\SentryCollector;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\User\User;
use kissj\User\UserRegeneration;
use kissj\User\UserRepository;
use kissj\User\UserService;
use Psr\Log\LoggerInterface;
use Skautis\Exception as SkautisException;
use Skautis\Skautis;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

readonly class SkautisService
{
    private Skautis $skautis;

    public function __construct(
        SkautisFactory $skautisFactory,
        private ParticipantRepository $participantRepository,
        private UserService $userService,
        private UserRegeneration $userRegeneration,
        private UserRepository $userRepository,
        private FlashMessagesInterface $flashMessages,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
        private SentryCollector $sentryCollector,
    ) {
        $this->skautis = $skautisFactory->getSkautis();
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
            $skautisUserDetail = $this->skautis->UserManagement->UserDetail();
            $skautisUserDetailExternal = $this->skautis->UserManagement->UserDetailExternal();
            $idPersonFromSkautis = $skautisUserDetail->ID_Person;
            if ($skautisUserDetail->HasMembership === true && is_numeric($idPersonFromSkautis)) {
                $skautisUnitName = $this->requestForUnitNameFromActiveMembership((int)$idPersonFromSkautis);
            } else {
                // suppress call to skautis if user has no membership, because it will throw exception
                $skautisUnitName = '';
            }

            return new SkautisUserData(
                $skautisUserDetailExternal->ID,
                $skautisUserDetailExternal->UserName,
                $idPersonFromSkautis,
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
            $this->flashMessages->error($this->translator->trans('flash.error.skautisUserError'));

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
        $this->participantRepository->persist($participant);

        $this->flashMessages->info($this->translator->trans('flash.info.skautisDataPrefilled'));

        return $participant;
    }

    public function updateSkautisUserMembership(User $user, SkautisUserData $skautisUserData): User
    {
        $user->skautisHasMembership = $skautisUserData->hasMembership;
        $this->userRepository->persist($user);

        return $user;
    }

    private function requestForUnitNameFromActiveMembership(int $idPersonFromSkautis): string
    {
        $skautisMemberships = $this->skautis->OrganizationUnit->MembershipAllPerson([
            'ID_Person' => $idPersonFromSkautis,
            'ID_MembershipType' => 'radne',
            'IsValid' => true,
        ])->MembershipAllOutput;

        $skautisUnitName = sprintf(
            '%s - %s',
            $skautisMemberships->RegistrationNumber,
            $skautisMemberships->Unit
        );

        return $skautisUnitName;
    }
}
