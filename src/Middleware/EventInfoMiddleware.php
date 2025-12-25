<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Event\EventType\EventType;
use kissj\Mailer\MailerSettings;
use kissj\Skautis\SkautisService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Translator;

class EventInfoMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly Twig $view,
        private readonly MailerSettings $mailerSettings,
        private readonly SkautisService $skautisService,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        $eventSlug = $route?->getArgument('eventSlug') ?? '';
        $event = $this->eventRepository->findBySlug($eventSlug);
        $request = $request->withAttribute('event', $event);

        if ($event instanceof Event) {
            $eventType = $event->getEventType();

            $this->enrichTemplatesWithEvent($event);
            $this->addEventSpecificTranslations($eventType);
            $this->enrichMailerSettingsWithEvent($event, $request);

            if ($eventType->isLoginSkautisAllowed()) {
                $this->initSkautis($event);
            }
        }

        return $handler->handle($request);
    }

    public function enrichTemplatesWithEvent(Event $event): void
    {
        $this->view->getEnvironment()->addGlobal('event', $event);
    }

    public function addEventSpecificTranslations(EventType $eventType): void
    {
        $translationFilePaths = $eventType->getTranslationFilePaths();
        if ($translationFilePaths !== []) {
            /** @var TranslationExtension $translatorExtension */
            $translatorExtension = $this->view->getEnvironment()->getExtension(TranslationExtension::class);
            /** @var Translator $translator */
            $translator = $translatorExtension->getTranslator();

            foreach ($translationFilePaths as $locale => $path) {
                $translator->addResource('yaml', $path, $locale);
            }
        }
    }

    public function enrichMailerSettingsWithEvent(Event $event, Request $request): void
    {
        $this->mailerSettings->setEvent($event);
        $this->mailerSettings->setFullUrlLink(
            $this->getRouter($request)->fullUrlFor(
                $request->getUri(),
                'landingPrettyUrl',
                ['eventSlug' => $event->slug],
            )
        );
    }

    private function initSkautis(Event $event): void
    {
        // init Skautis only when it is needed, because it is not possible start with empty app ID
        $this->skautisService->initSkautis($event->skautisAppId);
    }
}
