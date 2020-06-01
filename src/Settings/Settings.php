<?php

namespace kissj\Settings;

use DI\Bridge\Slim\CallableResolver;
use h4kuna\Fio\FioRead;
use h4kuna\Fio\Utils\FioFactory;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\ResolverChain;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Middleware\LocalizationResolverMiddleware;
use kissj\Middleware\UserAuthenticationMiddleware;
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
use Psr\Log\LoggerInterface;
use Slim\Psr7\Environment;
use Slim\Views\Twig;
use Symfony\Component\Translation\Translator;
use function DI\autowire;
use function DI\create;
use function DI\get;

class Settings {
    public function getSettingsAndDependencies(): array {
        $settings = $this->getSettings();
        $settings = array_merge($settings, $this->getDependencies($settings['settings']));

        return $settings;
    }

    protected function getSettings(): array {
        $settings = [
            'settings' => [
                // TODO tidy up
                'httpVersion' => '1.1',
                'responseChunkSize' => 4096,
                'routerCacheFile' => false,

                'debug' => true, // true fires Whoops debugger, false falls into Slim debugger (keep true at all times)

                // Whoops debug part
                'whoopsDebug' => false, // true enable Whoops nice debug page, false fires up production error handle

                // Testing site
                'useTestingSite' => false,

                // Renderer settings
                'renderer' => [
                    'templates_path' => __DIR__.'/../Templates/en',
                    'enable_cache' => true,
                    'cache_path' => __DIR__.'/../../temp/twig',
                    'debug_output' => false,
                ],

                // Monolog settings
                'logger' => [
                    'name' => 'slim-app',
                    'path' => __DIR__.'/../../logs/app.log',
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
                'locales' => [
                    'availableLocales' => ['cs', 'en'],
                    'defaultLocale' => 'cs',
                ],
            ],
        ];

        if (file_exists(__DIR__.'/settings_custom.php')) {
            $settings = array_replace_recursive($settings, require 'settings_custom.php');
        }
        return $settings;
    }

    private function getDependencies(array $settings): array {
        // TODO celanup
        $container = [
            'environment' => create(Environment::class),
            'foundHandler.invoker' => function (ContainerInterface $c) {
                $resolvers = [
                    // Inject parameters by name first
                    new AssociativeArrayResolver(),
                    // Then inject services by type-hints for those that weren't resolved
                    new TypeHintContainerResolver($c),
                    // Then fall back on parameters default values for optional route parameters
                    new DefaultValueResolver(),
                ];

                return new Invoker(new ResolverChain($resolvers), $c);
            },

            'callableResolver' => autowire(CallableResolver::class),
        ];

        $container[FlashMessagesInterface::class] = autowire(FlashMessagesBySession::class);

        $container[Logger::class] = function () use ($settings) {
            $logger = new Logger($settings['logger']['name']);
            $logger->pushProcessor(new UidProcessor());
            $logger->pushHandler(new StreamHandler($settings['logger']['path'], $settings['logger']['level']));

            return $logger;
        };

        $container['logger'] = get(Logger::class); // TODO remove
        $container[LoggerInterface::class] = get(Logger::class);

        $container[Connection::class] = function () use ($settings): Connection {
            return new Connection([
                'driver' => 'sqlite3',
                'database' => $settings['db']['path'],
            ]);
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

        $container[FioRead::class] = 'need to implement'; // TODO

        $container[UserAuthenticationMiddleware::class] = function (UserRegeneration $userRegeneration) {
            return new UserAuthenticationMiddleware($userRegeneration);
        };

        $container[LocalizationResolverMiddleware::class] = autowire()->constructor(
            get(Twig::class),
            $settings['locales']['availableLocales'],
            $settings['locales']['defaultLocale']
        );

        $container[Twig::class] = function (
            UserRegeneration $userRegeneration,
            FlashMessagesBySession $flashMessages
        ) use ($settings) {
            $rendererSettings = $settings['renderer'];

            $view = Twig::create(
                $rendererSettings['templates_path'],
                [
                    'cache' => $rendererSettings['enable_cache'] ? $rendererSettings['cache_path'] : false,
                    'debug' => $rendererSettings['debug_output'],
                ]
            );

            $view->getEnvironment()->addGlobal('flashMessages', $flashMessages);
            $user = $userRegeneration->getCurrentUser();
            $view->getEnvironment()->addGlobal('user', $user);
            if ($user !== null) {
                $view->getEnvironment()->addGlobal('event', $user->event);
            }
            /*
            // TODO move into middleware
            if ($settings['useTestingSite']) {
                $flashMessages->info('Test version - please do not imput any real personal details!');
                $flashMessages->info('Administration login: admin, password: admin, link: '
                    .$router->getRouteParser()->urlFor('administration'));
            }*/

            // translations
            // https://symfony.com/doc/current/components/translation.html
            $locale = 'cs'; // TODO connect
            $translator = new Translator($locale);
            $translator->setFallbackLocales(['cs']);

            $yamlLoader = new \Symfony\Component\Translation\Loader\YamlFileLoader();
            $translator->addLoader('yaml', $yamlLoader);
            $translator->addResource('yaml', __DIR__.'/../Templates/cs.yaml', 'cs');
            $translator->addResource('yaml', __DIR__.'/../Templates/en.yaml', 'en');

            $view->addExtension(new \Symfony\Bridge\Twig\Extension\TranslationExtension($translator));

            return $view;
        };
        $container['view'] = get(Twig::class); // TODO remove

        return $container;
    }
}
