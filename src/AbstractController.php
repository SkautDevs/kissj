<?php

namespace kissj;

use DI\Annotation\Inject;
use kissj\FlashMessages;
use Monolog\Logger;
use Slim\Interfaces\RouterInterface;
use Slim\Views\Twig;

abstract class AbstractController {
    /**
     * @Inject()
     * @var RouterInterface
     */
    protected $router;

    /**
     * @Inject()
     * @var FlashMessages\FlashMessagesBySession
     */
    protected $flashMessages;

    /**
     * @Inject("logger")
     * @var Logger
     */
    protected $logger;

    /**
     * @Inject("view")
     * @var Twig
     */
    protected $view;
}
