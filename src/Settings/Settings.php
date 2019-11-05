<?php

namespace kissj\Settings;

use DI\Bridge\Slim\CallableResolver;
use DI\Bridge\Slim\ControllerInvoker;
use DI\Container;
use h4kuna\Fio\FioRead;
use h4kuna\Fio\Utils\FioFactory;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\ResolverChain;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Orm\Mapper;
use kissj\User\UserRegeneration;
use LeanMapper\Connection;
use LeanMapper\DefaultEntityFactory;
use LeanMapper\IEntityFactory;
use LeanMapper\IMapper;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Slim\Handlers;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Interfaces\RouterInterface;
use Slim\Router;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use function DI\autowire;
use function DI\create;
use function DI\get;

class Settings {
    public function getSettingsAndDependencies(): array {
        $settings = [
            'settings' => [
                // TODO tidy up
                'httpVersion' => '1.1',
                'responseChunkSize' => 4096,
                'outputBuffering' => 'append',
                'determineRouteBeforeAppMiddleware' => false,
                'routerCacheFile' => false,

                'debug' => true, // true fires Whoops debugger, false falls into Slim debugger (keep true at all times)

                // Whoops debug part
                'whoopsDebug' => false, // true enable Whoops nice debug page, false fires up production error handle

                // Slim debug part
                'displayErrorDetails' => false, // set to false in production
                'addContentLengthHeader' => false, // Allow the web server to send the content-length header

                // Testing site
                'useTestingSite' => false,

                // Renderer settings
                'renderer' => [
                    'templates_path' => __DIR__.'/../Templates/en',
                    'enable_cache' => true,
                    'cache_path' => __DIR__.'/../../temp/twig',
                ],

                // Monolog settings
                'logger' => [
                    'name' => 'slim-app',
                    'path' => __DIR__.'/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],

                // PHPmailer settings - MailHog
                'mailer' => [
                    'smtp' => true,
                    'smtp_server' => 'mailhog',
                    'smtp_auth' => true,    // SMTP authentication
                    'smtp_port' => 1025,
                    'smtp_username' => '',
                    'smtp_password' => '',
                    'smtp_secure' => null, // ssl for Gmail, tls or nullalso possible

                    'from_mail' => 'registration@localhost', // registration mail
                    'from_name' => 'Registrace Localhost',

                    'bcc_mail' => '', // another mail
                    'bcc_name' => '',

                    // debugging settings
                    'disable_tls' => false, // turn off all certificate check
                    'debugOutoutLevel' => 0, // print debug level (0 min to 4 max)
                    'sendMailToMainRecipient' => true, // set false in dev
                ],

                'db' => [
                    'path' => __DIR__.'/../db.sqlite',
                ],

                'adminer' => [
                    // change password & add this into your settings_custom please
                    // 'login' => 'superSecretUsername',
                    // 'password' => 'superSecretPassword',
                ],
            ],
        ];

        if (file_exists(__DIR__.'/settings_custom.php')) {
            $settings = array_replace_recursive($settings, require 'settings_custom.php');
        }

        $settings = array_merge($settings, $this->getDependencies($settings['settings']));

        return $settings;
    }

    private function getDependencies(array $settings): array {
        $container = [
            // Default Slim services
            'router' => create(Router::class)
                ->method('setContainer', get(Container::class))
                ->method('setCacheFile', $settings['routerCacheFile']),
            Router::class => get('router'),
            RouterInterface::class => get('router'),
            'errorHandler' => create(Handlers\Error::class)
                ->constructor(get('settings.displayErrorDetails')),
            'phpErrorHandler' => create(Handlers\PhpError::class)
                ->constructor(get('settings.displayErrorDetails')),
            'notFoundHandler' => create(Handlers\NotFound::class),
            'notAllowedHandler' => create(Handlers\NotAllowed::class),
            'environment' => function () {
                return new Environment($_SERVER);
            },
            'flashMessages' => autowire(FlashMessagesBySession::class),
            'request' => function (ContainerInterface $c) {
                return Request::createFromEnvironment($c->get('environment'));
            },
            Request::class => get('request'),
            'response' => function (ContainerInterface $c) {
                $headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
                $response = new Response(200, $headers);

                return $response->withProtocolVersion($c->get('settings')['httpVersion']);
            },
            Response::class => get('response'),
            'foundHandler' => create(ControllerInvoker::class)
                ->constructor(get('foundHandler.invoker')),
            'foundHandler.invoker' => function (ContainerInterface $c) {
                $resolvers = [
                    // Inject parameters by name first
                    new AssociativeArrayResolver,
                    // Then inject services by type-hints for those that weren't resolved
                    new TypeHintContainerResolver($c),
                    // Then fall back on parameters default values for optional route parameters
                    new DefaultValueResolver(),
                ];

                return new Invoker(new ResolverChain($resolvers), $c);
            },

            'callableResolver' => autowire(CallableResolver::class),
        ];

        $container['logger'] = function () use ($settings) {
            $logger = new Logger($settings['logger']['name']);
            $logger->pushProcessor(new UidProcessor());
            $logger->pushHandler(new StreamHandler($settings['logger']['path'], $settings['logger']['level']));

            return $logger;
        };
        $container[Logger::class] = get('logger');

        $container[Connection::class] = function () use ($settings): Connection {
            $connection = new Connection([
                'driver' => 'sqlite3',
                'database' => $settings['db']['path'],
            ]);

            return $connection;
        };

        $container[IMapper::class] = create(Mapper::class);
        $container[IEntityFactory::class] = create(DefaultEntityFactory::class);
        $container[PhpMailerWrapper::class] = function (Twig $renderer) use ($settings) {
            return new PhpMailerWrapper($renderer, $settings['mailer']);
        };

        $container['paymentAutoMatcherFio'] = function () use ($settings) {
            // using h4kuna/fio - https://github.com/h4kuna/fio
            $paymentSettings = $settings['paymentSettings'];
            $fioFactory = new FioFactory([
                'mainAccount' => [
                    'account' => $paymentSettings['accountNumber'],
                    'token' => $paymentSettings['fioApiToken'],
                ],
            ]);

            return $fioFactory->createFioRead('mainAccount');
        };

        $container[FioRead::class] = 'need to implement';

        $container[Twig::class] = function (
            UserRegeneration $userRegeneration,
            FlashMessagesBySession $flashMessages,
            Request $request,
            Router $router
        ) use ($settings) {
            $rendererSettings = $settings['renderer'];

            $view = new Twig($rendererSettings['templates_path'], [
                'cache' => $rendererSettings['enable_cache'] ? $rendererSettings['cache_path'] : false,
            ]);

            $uri = $request->getUri();
            $basePath = rtrim(str_ireplace('index.php', '',
                $uri->getScheme().'://'.$uri->getHost().':'.$uri->getPort().$uri->getBasePath()), '/');

            // Add few elements for rendering
            $portString = '';
            $port = $uri->getPort();
            if ($port !== null) {
                $portString .= ':'.$port;
            }
            $baseHostScheme = $uri->getScheme().'://'.$uri->getHost().$portString;
            $view->addExtension(new TwigExtension($router, $basePath));
            $view->getEnvironment()->addGlobal('baseHostScheme', $baseHostScheme);
            $view->getEnvironment()->addGlobal('flashMessages', $flashMessages);
            /** @var \kissj\User\User $user */
            $user = $userRegeneration->getCurrentUser();
            $view->getEnvironment()->addGlobal('user', $user);
            if ($user !== null) {
                $view->getEnvironment()->addGlobal('event', $user->event);
            }
            // TODO move into middleware
            if ($settings['useTestingSite']) {
                $flashMessages->info('Test version - please do not imput any real personal details!');
                $flashMessages->info('Administration login: admin, password: admin, link: '
                    .$router->pathFor('administration'));
            }

            return $view;
        };
        $container['view'] = get(Twig::class);

        return $container;
    }
}
