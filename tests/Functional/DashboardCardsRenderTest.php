<?php

declare(strict_types=1);

namespace Tests\Functional;

use DOMDocument;
use DOMElement;
use DOMXPath;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Troop\TroopLeaderRepository;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class DashboardCardsRenderTest extends AppTestCase
{
    private const string CARD = 'contains(concat(" ", normalize-space(@class), " "), " card ")';
    private const string CARD_DOUBLE = 'contains(concat(" ", normalize-space(@class), " "), " card-double ")';
    private const string CARD_DOUBLE_LAYOUT = 'contains(concat(" ", normalize-space(@class), " "), " card-double-layout ")';
    private const string LOCK_LINK = '//a[substring(@href, string-length(@href) - string-length("/closeRegistration") + 1) = "/closeRegistration"]';
    private const string TP_TIE_FORM = '//form[substring(@action, string-length(@action) - string-length("/tieParticipantToTroopByParticipant") + 1) = "/tieParticipantToTroopByParticipant"]';

    public function testOpenTroopLeaderHasTieFormInJoinRowAndLockInOwnCard(): void
    {
        $xpath = $this->renderDashboard('tl', UserStatus::Open);

        $tieForm = $this->single($xpath, '//form[substring(@action, string-length(@action) - string-length("/tieParticipantToTroopByLeader") + 1) = "/tieParticipantToTroopByLeader"]');
        $joinCard = $this->single($xpath, 'ancestor::div[' . self::CARD_DOUBLE . '][1]', $tieForm);
        self::assertTrue($this->hasClass($joinCard, 'card'));
        $joinRow = $joinCard->parentNode;
        self::assertInstanceOf(DOMElement::class, $joinRow);
        self::assertTrue($this->hasClass($joinRow, 'card-double-layout'));

        $lockCard = $this->lockCard($xpath);
        self::assertStringEndsWith('/participant/closeRegistration', $this->single($xpath, self::LOCK_LINK)->getAttribute('href'));
        self::assertSame(0, $this->countNodes($xpath, './/form', $lockCard));
        self::assertSame(0, $this->countNodes($xpath, './/ol', $lockCard));
    }

    public function testOpenPatrolLeaderHasAddParticipantInJoinCardAndLockInOwnCard(): void
    {
        $xpath = $this->renderDashboard('pl', UserStatus::Open);

        $addLink = $this->single($xpath, '//a[substring(@href, string-length(@href) - string-length("/patrol/addParticipant") + 1) = "/patrol/addParticipant"]');
        $joinCard = $this->single($xpath, 'ancestor::div[' . self::CARD_DOUBLE . '][1]', $addLink);
        $joinRow = $joinCard->parentNode;
        self::assertInstanceOf(DOMElement::class, $joinRow);
        self::assertTrue($this->hasClass($joinRow, 'card-double-layout'));

        $lockCard = $this->lockCard($xpath);
        self::assertStringEndsWith('/patrol/closeRegistration', $this->single($xpath, self::LOCK_LINK)->getAttribute('href'));
        self::assertSame(0, $this->countNodes($xpath, './/a[contains(@href, "/patrol/addParticipant")]', $lockCard));
        self::assertSame(0, $this->countNodes($xpath, './/ol', $lockCard));
    }

    public function testOpenTroopParticipantWithoutTroopHasTieFormInJoinRow(): void
    {
        $xpath = $this->renderDashboard('tp', UserStatus::Open);

        $this->assertTroopParticipantTieFormInJoinRow($xpath);
        $lockCard = $this->lockCard($xpath);
        self::assertStringEndsWith('/participant/closeRegistration', $this->single($xpath, self::LOCK_LINK)->getAttribute('href'));
        self::assertSame(0, $this->countNodes($xpath, './/form', $lockCard));
    }

    public function testClosedTroopParticipantWithoutTroopStillHasTieFormInJoinRowAndNoLock(): void
    {
        $xpath = $this->renderDashboard('tp', UserStatus::Closed);

        $this->assertTroopParticipantTieFormInJoinRow($xpath);
        self::assertSame(0, $this->countNodes($xpath, self::LOCK_LINK));
    }

    public function testOpenTroopParticipantWithTroopHasNoJoinRowAndTroopInDetailsCard(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));

        $leaderUser = $this->createParticipantUser($app, $event, 'tl', UserStatus::Open);
        $troopLeaderRepository = $this->getService($app, TroopLeaderRepository::class);
        $troopLeader = $troopLeaderRepository->getFromUser($leaderUser);
        $troopLeader->patrolName = 'Dashboard Cards Troop';
        $troopLeaderRepository->persist($troopLeader);

        $participantUser = $this->createParticipantUser($app, $event, 'tp', UserStatus::Open);
        $troopParticipantRepository = $this->getService($app, TroopParticipantRepository::class);
        $troopParticipant = $troopParticipantRepository->getFromUser($participantUser);
        $troopParticipant->troopLeader = $troopLeader;
        $troopParticipantRepository->persist($troopParticipant);

        $xpath = $this->requestDashboard($participantUser, $event);

        self::assertSame(0, $this->countNodes($xpath, '//div[' . self::CARD_DOUBLE_LAYOUT . ']'));
        $editDetailsLink = $this->single($xpath, '//a[contains(@href, "/participant/showChangeDetails")]');
        $detailsCard = $this->single($xpath, 'ancestor::div[' . self::CARD . '][1]', $editDetailsLink);
        self::assertSame(1, $this->countNodes($xpath, './/h2[contains(., "Dashboard Cards Troop")]', $detailsCard));
        self::assertStringEndsWith('/participant/closeRegistration', $this->single($xpath, self::LOCK_LINK)->getAttribute('href'));
        $this->lockCard($xpath);
    }

    public function testOpenIstHasNoJoinRowAndLockInOwnCard(): void
    {
        $xpath = $this->renderDashboard('ist', UserStatus::Open);

        self::assertSame(0, $this->countNodes($xpath, '//div[' . self::CARD_DOUBLE_LAYOUT . ']'));
        $lockCard = $this->lockCard($xpath);
        self::assertStringEndsWith('/participant/closeRegistration', $this->single($xpath, self::LOCK_LINK)->getAttribute('href'));
        self::assertSame(0, $this->countNodes($xpath, './/a[contains(@href, "/participant/showChangeDetails")]', $lockCard));
    }

    public function testClosedTroopLeaderHasNoJoinRowAndNoLock(): void
    {
        $xpath = $this->renderDashboard('tl', UserStatus::Closed);

        self::assertSame(0, $this->countNodes($xpath, '//div[' . self::CARD_DOUBLE_LAYOUT . ']'));
        self::assertSame(0, $this->countNodes($xpath, self::LOCK_LINK));
    }

    private function assertTroopParticipantTieFormInJoinRow(DOMXPath $xpath): void
    {
        $tieForm = $this->single($xpath, self::TP_TIE_FORM);
        $joinCard = $this->single($xpath, 'ancestor::div[' . self::CARD_DOUBLE . '][1]', $tieForm);
        self::assertTrue($this->hasClass($joinCard, 'card'));
        $joinRow = $joinCard->parentNode;
        self::assertInstanceOf(DOMElement::class, $joinRow);
        self::assertTrue($this->hasClass($joinRow, 'card-double-layout'));
    }

    private function lockCard(DOMXPath $xpath): DOMElement
    {
        $lockLink = $this->single($xpath, self::LOCK_LINK);

        return $this->single($xpath, 'ancestor::div[' . self::CARD . '][1]', $lockLink);
    }

    private function renderDashboard(string $role, UserStatus $status): DOMXPath
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $user = $this->createParticipantUser($app, $event, $role, $status);

        return $this->requestDashboard($user, $event);
    }

    private function requestDashboard(User $user, Event $event): DOMXPath
    {
        $_SESSION['user'] = ['id' => $user->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/participant/dashboard'));
        self::assertSame(200, $response->getStatusCode());

        $document = new DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML((string) $response->getBody());
        libxml_clear_errors();

        return new DOMXPath($document);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createParticipantUser(App $app, Event $event, string $role, UserStatus $status): User
    {
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('dashboard-cards-' . uniqid('', true) . '@example.com', $event);
        $userService->createParticipantSetRole($user, $role);

        $user->status = $status;
        $this->getService($app, UserRepository::class)->persist($user);

        return $user;
    }

    private function single(DOMXPath $xpath, string $expression, ?DOMElement $context = null): DOMElement
    {
        $nodes = $xpath->query($expression, $context);
        self::assertNotFalse($nodes);
        self::assertCount(1, $nodes, $expression);
        $node = $nodes->item(0);
        self::assertInstanceOf(DOMElement::class, $node);

        return $node;
    }

    private function countNodes(DOMXPath $xpath, string $expression, ?DOMElement $context = null): int
    {
        $nodes = $xpath->query($expression, $context);
        self::assertNotFalse($nodes);

        return $nodes->length;
    }

    private function hasClass(DOMElement $element, string $class): bool
    {
        return in_array($class, explode(' ', $element->getAttribute('class')), true);
    }
}
