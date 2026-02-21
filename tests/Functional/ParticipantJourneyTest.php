<?php declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use PHPUnit\Framework\Attributes\Group;
use kissj\Mailer\MailerSettings;
use kissj\Participant\Guest\Guest;
use kissj\Participant\Guest\GuestRepository;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\Participant\Patrol\PatrolService;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopLeaderRepository;
use kissj\Participant\Troop\TroopParticipant;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\Participant\Troop\TroopService;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use kissj\Payment\PaymentStatus;
use kissj\User\LoginToken;
use kissj\User\LoginTokenRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class ParticipantJourneyTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    /**
     * Test complete IST registration happy path:
     * Register → Login → Choose IST Role → Fill Details → Lock → Admin Approve → Pay
     */
    public function testIstFullRegistrationJourney(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);
        
        $email = 'ist-test@example.com';
        
        // Step 1: Register and get login token
        $user = $this->registerUser($container, $email);
        $loginToken = $this->createLoginToken($container, $user);
        
        // Step 2: Login with token
        $responseLogin = $app->handle($this->createRequest(
            self::BASE_URL . '/tryLogin/' . $loginToken->token
        ));
        $this->assertSame(302, $responseLogin->getStatusCode());
        
        // Step 3: User should be redirected to choose role
        // Simulate session by creating participant with IST role
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');
        
        // Refresh user to get updated status
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $user = $userRepository->get($user->id);
        $this->assertSame(UserStatus::Open, $user->status);
        
        // Step 4: Fill IST details
        /** @var IstRepository $istRepository */
        $istRepository = $container->get(IstRepository::class);
        $ist = $istRepository->get($participant->id);
        
        $ist->firstName = 'Test';
        $ist->lastName = 'IST';
        $ist->nickname = 'Tester';
        $ist->birthDate = new \DateTimeImmutable('1990-01-01');
        $ist->email = $email;
        $ist->gender = 'male';
        $ist->country = 'CZ';
        $ist->contingent = 'detail.contingent.czechia';
        $istRepository->persist($ist);
        
        // Step 5: Lock registration (update user status)
        $user->status = UserStatus::Closed;
        $userRepository->persist($user);
        
        $this->assertSame(UserStatus::Closed, $user->status);
        
        // Step 6: Admin approves
        $user->status = UserStatus::Approved;
        $userRepository->persist($user);
        
        // Step 7: Generate payment
        /** @var PaymentService $paymentService */
        $paymentService = $container->get(PaymentService::class);
        $ist = $istRepository->get($ist->id); // Refresh
        $payment = $paymentService->createAndPersistNewEventPayment($ist);
        
        $this->assertSame(PaymentStatus::Waiting, $payment->status);
        
        // Step 8: Confirm payment
        $paymentService->confirmPayment($payment);
        
        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $container->get(PaymentRepository::class);
        $updatedPayment = $paymentRepository->get($payment->id);
        
        $this->assertSame(PaymentStatus::Paid, $updatedPayment->status);
        
        // Verify final user status
        $finalUser = $userRepository->get($user->id);
        $this->assertSame(UserStatus::Paid, $finalUser->status);
    }

    /**
     * Test Patrol Leader with participants registration:
     * Register Leader → Add Participants → Fill Details → Lock → Approve → Pay
     */
    #[Group('patrol')]
    public function testPatrolLeaderWithParticipantsJourney(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);
        
        // Ensure event has enough capacity for new registrations
        // Note: PostgreSQL accumulates data across test runs, so use high limits
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        $this->assertNotNull($event);
        $event->maximalClosedPatrolsCount = 10000;  // High limit for accumulated test data
        // Set min/max participants per patrol (null defaults to 0 which fails validation)
        $event->minimalPatrolParticipantsCount = 1;
        $event->maximalPatrolParticipantsCount = 10;
        $eventRepository->persist($event);
        
        $leaderEmail = 'patrol-leader@example.com';
        
        // Step 1: Register patrol leader
        $leaderUser = $this->registerUser($container, $leaderEmail);
        
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $participant = $userService->createParticipantSetRole($leaderUser, 'pl');
        
        // Step 2: Fill ALL required leader details (based on AbstractContentArbiter defaults)
        /** @var PatrolLeaderRepository $patrolLeaderRepository */
        $patrolLeaderRepository = $container->get(PatrolLeaderRepository::class);
        /** @var PatrolLeader $patrolLeader */
        $patrolLeader = $patrolLeaderRepository->get($participant->id);
        
        // Required fields from AbstractContentArbiter (default = true)
        $patrolLeader->patrolName = 'Test Patrol';  // Required for PatrolLeader
        $patrolLeader->firstName = 'Leader';
        $patrolLeader->lastName = 'Test';
        $patrolLeader->nickname = 'LeaderNick';
        $patrolLeader->permanentResidence = '123 Test Street, Test City';
        $patrolLeader->gender = 'female';
        $patrolLeader->birthDate = new \DateTimeImmutable('1995-05-15');
        $patrolLeader->healthProblems = 'None';
        $patrolLeader->psychicalHealthProblems = 'None';
        $patrolLeader->notes = 'Test notes';
        // Optional but good to have
        $patrolLeader->email = $leaderEmail;
        $patrolLeaderRepository->persist($patrolLeader);
        
        // Step 3: Add patrol participants with ALL required fields
        /** @var PatrolService $patrolService */
        $patrolService = $container->get(PatrolService::class);
        /** @var PatrolParticipantRepository $patrolParticipantRepository */
        $patrolParticipantRepository = $container->get(PatrolParticipantRepository::class);
        
        // Participant 1 - fill all required fields
        $participant1 = $patrolService->addPatrolParticipant($patrolLeader);
        $participant1->firstName = 'Participant';
        $participant1->lastName = 'One';
        $participant1->nickname = 'P1';
        $participant1->permanentResidence = '456 Test Ave, Test Town';
        $participant1->gender = 'male';
        $participant1->birthDate = new \DateTimeImmutable('2005-03-20');
        $participant1->healthProblems = 'None';
        $participant1->psychicalHealthProblems = 'None';
        $participant1->notes = '';
        $participant1->email = 'participant1@example.com';
        $patrolParticipantRepository->persist($participant1);
        
        // Participant 2 - fill all required fields
        $participant2 = $patrolService->addPatrolParticipant($patrolLeader);
        $participant2->firstName = 'Participant';
        $participant2->lastName = 'Two';
        $participant2->nickname = 'P2';
        $participant2->permanentResidence = '789 Test Blvd, Test Village';
        $participant2->gender = 'female';
        $participant2->birthDate = new \DateTimeImmutable('2006-07-10');
        $participant2->healthProblems = 'None';
        $participant2->psychicalHealthProblems = 'None';
        $participant2->notes = '';
        $participant2->email = 'participant2@example.com';
        $patrolParticipantRepository->persist($participant2);
        
        // Verify participants are linked (need minimum 2 for test event)
        $patrolLeader = $patrolLeaderRepository->get($patrolLeader->id); // Refresh
        $this->assertSame(2, $patrolLeader->getPatrolParticipantsCount());
        
        // Initialize mailer settings (normally done by middleware)
        $this->initializeMailerSettings($container, $leaderUser->event);
        
        // Step 4: Lock patrol registration
        $patrolService->closeRegistration($patrolLeader);
        
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $leaderUser = $userRepository->get($leaderUser->id);
        $this->assertSame(UserStatus::Closed, $leaderUser->status);
        
        // Step 5: Admin approves
        $leaderUser->status = UserStatus::Approved;
        $userRepository->persist($leaderUser);
        
        // Step 6: Generate and confirm payment
        /** @var PaymentService $paymentService */
        $paymentService = $container->get(PaymentService::class);
        $patrolLeader = $patrolLeaderRepository->get($patrolLeader->id); // Refresh
        $payment = $paymentService->createAndPersistNewEventPayment($patrolLeader);
        $paymentService->confirmPayment($payment);
        
        // Verify final status
        $finalUser = $userRepository->get($leaderUser->id);
        $this->assertSame(UserStatus::Paid, $finalUser->status);
    }

    /**
     * Test login flow via HTTP requests
     */
    public function testLoginViaHttpRequests(): void
    {
        $app = $this->getTestApp();
        $email = 'http-test@example.com';
        
        // Step 1: Request login page (may redirect if event not found)
        $responseLoginPage = $app->handle($this->createRequest(self::BASE_URL . '/login'));
        // Accept either 200 (login page shown) or 302 (redirect to event list if event not configured)
        $this->assertTrue(
            in_array($responseLoginPage->getStatusCode(), [200, 302], true),
            'Expected 200 or 302, got ' . $responseLoginPage->getStatusCode()
        );
        
        // If we got the login page, test the full flow
        if ($responseLoginPage->getStatusCode() === 200) {
            $this->assertStringContainsString('form-email', (string)$responseLoginPage->getBody());
            
            // Step 2: Submit email
            $app = $this->getTestApp(false);
            $responseSubmitEmail = $app->handle($this->createRequest(
                self::BASE_URL . '/login',
                'POST',
                ['email' => $email]
            ));
            $this->assertSame(302, $responseSubmitEmail->getStatusCode());
            
            // Step 3: Follow redirect to "link sent" page
            $app = $this->getTestApp(false);
            $linkSentUrl = $responseSubmitEmail->getHeaderLine('Location');
            $responseLinkSent = $app->handle($this->createRequest($linkSentUrl));
            $this->assertSame(200, $responseLinkSent->getStatusCode());
        }
    }

    /**
     * Test that user can view dashboard after choosing role
     */
    public function testDashboardAccessAfterRoleSelection(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);
        
        $email = 'dashboard-test@example.com';
        $user = $this->registerUser($container, $email);
        
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');
        
        // User should now have participant role
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $updatedUser = $userRepository->get($user->id);
        
        $this->assertSame(UserStatus::Open, $updatedUser->status);
        $this->assertNotNull($participant);
    }

    /**
     * Test Guest registration journey:
     * Register → Choose Guest Role → Fill Details → Close → Admin Approve → Paid (no payment needed)
     *
     * Guests have price 0 by default, so approval sets them directly to Paid with no Payment created.
     */
    #[Group('guest')]
    public function testGuestRegistrationJourney(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);

        $guestEmail = 'guest-test@example.com';

        // Step 1: Register guest user
        $guestUser = $this->registerUser($container, $guestEmail);
        self::assertSame(UserStatus::WithoutRole, $guestUser->status);

        // Step 2: Choose guest role
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $participant = $userService->createParticipantSetRole($guestUser, 'guest');

        // Verify role is set and status is Open
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $guestUser = $userRepository->get($guestUser->id);
        self::assertSame(UserStatus::Open, $guestUser->status);

        // Step 3: Fill guest details
        /** @var GuestRepository $guestRepository */
        $guestRepository = $container->get(GuestRepository::class);
        /** @var Guest $guest */
        $guest = $guestRepository->get($participant->id);

        $guest->firstName = 'Test';
        $guest->lastName = 'Guest';
        $guest->nickname = 'Visitor';
        $guest->permanentResidence = '123 Guest Street, Guest City';
        $guest->gender = 'other';
        $guest->birthDate = new \DateTimeImmutable('1985-08-20');
        $guest->telephoneNumber = '+420123456789';
        $guest->arrivalDate = new \DateTimeImmutable('2026-07-01');
        $guest->departureDate = new \DateTimeImmutable('2026-07-10');
        $guest->healthProblems = 'None';
        $guest->psychicalHealthProblems = 'None';
        $guest->notes = 'VIP guest';
        $guest->email = $guestEmail;
        $guestRepository->persist($guest);

        // Initialize mailer settings (normally done by middleware)
        $this->initializeMailerSettings($container, $guestUser->event);

        // Step 4: Close registration
        /** @var ParticipantService $participantService */
        $participantService = $container->get(ParticipantService::class);
        $participantService->closeRegistration($guest);

        // Step 5: Admin approves (Guest price is 0, so goes directly to Paid)
        $participantService->approveRegistration($guest);

        // Verify final status is Paid with no payments
        $finalUser = $userRepository->get($guestUser->id);
        self::assertSame(UserStatus::Paid, $finalUser->status);
        self::assertSame(0, $guest->countPaidPayments());
        self::assertSame(0, $guest->countWaitingPayments());
    }

    /**
     * Test Troop Leader with Troop Participants registration:
     * 1. Leader registers → chooses role → fills details
     * 2. Participants register independently → choose role → fill details
     * 3. Participants tie to leader using tie codes
     * 4. Participants close their registration
     * 5. Leader closes registration (must have minimum participants who are closed)
     * 6. Admin approves → Payment
     * 
     * Key difference from Patrol: Troop participants register independently and use
     * tie codes to join a leader, rather than being added by the leader.
     */
    #[Group('troop')]
    public function testTroopLeaderWithParticipantsJourney(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);
        
        // Enable troop registrations for test event (limits are null by default = full)
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        $this->assertNotNull($event);
        $this->enableTroopForEvent($container, $event);
        
        $leaderEmail = 'troop-leader@example.com';
        $participant1Email = 'troop-participant1@example.com';
        $participant2Email = 'troop-participant2@example.com';
        
        // Step 1: Register and setup Troop Leader
        $leaderUser = $this->registerUser($container, $leaderEmail);
        
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $leaderParticipant = $userService->createParticipantSetRole($leaderUser, 'tl');
        
        /** @var TroopLeaderRepository $troopLeaderRepository */
        $troopLeaderRepository = $container->get(TroopLeaderRepository::class);
        /** @var TroopLeader $troopLeader */
        $troopLeader = $troopLeaderRepository->get($leaderParticipant->id);
        
        // Fill leader details (required fields from AbstractContentArbiter + patrolName for TroopLeader)
        $troopLeader->patrolName = 'Test Troop';  // Required for TroopLeader (reused as troop name)
        $troopLeader->firstName = 'Troop';
        $troopLeader->lastName = 'Leader';
        $troopLeader->nickname = 'Chief';
        $troopLeader->permanentResidence = '123 Leader Ave, Leader City';
        $troopLeader->gender = 'male';
        $troopLeader->birthDate = new \DateTimeImmutable('1990-01-15');
        $troopLeader->healthProblems = 'None';
        $troopLeader->psychicalHealthProblems = 'None';
        $troopLeader->notes = 'Troop leader notes';
        $troopLeader->email = $leaderEmail;
        $troopLeaderRepository->persist($troopLeader);
        
        // Store the leader's tie code for verification
        $leaderTieCode = $troopLeader->tieCode;
        $this->assertNotEmpty($leaderTieCode, 'Leader should have a tie code');
        
        // Step 2: Register Troop Participant 1
        $participant1User = $this->registerUser($container, $participant1Email);
        $participant1Data = $userService->createParticipantSetRole($participant1User, 'tp');
        
        /** @var TroopParticipantRepository $troopParticipantRepository */
        $troopParticipantRepository = $container->get(TroopParticipantRepository::class);
        /** @var TroopParticipant $troopParticipant1 */
        $troopParticipant1 = $troopParticipantRepository->get($participant1Data->id);
        
        // Fill participant 1 details
        $troopParticipant1->firstName = 'Scout';
        $troopParticipant1->lastName = 'One';
        $troopParticipant1->nickname = 'S1';
        $troopParticipant1->permanentResidence = '456 Scout Street, Scout Town';
        $troopParticipant1->gender = 'male';
        $troopParticipant1->birthDate = new \DateTimeImmutable('2005-06-20');
        $troopParticipant1->healthProblems = 'None';
        $troopParticipant1->psychicalHealthProblems = 'None';
        $troopParticipant1->notes = '';
        $troopParticipant1->email = $participant1Email;
        $troopParticipantRepository->persist($troopParticipant1);
        
        $participant1TieCode = $troopParticipant1->tieCode;
        $this->assertNotEmpty($participant1TieCode, 'Participant 1 should have a tie code');
        
        // Step 3: Register Troop Participant 2
        $participant2User = $this->registerUser($container, $participant2Email);
        $participant2Data = $userService->createParticipantSetRole($participant2User, 'tp');
        
        /** @var TroopParticipant $troopParticipant2 */
        $troopParticipant2 = $troopParticipantRepository->get($participant2Data->id);
        
        // Fill participant 2 details
        $troopParticipant2->firstName = 'Scout';
        $troopParticipant2->lastName = 'Two';
        $troopParticipant2->nickname = 'S2';
        $troopParticipant2->permanentResidence = '789 Scout Blvd, Scout Village';
        $troopParticipant2->gender = 'female';
        $troopParticipant2->birthDate = new \DateTimeImmutable('2006-09-10');
        $troopParticipant2->healthProblems = 'None';
        $troopParticipant2->psychicalHealthProblems = 'None';
        $troopParticipant2->notes = '';
        $troopParticipant2->email = $participant2Email;
        $troopParticipantRepository->persist($troopParticipant2);
        
        $participant2TieCode = $troopParticipant2->tieCode;
        $this->assertNotEmpty($participant2TieCode, 'Participant 2 should have a tie code');
        
        // Step 4: Tie participants to leader
        /** @var TroopService $troopService */
        $troopService = $container->get(TroopService::class);
        
        // Refresh leader to ensure status is current
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $leaderUser = $userRepository->get($leaderUser->id);
        $this->assertSame(UserStatus::Open, $leaderUser->status, 'Leader must be in Open status to accept ties');
        
        // Tie participant 1 to leader
        $troopParticipant1 = $troopService->tieTroopParticipantToTroopLeader($troopParticipant1, $troopLeader);
        $this->assertNotNull($troopParticipant1->troopLeader, 'Participant 1 should be tied to leader');
        $this->assertSame($troopLeader->id, $troopParticipant1->troopLeader->id);
        
        // Tie participant 2 to leader
        $troopParticipant2 = $troopService->tieTroopParticipantToTroopLeader($troopParticipant2, $troopLeader);
        $this->assertNotNull($troopParticipant2->troopLeader, 'Participant 2 should be tied to leader');
        $this->assertSame($troopLeader->id, $troopParticipant2->troopLeader->id);
        
        // Verify leader has 2 participants
        $troopLeader = $troopLeaderRepository->get($troopLeader->id);
        $this->assertSame(2, $troopLeader->getTroopParticipantsCount());
        
        // Step 5: Participants close their registration
        // Initialize mailer settings (normally done by middleware)
        $this->initializeMailerSettings($container, $leaderUser->event);
        
        /** @var ParticipantService $participantService */
        $participantService = $container->get(ParticipantService::class);
        
        // Participant 1 closes registration
        $troopParticipant1 = $troopParticipantRepository->get($troopParticipant1->id);
        $participantService->closeRegistration($troopParticipant1);
        $participant1User = $userRepository->get($participant1User->id);
        $this->assertSame(UserStatus::Closed, $participant1User->status, 'Participant 1 should be Closed');
        
        // Participant 2 closes registration
        $troopParticipant2 = $troopParticipantRepository->get($troopParticipant2->id);
        $participantService->closeRegistration($troopParticipant2);
        $participant2User = $userRepository->get($participant2User->id);
        $this->assertSame(UserStatus::Closed, $participant2User->status, 'Participant 2 should be Closed');
        
        // Step 6: Leader closes registration
        $troopLeader = $troopLeaderRepository->get($troopLeader->id);
        $participantService->closeRegistration($troopLeader);
        $leaderUser = $userRepository->get($leaderUser->id);
        $this->assertSame(UserStatus::Closed, $leaderUser->status, 'Leader should be Closed');
        
        // Step 7: Admin approves leader
        $leaderUser->status = UserStatus::Approved;
        $userRepository->persist($leaderUser);
        
        // Step 8: Payment
        /** @var PaymentService $paymentService */
        $paymentService = $container->get(PaymentService::class);
        $troopLeader = $troopLeaderRepository->get($troopLeader->id);
        $payment = $paymentService->createAndPersistNewEventPayment($troopLeader);
        $paymentService->confirmPayment($payment);
        
        // Verify final status
        $finalLeaderUser = $userRepository->get($leaderUser->id);
        $this->assertSame(UserStatus::Paid, $finalLeaderUser->status);
    }

    /**
     * Test tying troop participant to leader using tie codes (the typical user flow).
     * This tests the tryTieTogetherWithMessages method which uses tie codes.
     */
    #[Group('troop')]
    public function testTroopTieWithTieCodes(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);
        
        // Enable troop registrations for test event
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        $this->assertNotNull($event);
        $this->enableTroopForEvent($container, $event);
        
        // Create leader
        $leaderUser = $this->registerUser($container, 'tie-test-leader@example.com');
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $leaderParticipant = $userService->createParticipantSetRole($leaderUser, 'tl');
        
        /** @var TroopLeaderRepository $troopLeaderRepository */
        $troopLeaderRepository = $container->get(TroopLeaderRepository::class);
        /** @var TroopLeader $troopLeader */
        $troopLeader = $troopLeaderRepository->get($leaderParticipant->id);
        $leaderTieCode = $troopLeader->tieCode;
        
        // Create participant
        $participantUser = $this->registerUser($container, 'tie-test-participant@example.com');
        $participantData = $userService->createParticipantSetRole($participantUser, 'tp');
        
        /** @var TroopParticipantRepository $troopParticipantRepository */
        $troopParticipantRepository = $container->get(TroopParticipantRepository::class);
        /** @var TroopParticipant $troopParticipant */
        $troopParticipant = $troopParticipantRepository->get($participantData->id);
        $participantTieCode = $troopParticipant->tieCode;
        
        // Verify not tied yet
        $this->assertNull($troopParticipant->troopLeader, 'Should not be tied initially');
        
        // Tie using tie codes
        /** @var TroopService $troopService */
        $troopService = $container->get(TroopService::class);
        $result = $troopService->tryTieTogetherWithMessages(
            $leaderTieCode,
            $participantTieCode,
            $leaderUser->event
        );
        
        $this->assertTrue($result, 'Tying should succeed');
        
        // Verify tied
        $troopParticipant = $troopParticipantRepository->get($troopParticipant->id);
        $this->assertNotNull($troopParticipant->troopLeader, 'Should be tied after operation');
        $this->assertSame($troopLeader->id, $troopParticipant->troopLeader->id);
    }

    public function testSetRoleRejectsOtWithoutSessionFlag(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);

        $email = 'ot-gate-test@example.com';
        $user = $this->registerUser($container, $email);
        $loginToken = $this->createLoginToken($container, $user);

        // Login via token (without ot_token param, so no session flag)
        $app->handle($this->createRequest(
            self::BASE_URL . '/tryLogin/' . $loginToken->token
        ));

        // Attempt to set OT role without session flag
        $app = $this->getTestApp(false);
        $responseSetRole = $app->handle($this->createRequest(
            self::BASE_URL . '/setRole',
            'POST',
            ['role' => 'ot'],
        ));

        // Should be redirected back to chooseRole (302), not to dashboard
        self::assertSame(302, $responseSetRole->getStatusCode());
        $location = $responseSetRole->getHeaderLine('Location');
        self::assertStringContainsString('chooseRole', $location);
    }

    /**
     * Test that approval with a nonzero price creates a Payment and keeps status at Approved.
     */
    public function testApprovalWithPriceCreatesPayment(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);

        $email = 'ist-price-test@example.com';
        $user = $this->registerUser($container, $email);

        // Set nonzero default price on the event
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->get($user->event->id);
        $event->defaultPrice = 100;
        $eventRepository->persist($event);

        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        /** @var IstRepository $istRepository */
        $istRepository = $container->get(IstRepository::class);
        $ist = $istRepository->get($participant->id);

        $ist->firstName = 'Test';
        $ist->lastName = 'IST';
        $ist->nickname = 'Tester';
        $ist->birthDate = new \DateTimeImmutable('1990-01-01');
        $ist->email = $email;
        $ist->gender = 'male';
        $ist->country = 'CZ';
        $ist->contingent = 'detail.contingent.czechia';
        $istRepository->persist($ist);

        $this->initializeMailerSettings($container, $user->event);

        /** @var ParticipantService $participantService */
        $participantService = $container->get(ParticipantService::class);
        $participantService->closeRegistration($ist);

        $participantService->approveRegistration($ist);

        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $finalUser = $userRepository->get($user->id);
        self::assertSame(UserStatus::Approved, $finalUser->status);
        self::assertSame(1, $ist->countWaitingPayments());
    }

    /**
     * Test that OT with price=0 goes directly to Paid after approval, with no Payment created.
     */
    public function testOrganizingTeamWithZeroPriceGoesToPaid(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);

        $email = 'ot-zero-price@example.com';
        $user = $this->registerUser($container, $email);

        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->get($user->event->id);
        $event->allowOrganizingTeam = true;
        $event->organizingTeamPrice = 0;
        $event->organizingTeamRegistrationToken = 'test-token';
        $eventRepository->persist($event);

        $_SESSION['ot_access_granted'] = true;

        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ot');

        /** @var ParticipantRepository $participantRepository */
        $participantRepository = $container->get(ParticipantRepository::class);
        $ot = $participantRepository->get($participant->id);
        $ot->firstName = 'Test';
        $ot->lastName = 'OT';
        $ot->nickname = 'Organizer';
        $ot->permanentResidence = '123 OT Street, OT City';
        $ot->gender = 'male';
        $ot->birthDate = new \DateTimeImmutable('1990-01-01');
        $ot->healthProblems = 'None';
        $ot->psychicalHealthProblems = 'None';
        $ot->notes = '';
        $ot->email = $email;
        $participantRepository->persist($ot);

        $this->initializeMailerSettings($container, $event);

        /** @var ParticipantService $participantService */
        $participantService = $container->get(ParticipantService::class);
        $participantService->closeRegistration($ot);
        $participantService->approveRegistration($ot);

        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $finalUser = $userRepository->get($user->id);
        self::assertSame(UserStatus::Paid, $finalUser->status);
        self::assertSame(0, $ot->countWaitingPayments());
    }

    private function getContainer(App $app): ContainerInterface
    {
        $container = $app->getContainer();
        if ($container === null) {
            throw new \RuntimeException('Container is null');
        }
        return $container;
    }

    private function registerUser(ContainerInterface $container, string $email): User
    {
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        if ($event === null) {
            // Fallback to first event if test-event-slug doesn't exist
            $event = $eventRepository->get(1);
        }
        
        return $userService->registerEmailUser($email, $event);
    }

    private function createLoginToken(ContainerInterface $container, User $user): LoginToken
    {
        /** @var LoginTokenRepository $loginTokenRepository */
        $loginTokenRepository = $container->get(LoginTokenRepository::class);
        
        $loginToken = new LoginToken();
        $loginToken->token = bin2hex(random_bytes(16));
        $loginToken->user = $user;
        $loginToken->used = false;
        $loginTokenRepository->persist($loginToken);
        
        return $loginToken;
    }

    private function initializeMailerSettings(ContainerInterface $container, Event $event): void
    {
        /** @var MailerSettings $mailerSettings */
        $mailerSettings = $container->get(MailerSettings::class);
        $mailerSettings->setEvent($event);
        $mailerSettings->setFullUrlLink('http://test.example.com/v2/event/' . $event->slug);

        /** @var \Slim\Views\Twig $view */
        $view = $container->get(\Slim\Views\Twig::class);
        $view->getEnvironment()->addGlobal('event', $event);
    }

    /**
     * Configure event to allow troop registrations.
     * The test event doesn't have troop limits set by default (null = 0 = full),
     * so we need to set them for troop tests to work.
     */
    private function enableTroopForEvent(ContainerInterface $container, Event $event): void
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        
        $event->allowTroops = true;
        // Use high limits to handle accumulated test data in PostgreSQL
        $event->maximalClosedTroopLeadersCount = 10000;
        $event->maximalClosedTroopParticipantsCount = 10000;
        // Also need to set min/max patrol participants count - used for validation of group sizes
        // These are used by getMinimalPpCount/getMaximalPpCount for TroopLeader validation
        $event->minimalPatrolParticipantsCount = 1;
        $event->maximalPatrolParticipantsCount = 10;
        $eventRepository->persist($event);
    }
}
