<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Mailer\MailerSettings;
use kissj\Skautis\SkautisFactory;
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
        private readonly SkautisFactory $skautisFactory,
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
            $this->enrichTemplatesWithEvent($event);
            $this->addEventSpecificTranslations($event);
            $this->enrichMailerSettingsWithEvent($event, $request);
            $this->addAppIdToSkautisFactory($event);
        }

        return $handler->handle($request);
    }

    public function enrichTemplatesWithEvent(Event $event): void
    {
        $this->view->getEnvironment()->addGlobal('event', $event);
    }

    public function addEventSpecificTranslations(Event $event): void
    {
        $translationFilePaths = $event->getEventType()->getTranslationFilePaths();
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

    private function addAppIdToSkautisFactory(Event $event): void
    {
        $this->skautisFactory->setAppId($event->skautisAppId);
    }
}
