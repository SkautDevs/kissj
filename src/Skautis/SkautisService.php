<?php

declare(strict_types=1);

namespace kissj\Skautis;

use Exception;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\FlashMessages\FlashMessagesInterface;
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

class SkautisService
{
    private Skautis $skautis;

    public function __construct(
        readonly SkautisFactory $skautisFactory,
        private readonly ParticipantRepository $participantRepository,
        private readonly UserService $userService,
        private readonly UserRegeneration $userRegeneration,
        private readonly UserRepository $userRepository,
        private readonly FlashMessagesInterface $flashMessages,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
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
            $skautisUnitDetail = $this->skautis->OrganizationUnit->UnitDetail();

            return new SkautisUserData(
                $skautisUserDetailExternal->ID,
                $skautisUserDetailExternal->UserName,
                $skautisUserDetailExternal->ID_Person,
                $skautisUserDetailExternal->FirstName,
                $skautisUserDetailExternal->LastName,
                $skautisUserDetailExternal->NickName,
                DateTimeUtils::getDateTime($skautisUserDetailExternal->Birthday),
                $skautisUserDetailExternal->Email,
                $skautisUserDetailExternal->Phone,
                $skautisUserDetailExternal->Street,
                $skautisUserDetailExternal->City,
                $skautisUserDetailExternal->PostCode,
                $skautisUserDetail->HasMembership,
                $skautisUnitDetail->NewDisplayName,
            );
        } catch (Exception $e) {
            $this->logger->error('Error while getting user details from skautis: ' . $e->getMessage(), [
                'exception' => $e,
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
}