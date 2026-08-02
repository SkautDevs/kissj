<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;
use Throwable;

class FoodStatsAdminPageTest extends AppTestCase
{
    /** @var list<Participant> participants this test entered and that must be left again on teardown */
    private array $enteredParticipants = [];

    private ?ParticipantService $participantServiceForTeardown = null;

    protected function tearDown(): void
    {
        // The dev DB is never reset between runs, so any participant left "entered" here
        // would permanently inflate every future "currently present" query on its event.
        // This must run even when a test fails partway through, hence tearDown() rather
        // than a few lines at the end of each test method - and it must run before
        // parent::tearDown() closes the DB connection these calls rely on. setAsLeaved()
        // persists, which can genuinely throw, so the exception is caught and rethrown
        // after parent::tearDown() rather than using try/finally: PHPStan's
        // phpunit.callParent check only looks for a literal parent::tearDown() call as a
        // direct statement of this method, not one nested inside a finally block.
        $cleanupFailure = null;
        try {
            if ($this->participantServiceForTeardown !== null) {
                foreach ($this->enteredParticipants as $participant) {
                    $this->participantServiceForTeardown->setAsLeaved($participant);
                }
            }
        } catch (Throwable $throwable) {
            $cleanupFailure = $throwable;
        }

        parent::tearDown();

        if ($cleanupFailure !== null) {
            throw $cleanupFailure;
        }
    }

    private function enterAndTrackForCleanup(ParticipantService $participantService, Participant $participant): Participant
    {
        $participantService->setAsEntered($participant);
        $this->enteredParticipants[] = $participant;
        $this->participantServiceForTeardown = $participantService;

        return $participant;
    }

    /** @param App<ContainerInterface> $app */
    private function fetchOtherFoodSummaryCount(App $app, string $eventSlug): int
    {
        $response = $app->handle($this->createRequest(
            '/v2/event/' . $eventSlug . '/admin/foodStats',
        ));

        self::assertSame(200, $response->getStatusCode());

        return $this->parseOtherFoodSummaryCount((string)$response->getBody());
    }

    // when the other-diet list is empty, the template renders a "nobody" paragraph and no
    // table at all, so the count must always come from the <summary> text, not from counting
    // rendered rows
    private function parseOtherFoodSummaryCount(string $body): int
    {
        self::assertSame(
            1,
            preg_match('/jiná strava - detail \((\d+)\)/u', $body, $summaryMatches),
            'summary does not carry a participant count',
        );

        return (int)$summaryMatches[1];
    }

    public function testFoodStatsPageShowsPresentOnSiteMatrix(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $participantService = $this->getService($app, ParticipantService::class);

        $event = $eventRepository->findBySlug('obrok37');
        self::assertNotNull($event);

        $email = 'food-stats-page-' . bin2hex(random_bytes(4)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');
        $participant->foodPreferences = 'detail.foodVegetarian';
        $participantRepository->persist($participant);
        $user->status = UserStatus::Paid;
        $userRepository->persist($user);
        $this->enterAndTrackForCleanup($participantService, $participant);

        $adminUser = $this->createAdminUser($app);
        $adminUser->status = UserStatus::Open;
        // createAdminUser() builds the admin against event 1, but LoggedOnlyMiddleware logs the
        // user out when their event does not match the event in the URL, so it must be repointed.
        $adminUser->event = $event;
        $userRepository->persist($adminUser);

        $_SESSION['user'] = ['id' => $adminUser->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/admin/foodStats',
        ));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        // obrok37 is event type 'obrok', whose getLanguages() exposes only 'cs', so the
        // localization middleware always renders Czech regardless of Accept-Language.

        // 'aktuálně přítomní na akci' and 'celkem' are static template markup that renders
        // regardless of the data passed in, so they alone can't prove data actually reached
        // the page. Slice out just the new matrix card (header through its closing table) and
        // assert the data-driven cells - a vegetarian column and an IST row - are inside it.
        // The bare strings aren't otherwise discriminating: the pre-existing day-by-day tables
        // also have diet headers and an IST card, so the slice is what makes this test meaningful.
        $headerPosition = strpos($body, 'aktuálně přítomní na akci');
        self::assertNotFalse($headerPosition, 'present on-site matrix header not found in response body');

        $tableEndPosition = strpos($body, '</table>', $headerPosition);
        self::assertNotFalse($tableEndPosition, 'present on-site matrix table not closed in response body');

        $matrixSection = substr($body, $headerPosition, $tableEndPosition - $headerPosition);

        self::assertStringContainsString('vegetariánské', $matrixSection);
        self::assertStringContainsString('servis tým', $matrixSection);
    }

