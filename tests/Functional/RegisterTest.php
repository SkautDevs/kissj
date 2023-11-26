<?php declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\User\UserService;
use Psr\Container\ContainerInterface;
use Tests\AppTestCase;

class RegisterTest extends AppTestCase
{
    public function testRegisterAndLogin(): void
    {
        $app = $this->getTestApp();
        /** @var ContainerInterface $container */
        $container = $app->getContainer();
        
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $testEvent = $eventRepository->get(1);

        $email = 'test@example.com';
        $user = $userService->registerEmailUser($email, $testEvent);

        $this->markTestSkipped('TODO fix generation of link used in email');
        $token = $userService->sendLoginTokenByMail($email, $this->createRequest(''), $testEvent);
        $loadedUser = $userService->getLoginTokenFromStringToken($token)->user;

        $this->assertEquals($user->id, $loadedUser->id);
    }
}
