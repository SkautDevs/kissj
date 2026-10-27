<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\BankPayment\BankPayment;
use kissj\BankPayment\BankPaymentRepository;
use kissj\Event\EventRepository;
use kissj\Payment\PaymentService;
use kissj\User\User;
use kissj\User\UserLoginType;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Tests\AppTestCase;

class BankPaymentEventScopeTest extends AppTestCase
{
    public function testSetPairedChangesPaymentOfSameEvent(): void
    {
        $app = $this->getTestApp();
        $event = $this->getSmallTestEvent($this->getService($app, EventRepository::class));
        $bankPayment = $this->createBankPayment($this->getService($app, BankPaymentRepository::class), $event);

        $this->getService($app, PaymentService::class)->setBankPaymentPaired($bankPayment->id, $event);

        self::assertSame(
            BankPayment::STATUS_PAIRED,
            $this->reloadStatus($this->getService($app, BankPaymentRepository::class), $bankPayment),
        );
    }

    public function testSetUnrelatedChangesPaymentOfSameEvent(): void
    {
        $app = $this->getTestApp();
        $event = $this->getSmallTestEvent($this->getService($app, EventRepository::class));
        $bankPayment = $this->createBankPayment($this->getService($app, BankPaymentRepository::class), $event);

        $this->getService($app, PaymentService::class)->setBankPaymentUnrelated($bankPayment->id, $event);

        self::assertSame(
            BankPayment::STATUS_UNRELATED,
            $this->reloadStatus($this->getService($app, BankPaymentRepository::class), $bankPayment),
        );
    }

    public function testSetPairedRejectsPaymentOfOtherEvent(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getSmallTestEvent($eventRepository);
        $otherEvent = $this->getTestSlugEvent($eventRepository);
        $bankPayment = $this->createBankPayment($this->getService($app, BankPaymentRepository::class), $otherEvent);

        try {
            $this->getService($app, PaymentService::class)->setBankPaymentPaired($bankPayment->id, $event);
            self::fail('Expected RuntimeException');
        } catch (RuntimeException) {
            self::assertSame(
                BankPayment::STATUS_FRESH,
                $this->reloadStatus($this->getService($app, BankPaymentRepository::class), $bankPayment),
            );
        }
    }

    public function testSetUnrelatedRejectsPaymentOfOtherEvent(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getSmallTestEvent($eventRepository);
        $otherEvent = $this->getTestSlugEvent($eventRepository);
        $bankPayment = $this->createBankPayment($this->getService($app, BankPaymentRepository::class), $otherEvent);

        try {
            $this->getService($app, PaymentService::class)->setBankPaymentUnrelated($bankPayment->id, $event);
            self::fail('Expected RuntimeException');
        } catch (RuntimeException) {
            self::assertSame(
                BankPayment::STATUS_FRESH,
                $this->reloadStatus($this->getService($app, BankPaymentRepository::class), $bankPayment),
            );
        }
    }

    public function testPairedRouteChangesPaymentOfSameEvent(): void
    {
        $app = $this->getTestApp();
        $bankPayment = $this->createBankPayment(
            $this->getService($app, BankPaymentRepository::class),
            $this->getTestSlugEvent($this->getService($app, EventRepository::class)),
        );

        $response = $this->postAsAdmin('setPaymentPaired', $bankPayment);

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('/admin/payments/auto', $response->getHeaderLine('Location'));
        self::assertSame(
            BankPayment::STATUS_PAIRED,
            $this->reloadStatus($this->getService($app, BankPaymentRepository::class), $bankPayment),
        );
    }

    public function testUnrelatedRouteChangesPaymentOfSameEvent(): void
    {
        $app = $this->getTestApp();
        $bankPayment = $this->createBankPayment(
            $this->getService($app, BankPaymentRepository::class),
            $this->getTestSlugEvent($this->getService($app, EventRepository::class)),
        );

        $response = $this->postAsAdmin('setPaymentUnrelated', $bankPayment);

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('/admin/payments/auto', $response->getHeaderLine('Location'));
        self::assertSame(
            BankPayment::STATUS_UNRELATED,
            $this->reloadStatus($this->getService($app, BankPaymentRepository::class), $bankPayment),
        );
    }

    private function postAsAdmin(string $routeSegment, BankPayment $bankPayment): ResponseInterface
    {
        $userRepository = $this->getService($this->getTestApp(false), UserRepository::class);
        $admin = new User();
        $admin->event = $this->getTestSlugEvent($this->getService($this->getTestApp(false), EventRepository::class));
        $admin->role = UserRole::Admin;
        $admin->email = 'admin-bank-scope-' . bin2hex(random_bytes(4)) . '@example.com';
        $admin->loginType = UserLoginType::Email;
        $admin->status = UserStatus::Open;
        $userRepository->persist($admin);
        $_SESSION['user'] = ['id' => $admin->id];

        return $this->getTestApp(false)->handle($this->createRequest(
            '/v2/event/test-slug/admin/payments/auto/' . $routeSegment . '/' . $bankPayment->id,
            'POST',
            ['notice' => 'test'],
        ));
    }

    private function reloadStatus(BankPaymentRepository $repository, BankPayment $bankPayment): ?string
    {
        return $repository->get($bankPayment->id)->status;
    }
}