    public function testFoodStatsPageShowsOtherFoodDetailRows(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $participantService = $this->getService($app, ParticipantService::class);

        $event = $eventRepository->findBySlug('obrok37');
        self::assertNotNull($event);

        // admin session must exist before the "before" snapshot below, since that request
        // needs to reach the admin-only foodStats page too
        $adminUser = $this->createAdminUser($app);
        $adminUser->status = UserStatus::Open;
        $adminUser->event = $event;
        $userRepository->persist($adminUser);

        $_SESSION['user'] = ['id' => $adminUser->id];
        $app = $this->getTestApp(false);

        // the shared dev DB may already hold other "other diet" participants, so the count is
        // asserted as a delta across a fixture entry rather than against a fixed or
        // self-referential number
        $countBefore = $this->fetchOtherFoodSummaryCount($app, $event->slug);

        $nameSuffix = bin2hex(random_bytes(4));
        $email = 'food-stats-other-' . $nameSuffix . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');
        $participant->firstName = 'Otherfood';
        $participant->lastName = 'Tester' . $nameSuffix;
        $participant->foodPreferences = 'detail.foodOther';
        $participant->notes = 'jen syrova strava ' . $nameSuffix;
        $participant->healthProblems = 'alergie na arasidy ' . $nameSuffix;
        $participantRepository->persist($participant);
        $user->status = UserStatus::Paid;
        $userRepository->persist($user);
        $this->enterAndTrackForCleanup($participantService, $participant);

        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/admin/foodStats',
        ));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();

        // slice out the detail section so the assertions cannot accidentally match the
        // pre-existing day-by-day cards further down the page. Bounded on </details> rather
        // than </table>: an empty other-diet list renders only a <p> and no table at all, so
        // anchoring on </table> would read past the section into the next card.
        $headerPosition = strpos($body, 'jiná strava - detail');
        self::assertNotFalse($headerPosition, 'other food detail header not found in response body');

        $sectionEndPosition = strpos($body, '</details>', $headerPosition);
        self::assertNotFalse($sectionEndPosition, 'other food detail section not closed in response body');

        $detailSection = substr($body, $headerPosition, $sectionEndPosition - $headerPosition);

        self::assertStringContainsString('Tester' . $nameSuffix, $detailSection);
        self::assertStringContainsString('jen syrova strava ' . $nameSuffix, $detailSection);
        self::assertStringContainsString('alergie na arasidy ' . $nameSuffix, $detailSection);
        self::assertStringContainsString('servis tým', $detailSection);

        // the section must be collapsed into a <details> whose <summary> carries the header
        $detailsPosition = strrpos(substr($body, 0, $headerPosition), '<details');
        self::assertNotFalse($detailsPosition, 'other food detail section is not wrapped in <details>');

        $summaryPrefix = substr($body, $detailsPosition, $headerPosition - $detailsPosition);
        self::assertStringContainsString('<summary', $summaryPrefix);
        self::assertStringNotContainsString('</details>', $summaryPrefix);

        // the fixture participant was just entered, so the table (and its tbody) must exist;
        // count closing </tr> inside the tbody rather than opening <tr> minus the head row -
        // that stays correct even if the head row's own markup changes
        $tbodyPosition = strpos($detailSection, '<tbody>');
        self::assertNotFalse($tbodyPosition, 'other food detail table has no <tbody>');

        $renderedRows = substr_count($detailSection, '</tr>', $tbodyPosition);
        self::assertGreaterThanOrEqual(1, $renderedRows);

        $countAfter = $this->parseOtherFoodSummaryCount($body);
        self::assertSame($countBefore + 1, $countAfter);
    }

    public function testFoodStatsPageShowsEmDashForEmptyOtherFoodFields(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $participantService = $this->getService($app, ParticipantService::class);

        $event = $eventRepository->findBySlug('obrok37');
        self::assertNotNull($event);

        $nameSuffix = bin2hex(random_bytes(4));
        $email = 'food-stats-other-empty-' . $nameSuffix . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');
        $participant->firstName = 'Otherfoodempty';
        $participant->lastName = 'Blank' . $nameSuffix;
        $participant->foodPreferences = 'detail.foodOther';
        // notes, healthProblems and contingent are intentionally left null so the em-dash
        // fallback branches render - this is exactly the code path where bare `.notes` /
        // `.healthProblems` / `.contingent` access blows up via LeanMapper's __call (see
        // "added other-food detail table to admin food dashboard").
        // Never give this fixture a contingent: the dev DB is never reset between runs, so an
        // 'other food' participant on obrok37 with a contingent would permanently flip
        // showContingent to true for every future run of this suite.
        $participantRepository->persist($participant);
        $user->status = UserStatus::Paid;
        $userRepository->persist($user);
        $this->enterAndTrackForCleanup($participantService, $participant);

        $adminUser = $this->createAdminUser($app);
        $adminUser->status = UserStatus::Open;
        $adminUser->event = $event;
        $userRepository->persist($adminUser);

        $_SESSION['user'] = ['id' => $adminUser->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/admin/foodStats',
        ));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();

        $headerPosition = strpos($body, 'jiná strava - detail');
        self::assertNotFalse($headerPosition, 'other food detail header not found in response body');

        $tableEndPosition = strpos($body, '</table>', $headerPosition);
        self::assertNotFalse($tableEndPosition, 'other food detail table not closed in response body');

        $detailSection = substr($body, $headerPosition, $tableEndPosition - $headerPosition);

        // isolate this fixture's own row - the shared dev DB has other 'other food' participants
        // in the same table, whose cells must not be counted here
        $namePosition = strpos($detailSection, 'Blank' . $nameSuffix);
        self::assertNotFalse($namePosition, 'empty-fields participant not found in detail section');

        $rowStart = strrpos(substr($detailSection, 0, $namePosition), '<tr>');
        self::assertNotFalse($rowStart, 'opening <tr> not found for empty-fields participant row');

        $rowEnd = strpos($detailSection, '</tr>', $namePosition);
        self::assertNotFalse($rowEnd, 'closing </tr> not found for empty-fields participant row');

        $row = substr($detailSection, $rowStart, $rowEnd - $rowStart);

        // notice and issues are always-rendered columns for this row; contingent is additionally
        // gated by showContingent, whose value depends on other tests' fixtures in the shared dev
        // DB, so assert at least the two guaranteed em dashes rather than an exact count
        self::assertGreaterThanOrEqual(2, substr_count($row, '—'));
    }

    public function testFoodStatsPageOmitsContingentColumnWhenNobodyHasOne(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $participantService = $this->getService($app, ParticipantService::class);

        $event = $eventRepository->findBySlug('obrok37');
        self::assertNotNull($event);

        $nameSuffix = bin2hex(random_bytes(4));
        $email = 'food-stats-other-nocontingent-' . $nameSuffix . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');
        $participant->firstName = 'Otherfoodnocontingent';
        $participant->lastName = 'NoContingent' . $nameSuffix;
        $participant->foodPreferences = 'detail.foodOther';
        // contingent intentionally left null - this fixture, plus tearDown() leaving every
        // participant these tests enter (here and in ParticipantStatisticsServiceFoodTest),
        // is what keeps showContingent false and this test deterministic
        $participantRepository->persist($participant);
        $user->status = UserStatus::Paid;
        $userRepository->persist($user);
        $this->enterAndTrackForCleanup($participantService, $participant);

        $adminUser = $this->createAdminUser($app);
        $adminUser->status = UserStatus::Open;
        $adminUser->event = $event;
        $userRepository->persist($adminUser);

        $_SESSION['user'] = ['id' => $adminUser->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/admin/foodStats',
        ));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();

        $headerPosition = strpos($body, 'jiná strava - detail');
        self::assertNotFalse($headerPosition, 'other food detail header not found in response body');

        $tableEndPosition = strpos($body, '</table>', $headerPosition);
        self::assertNotFalse($tableEndPosition, 'other food detail table not closed in response body');

        $detailSection = substr($body, $headerPosition, $tableEndPosition - $headerPosition);

        // 'Kontingent' (detail.contingentTitle) only renders inside the header's showContingent
        // guard, so its absence here covers the header row
        self::assertStringNotContainsString('Kontingent', $detailSection);

        // isolate this fixture's own row and confirm the body doesn't add a contingent cell
        // either - 5 columns (name, role, food, notice, issues) rather than 6
        $namePosition = strpos($detailSection, 'NoContingent' . $nameSuffix);
        self::assertNotFalse($namePosition, 'no-contingent participant not found in detail section');

        $rowStart = strrpos(substr($detailSection, 0, $namePosition), '<tr>');
        self::assertNotFalse($rowStart, 'opening <tr> not found for no-contingent participant row');

        $rowEnd = strpos($detailSection, '</tr>', $namePosition);
        self::assertNotFalse($rowEnd, 'closing </tr> not found for no-contingent participant row');

        $row = substr($detailSection, $rowStart, $rowEnd - $rowStart);

        self::assertSame(5, substr_count($row, '<td>'));
    }
}
