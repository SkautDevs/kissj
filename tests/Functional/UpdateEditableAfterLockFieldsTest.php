<?php declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\ContentArbiter\ContentArbiterItemType;
use kissj\Event\EventRepository;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\ParticipantService;
use kissj\User\UserService;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class UpdateEditableAfterLockFieldsTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';

    public function testOnlyEditableFieldsAreUpdated(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);

        $user = $this->registerUser($container, 'after-lock-test@example.com');

        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        /** @var IstRepository $istRepository */
        $istRepository = $container->get(IstRepository::class);
        $ist = $istRepository->get($participant->id);

        $ist->firstName = 'Original';
        $ist->lastName = 'Name';
        $ist->healthProblems = 'none';
        $ist->notes = 'original note';
        $ist->gender = 'man';
        $istRepository->persist($ist);

        // Only allow health and notes to be edited after lock
        $editableItems = [
            new ContentArbiterItem(
                slug: 'healthProblems',
                allowed: true,
                type: ContentArbiterItemType::Text,
                order: 190,
                label: 'detail.issues',
                required: false,
                editableAfterLock: true,
            ),
            new ContentArbiterItem(
                slug: 'notes',
                allowed: true,
                type: ContentArbiterItemType::Textarea,
                order: 500,
                label: 'detail.notice',
                required: false,
                editableAfterLock: true,
            ),
        ];

        $params = [
            'healthProblems' => 'updated health',
            'notes' => 'updated note',
            'firstName' => 'Hacker',
            'lastName' => 'Attack',
            'gender' => 'other',
        ];

        /** @var ParticipantService $participantService */
        $participantService = $container->get(ParticipantService::class);
        $participantService->updateEditableAfterLockFields($ist, $params, $editableItems);

        $refreshed = $istRepository->get($ist->id);

        // Editable fields should be updated
        self::assertSame('updated health', $refreshed->healthProblems);
        self::assertSame('updated note', $refreshed->notes);

        // Non-editable fields should remain unchanged
        self::assertSame('Original', $refreshed->firstName);
        self::assertSame('Name', $refreshed->lastName);
        self::assertSame('man', $refreshed->gender);
    }

    private function getContainer(App $app): ContainerInterface
    {
        $container = $app->getContainer();
        if ($container === null) {
            throw new \RuntimeException('Container is null');
        }

        return $container;
    }

    private function registerUser(ContainerInterface $container, string $email): \kissj\User\User
    {
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);

        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        if ($event === null) {
            $event = $eventRepository->get(1);
        }

        return $userService->registerEmailUser($email, $event);
    }
}
