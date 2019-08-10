<?php

namespace kissj;

use kissj\FlashMessages\FlashMessagesInterface;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\RouterInterface;

class AbstractController {
    /** @var RouterInterface */
    protected $router;
    /** @var FlashMessagesInterface */
    protected $flashMessages;
    /** @var Logger */
    protected $logger;

    public function __construct(ContainerInterface $c) {
        $this->router = $c->get('router');
        $this->flashMessages = $c->get('flashMessages');
        $this->logger = $c->get('logger');
    }
}
