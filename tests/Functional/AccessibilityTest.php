<?php

declare(strict_types=1);

namespace Tests\Functional;

use DOMDocument;
use DOMElement;
use DOMXPath;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class AccessibilityTest extends AppTestCase
{
    public function testLandingPageHasLandmarksSkipLinkAndAccessibleImages(): void
    {
        $app = $this->getTestApp();
        $xpath = $this->requestHtml($app, '/v2/kissj/events');

        $this->assertBaseLayoutAccessibility($xpath);

        // Landing marquee logos duplicate figcaption text, so img alt must be empty
        $marqueeImages = $xpath->query('//figure[contains(@class, "landing-logo-item")]//img');
        self::assertNotFalse($marqueeImages);
        self::assertGreaterThan(0, $marqueeImages->length);
        foreach ($marqueeImages as $img) {
            self::assertInstanceOf(DOMElement::class, $img);
            self::assertTrue($img->hasAttribute('alt'));
            self::assertSame('', $img->getAttribute('alt'));
        }
    }

    public function testLoginPageHasAccessibleFormAndLandmarks(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $xpath = $this->requestHtml($app, '/v2/event/' . $event->slug . '/login');

        $this->assertBaseLayoutAccessibility($xpath);
        $this->assertAllFormControlsLabeled($xpath);
        $this->assertHeadingHierarchyValid($xpath);
    }

    public function testChangeDetailsFormHasValidLabelsFieldsetsAndNoInvalidTextareaType(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $user = $this->createParticipantUser($app, $event, 'ist', UserStatus::Open);

        $_SESSION['user'] = ['id' => $user->id];
        $xpath = $this->requestHtml(
            $this->getTestApp(false),
            '/v2/event/' . $event->slug . '/participant/showChangeDetails',
        );

        $this->assertBaseLayoutAccessibility($xpath);
        $this->assertAllFormControlsLabeled($xpath);
        $this->assertHeadingHierarchyValid($xpath);

        // Textareas must not carry invalid type attribute
        $textareasWithType = $xpath->query('//textarea[@type]');
        self::assertNotFalse($textareasWithType);
        self::assertSame(0, $textareasWithType->length, 'Textarea elements must not have a type attribute');

        // preferredPosition checkbox group must be inside a fieldset with a legend
        $checkboxes = $xpath->query('//input[@name="preferredPosition[]"]');
        self::assertNotFalse($checkboxes);
        if ($checkboxes->length > 0) {
            $firstCheckbox = $checkboxes->item(0);
            self::assertInstanceOf(DOMElement::class, $firstCheckbox);
            $fieldset = $xpath->query('ancestor::fieldset[1]/legend', $firstCheckbox);
            self::assertNotFalse($fieldset);
            self::assertSame(
                1,
                $fieldset->length,
                'preferredPosition checkboxes must be grouped in a fieldset with a legend',
            );
        }
    }

    public function testParticipantDashboardIconLinksAndHeadingsAreAccessible(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $user = $this->createParticipantUser($app, $event, 'pl', UserStatus::Open);

        $patrolLeader = $this->getService($app, PatrolLeaderRepository::class)->getOneBy(['user_id' => $user->id]);
        $pp = new PatrolParticipant();
        $pp->patrolLeader = $patrolLeader;
        $pp->firstName = 'Jan';
        $pp->lastName = 'Novak';
        $this->getService($app, PatrolParticipantRepository::class)->persist($pp);

        $_SESSION['user'] = ['id' => $user->id];
        $xpath = $this->requestHtml($this->getTestApp(false), '/v2/event/' . $event->slug . '/participant/dashboard');

        $this->assertBaseLayoutAccessibility($xpath);
        $this->assertHeadingHierarchyValid($xpath);

        // Active step must be marked with aria-current="step"
        $activeStep = $xpath->query('//ul[contains(@class, "steps")]/li[@aria-current="step"]');
        self::assertNotFalse($activeStep);
        self::assertSame(1, $activeStep->length, 'Exactly one registration step must have aria-current="step"');

        // Icon-only links must have non-empty aria-label and aria-hidden="true" on the icon
        $iconLinks = $xpath->query('//a[.//i[contains(@class, "material-icons")]]');
        self::assertNotFalse($iconLinks);
        self::assertGreaterThan(0, $iconLinks->length);
        foreach ($iconLinks as $link) {
            self::assertInstanceOf(DOMElement::class, $link);
            self::assertNotSame(
                '',
                trim($link->getAttribute('aria-label')),
                'Icon-only link must have a non-empty aria-label',
            );
            $icons = $xpath->query('.//i[contains(@class, "material-icons")]', $link);
            self::assertNotFalse($icons);
            foreach ($icons as $icon) {
                self::assertInstanceOf(DOMElement::class, $icon);
                self::assertSame(
                    'true',
                    $icon->getAttribute('aria-hidden'),
                    'Decorative Material Icons must have aria-hidden="true"',
                );
            }
        }
    }

    public function testAdminDashboardAndApprovePagesAreAccessible(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $admin = $this->createTestAdminUser($app, $event);

        // Create a closed IST participant so approve page renders a card
        $this->createParticipantUser($app, $event, 'ist', UserStatus::Closed);

        $_SESSION['user'] = ['id' => $admin->id];
        $dashboardXpath = $this->requestHtml(
            $this->getTestApp(false),
            '/v2/event/' . $event->slug . '/admin/dashboard',
        );
        $this->assertBaseLayoutAccessibility($dashboardXpath);
        $this->assertAllFormControlsLabeled($dashboardXpath);
        $this->assertHeadingHierarchyValid($dashboardXpath);

        $_SESSION['user'] = ['id' => $admin->id];
        $approveXpath = $this->requestHtml($this->getTestApp(false), '/v2/event/' . $event->slug . '/admin/approving');
        $this->assertBaseLayoutAccessibility($approveXpath);
        $this->assertAllFormControlsLabeled($approveXpath);
        $this->assertHeadingHierarchyValid($approveXpath);
    }

    public function testAdminTablesHaveScopeAttributes(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $admin = $this->createTestAdminUser($app, $event);

        foreach (['/admin/finances', '/admin/foodStats', '/admin/organizing-team'] as $path) {
            $_SESSION['user'] = ['id' => $admin->id];
            $xpath = $this->requestHtml($this->getTestApp(false), '/v2/event/' . $event->slug . $path);
            $this->assertHeadingHierarchyValid($xpath);
            $this->assertAllFormControlsLabeled($xpath);

            $headers = $xpath->query('//table//th');
            self::assertNotFalse($headers);
            foreach ($headers as $th) {
                self::assertInstanceOf(DOMElement::class, $th);
                $scope = $th->getAttribute('scope');
                self::assertContains(
                    $scope,
                    ['col', 'row'],
                    sprintf('<th> on %s must have scope="col" or scope="row"', $path),
                );
            }
        }
    }

    private function assertBaseLayoutAccessibility(DOMXPath $xpath): void
    {
        // Skip link and main target
        $skipLinks = $xpath->query('//body/a[contains(@class, "skip-link") and @href="#main-content"]');
        self::assertNotFalse($skipLinks);
        self::assertSame(1, $skipLinks->length, 'Page must have a skip-to-content link targeting #main-content');

        $main = $xpath->query('//main[@id="main-content"]');
        self::assertNotFalse($main);
        self::assertSame(1, $main->length, 'Page must have <main id="main-content">');
        $mainEl = $main->item(0);
        self::assertInstanceOf(DOMElement::class, $mainEl);
        self::assertFalse($mainEl->hasAttribute('role'), '<main> should not have redundant role="main"');

        // Header landmark
        $headers = $xpath->query('//header');
        self::assertNotFalse($headers);
        self::assertSame(1, $headers->length, 'Page must have a <header> landmark');

        // Nav landmarks must have aria-label
        $navs = $xpath->query('//nav');
        self::assertNotFalse($navs);
        self::assertGreaterThanOrEqual(1, $navs->length, 'Page must have at least one <nav> landmark');
        foreach ($navs as $nav) {
            self::assertInstanceOf(DOMElement::class, $nav);
            self::assertNotSame('', trim($nav->getAttribute('aria-label')), '<nav> must have a non-empty aria-label');
        }

        // All images must have alt attributes and no raw camelCase skautisLogo
        $images = $xpath->query('//img');
        self::assertNotFalse($images);
        foreach ($images as $img) {
            self::assertInstanceOf(DOMElement::class, $img);
            self::assertTrue($img->hasAttribute('alt'), 'Every <img> must have an alt attribute');
            self::assertNotSame('skautisLogo', $img->getAttribute('alt'), 'Alt text must not be raw "skautisLogo"');
            self::assertStringStartsNotWith(
                'logo of ',
                $img->getAttribute('alt'),
                'Footer logo alt must be localized, not hardcoded English "logo of ..."',
            );
        }

        // Flash alerts must have role="alert" or role="status"
        $alerts = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " alert ")]');
        self::assertNotFalse($alerts);
        foreach ($alerts as $alert) {
            self::assertInstanceOf(DOMElement::class, $alert);
            self::assertContains($alert->getAttribute('role'), ['alert', 'status']);
        }
    }

    private function assertAllFormControlsLabeled(DOMXPath $xpath): void
    {
        // Every <label for="X"> must point to an existing element with id="X"
        $labels = $xpath->query('//label[@for]');
        self::assertNotFalse($labels);
        foreach ($labels as $label) {
            self::assertInstanceOf(DOMElement::class, $label);
            $for = $label->getAttribute('for');
            self::assertNotSame('', $for);
            $targets = $xpath->query('//*[@id="' . $for . '"]');
            self::assertNotFalse($targets);
            self::assertGreaterThan(
                0,
                $targets->length,
                sprintf('<label for="%s"> has no matching element with id="%s"', $for, $for),
            );
        }

        // Every interactive form control must have an accessible name
        $controls = $xpath->query(
            '//input[not(@type="hidden") and not(@type="submit") and not(@type="button")]'
            . ' | //select | //textarea',
        );
        self::assertNotFalse($controls);
        foreach ($controls as $control) {
            self::assertInstanceOf(DOMElement::class, $control);
            $id = $control->getAttribute('id');
            $ariaLabel = trim($control->getAttribute('aria-label'));
            $wrappingLabel = $xpath->query('ancestor::label[1]', $control);
            $hasWrappingLabel = $wrappingLabel !== false && $wrappingLabel->length > 0;
            $hasForLabel = false;
            if ($id !== '') {
                $forLabels = $xpath->query('//label[@for="' . $id . '"]');
                $hasForLabel = $forLabels !== false && $forLabels->length > 0;
            }
            self::assertTrue(
                $hasForLabel || $hasWrappingLabel || $ariaLabel !== '',
                sprintf(
                    'Form control <%s name="%s" id="%s"> is missing an accessible label',
                    $control->tagName,
                    $control->getAttribute('name'),
                    $id,
                ),
            );
        }
    }

    private function assertHeadingHierarchyValid(DOMXPath $xpath): void
    {
        $headings = $xpath->query('//h1 | //h2 | //h3 | //h4 | //h5 | //h6');
        self::assertNotFalse($headings);
        $previousLevel = 0;
        foreach ($headings as $heading) {
            self::assertInstanceOf(DOMElement::class, $heading);
            $level = (int) substr($heading->tagName, 1);
            if ($previousLevel > 0) {
                self::assertLessThanOrEqual(
                    $previousLevel + 1,
                    $level,
                    sprintf(
                        'Heading level skipped from <h%d> to <h%d> ("%s")',
                        $previousLevel,
                        $level,
                        trim($heading->textContent),
                    ),
                );
            }
            $previousLevel = $level;
        }
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function requestHtml(App $app, string $path): DOMXPath
    {
        $response = $app->handle($this->createRequest($path));
        self::assertSame(200, $response->getStatusCode(), 'Expected 200 OK for ' . $path);

        $document = new DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>' . (string) $response->getBody());
        libxml_clear_errors();

        return new DOMXPath($document);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createParticipantUser(App $app, Event $event, string $role, UserStatus $status): User
    {
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('a11y-' . uniqid('', true) . '@example.com', $event);
        $userService->createParticipantSetRole($user, $role);

        $user->status = $status;
        $this->getService($app, UserRepository::class)->persist($user);

        return $user;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createTestAdminUser(App $app, Event $event): User
    {
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('a11y-admin-' . uniqid('', true) . '@example.com', $event);
        $user->role = UserRole::Admin;
        $this->getService($app, UserRepository::class)->persist($user);

        return $user;
    }
}
