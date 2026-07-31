<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Tests\AppTestCase;

class FoodStatsAdminPageTest extends AppTestCase
{
    public function testFoodStatsPageShowsPresentOnSiteMatrix(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $participantService = $this->getService($app, ParticipantService::class);

        $event = $eventRepository->get(1);

        $email = 'food-stats-page-' . bin2hex(random_bytes(4)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');
        $participant->foodPreferences = 'detail.foodVegetarian';
        $participantRepository->persist($participant);
        $user->status = UserStatus::Paid;
        $userRepository->persist($user);
        $participantService->setAsEntered($participant);

        $adminUser = $this->createAdminUser($app);
        $adminUser->status = UserStatus::Open;
        $userRepository->persist($adminUser);

        $_SESSION['user'] = ['id' => $adminUser->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/admin/foodStats',
        ));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        // Event 1 is event_type 'wsj', whose EventTypeWsj::getLanguages() only exposes 'cs',
        // so the localization middleware always renders Czech regardless of Accept-Language;
        // asserting the English translation keys from the brief would never pass here.

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
}
