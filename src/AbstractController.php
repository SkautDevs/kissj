<?php

namespace kissj;

use DI\Annotation\Inject;
use kissj\Event\Event;
use kissj\FileHandler\FileHandler;
use kissj\FlashMessages\FlashMessagesBySession;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\UploadedFile;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractController
{
    /**
     * @Inject()
     */
    protected FlashMessagesBySession $flashMessages;

    /**
     * @Inject("Psr\Log\LoggerInterface")
     */
    protected Logger $logger;

    /**
     * @Inject()
     */
    protected Twig $view;

    /**
     * @Inject()
     */
    protected TranslatorInterface $translator;

    /**
     * @Inject()
     */
    protected FileHandler $fileHandler;

    /**
     * @param Request  $request
     * @param Response $response
     * @param string   $routeName
     * @param string[] $arguments
     * @return Response
     */
    protected function redirect(
        Request $request,
        Response $response,
        string $routeName,
        array $arguments = []
    ): Response {
        $event = $this->tryGetEvent($request);
        if ($event instanceof Event) {
            $arguments = array_merge($arguments, ['eventSlug' => $event->slug]);

            return $response
                ->withHeader('Location', $this->getRouter($request)->urlFor($routeName, $arguments))
                ->withStatus(302);
        }

        $this->flashMessages->warning($this->translator->trans('flash.warning.nonexistentEvent'));
        
        return $response
            ->withHeader('Location', $this->getRouter($request)->urlFor('eventList', $arguments))
            ->withStatus(302);
    }

    protected function getRouter(Request $request): RouteParserInterface
    {
        return RouteContext::fromRequest($request)->getRouteParser();
    }

    /**
     * @param UploadedFileInterface[] $uploadedFiles
     */
    protected function resolveUploadedFiles(array $uploadedFiles): ?UploadedFile
    {
        if (!array_key_exists('uploadFile', $uploadedFiles) || !$uploadedFiles['uploadFile'] instanceof UploadedFile) {
            // problem - too big file -> not safe anything, because always got nulls in request fields
            $this->flashMessages->warning($this->translator->trans('flash.warning.fileTooBig'));

            return null;
        }

        $errorNum = $uploadedFiles['uploadFile']->getError();

        switch ($errorNum) {
            case UPLOAD_ERR_OK:
                $uploadedFile = $uploadedFiles['uploadFile'];

                // check for too-big files
                if ($uploadedFile->getSize() > 10_000_000) { // 10MB
                    $this->flashMessages->warning($this->translator->trans('flash.warning.fileTooBig'));

                    return null;
                }

                return $uploadedFile;
            case UPLOAD_ERR_INI_SIZE:
                $this->flashMessages->warning($this->translator->trans('flash.warning.fileTooBig'));

                return null;
            default:
                $this->flashMessages->warning($this->translator->trans('flash.warning.general'));

                return null;
        }
    }

    protected function getEvent(Request $request): Event
    {
        return $request->getAttribute('event');
    }

    protected function tryGetEvent(Request $request): ?Event
    {
        return $request->getAttribute('event');
    }
}
