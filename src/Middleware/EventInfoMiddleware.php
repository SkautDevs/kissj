<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Mailer\MailerSettings;
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
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        $eventSlug = $route?->getArgument('eventSlug') ?? '';
        $event = $this->eventRepository->findBySlug($eventSlug);
        if ($event instanceof Event) {
            $request = $request->withAttribute('event', $event);
            $this->view->getEnvironment()->addGlobal('event', $event); // used in templates

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

            $this->mailerSettings->setEvent($event);
            $this->mailerSettings->setFullUrlLink(
                $this->getRouter($request)->fullUrlFor(
                    $request->getUri(),
                    'landingPrettyUrl',
                    ['eventSlug' => $event->slug],
                )
            );
        } else {
            $request = $request->withAttribute('event', null);
        }

        return $handler->handle($request);
    }
}
