<?php

declare(strict_types=1);

namespace kissj\Settings;

use Aws\S3\S3Client;
use Dotenv\Dotenv;
use kissj\FileHandler\FileHandler;
use kissj\FileHandler\LocalFileHandler;
use kissj\FileHandler\S3bucketFileHandler;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Logging\Sentry\SentryService;
use kissj\Mailer\MailerSettings;
use kissj\Middleware\MonologContextMiddleware;
use kissj\Middleware\SentryContextMiddleware;
use kissj\Middleware\SentryHttpContextMiddleware;
use kissj\Middleware\UserAuthenticationMiddleware;
use kissj\Orm\Mapper;
use kissj\PdfGenerator\PdfGenerator;
use kissj\PdfGenerator\mPdfGenerator;
use kissj\Skautis\SkautisFactory;
use kissj\User\UserRegeneration;
use LeanMapper\Connection;
use LeanMapper\DefaultEntityFactory;
use LeanMapper\IEntityFactory;
use LeanMapper\IMapper;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;
use Sentry\ClientBuilder;
use Sentry\Event as SentryEvent;
use Sentry\Monolog\Handler as SentryHandler;
use Sentry\SentrySdk;
use Sentry\State\Hub as SentryHub;
use Slim\Views\Twig;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\DebugExtension;

use function DI\autowire;
use function DI\create;
use function DI\get;

class Settings
{
    private const LOCALES_AVAILABLE = ['en', 'cs', 'sk'];

    public function __construct(
        string $envPath,
        string $envFilename,
    ) {
        $_ENV['APP_NAME'] = 'KISSJ'; // do not want to be changed soon (:

        $dotenv = Dotenv::createImmutable($envPath, $envFilename);
        $dotenv->load();
        $this->validateAllSettings($dotenv);
    }

    public function initSentry(): SentryHub
    {
        // init every time for capturing performance
        $sentryClient = ClientBuilder::create([
            'dsn' => $_ENV['SENTRY_DSN'],
            'environment' => $_ENV['DEBUG'] !== 'true' ? 'PROD' : 'DEBUG',
            'traces_sample_rate' => (float)$_ENV['SENTRY_PROFILING_RATE'],
            'profiles_sample_rate' => 1.0,
            'before_send' => function (SentryEvent $event): ?SentryEvent {
                // Check if error is from middleware exception capturer
                // Exceptions are captured in the middleware as exception directly with \Sentry\captureException()
                if ($event->getLogger() === "monolog.KISSJ"
                    && str_contains($event->getMessage() ?? '', 'Exception!')) {
                    return null;
                }

                return $event;
            },
        ])->getClient();
        SentrySdk::init()->bindClient($sentryClient);

        $sentryHub = new SentryHub($sentryClient);
        SentrySdk::setCurrentHub($sentryHub);

        return $sentryHub;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContainerDefinition(
        SentryHub $sentryHub,
    ): array {
        $container = [];
        $container[Connection::class] = function (): Connection {
            return new Connection([
                    'driver' => 'postgre',
                    'host' => $_ENV['DATABASE_HOST'],
                    'username' => $_ENV['POSTGRES_USER'],
                    'password' => $_ENV['POSTGRES_PASSWORD'],
                    'database' => $_ENV['POSTGRES_DB'],
                ]);
        };
        $container[FileHandler::class] = match ($_ENV['FILE_HANDLER_TYPE']) {
            'local' => new LocalFileHandler(),
            's3bucket' => get(S3bucketFileHandler::class),
            default => throw new \UnexpectedValueException('Got unknown FileHandler type parameter: '
                . $_ENV['FILE_HANDLER_TYPE']),
        };
        $container[FlashMessagesInterface::class] = autowire(FlashMessagesBySession::class);
        $container[IMapper::class] = create(Mapper::class);
        $container[IEntityFactory::class] = create(DefaultEntityFactory::class);

        $container[SentryHub::class] = $sentryHub;

        $container[Logger::class] = function (SentryHub $sentryHub): LoggerInterface {
            $logger = new Logger($_ENV['APP_NAME']);
            $logger->pushProcessor(new UidProcessor());
            $logger->pushProcessor(new GitProcessor());
            $logger->pushProcessor(new WebProcessor());
            $logger->pushHandler(
                new StreamHandler('php://stdout', $_ENV['LOGGER_LEVEL'])
            );

            $sentryHandler = new SentryHandler(
                $sentryHub,
                Logger::WARNING
            );

            // Log only warnings or higher severity events/errors to Sentry
            $logger->pushHandler($sentryHandler);

            if ($_ENV['DEBUG'] === 'true') {
                $logger->pushHandler(
                    new StreamHandler(__DIR__ . '/../../logs/debug.log', Logger::DEBUG),
                );
            }

            return $logger;
        };
        $container[LoggerInterface::class] = get(Logger::class);
        $container[MailerSettings::class] = function (): MailerSettings {
            return new MailerSettings(
                $_ENV['MAIL_DSN'],
                $_ENV['MAIL_SEND_MAIL_TO_MAIN_RECIPIENT'],
            );
        };
        $container[S3bucketFileHandler::class] = fn (
            S3Client $s3Client,
            SentryService $sentryService
        ) => new S3bucketFileHandler(
            $s3Client,
            $_ENV['S3_BUCKET'],
            $sentryService,
        );
        $container[S3Client::class] = fn () => new S3Client([
            'version' => 'latest',
            'region' => $_ENV['S3_REGION'],
            'endpoint' => $_ENV['S3_ENDPOINT'],
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => $_ENV['S3_KEY'],
                'secret' => $_ENV['S3_SECRET'],
            ],
        ]);
        $container[SkautisFactory::class] = function (): SkautisFactory {
            return new SkautisFactory(
                $_ENV['SKAUTIS_APP_ID'],
                $_ENV['SKAUTIS_USE_TEST'] !== 'false',
            );
        };
        $container[PdfGenerator::class] = get(mPdfGenerator::class);
        $container[Translator::class] = function () {
            // https://symfony.com/doc/current/components/translation.html
            $translator = new Translator($_ENV['DEFAULT_LOCALE']);
            $translator->setFallbackLocales([$_ENV['DEFAULT_LOCALE']]);

            $translator->addLoader('yaml', new YamlFileLoader());
            $translator->addResource('yaml', __DIR__ . '/../Templates/cs.yaml', 'cs');
            $translator->addResource('yaml', __DIR__ . '/../Templates/sk.yaml', 'sk');
            $translator->addResource('yaml', __DIR__ . '/../Templates/en.yaml', 'en');

            return $translator;
        };
        $container[TranslatorInterface::class] = get(Translator::class);
        $container[Twig::class] = function (
            UserRegeneration $userRegeneration,
            Translator $translator,
            FlashMessagesBySession $flashMessages
        ) {
            $view = Twig::create(
                [
                    __DIR__ . '/../Templates/translatable',
                    __DIR__ . '/../../public',
                ],
                [
                    // env. variables are parsed into strings
                    'cache' => $_ENV['TEMPLATE_CACHE'] !== 'false' ? __DIR__ . '/../../temp/twig' : false,
                    'debug' => $_ENV['DEBUG'] === 'true',
                ]
            );

            $view->getEnvironment()->addGlobal('flashMessages', $flashMessages);

            $user = $userRegeneration->getCurrentUser();
            $view->getEnvironment()->addGlobal('user', $user);
            $view->getEnvironment()->addGlobal('debug', $_ENV['DEBUG'] === "true");

            $view->addExtension(new DebugExtension()); // not needed to disable in production
            $view->addExtension(new TranslationExtension($translator));
            $view->addExtension(new TwigExtension());

            return $view;
        };

        $container[UserAuthenticationMiddleware::class]
            = fn (UserRegeneration $userRegeneration) => new UserAuthenticationMiddleware($userRegeneration);

        $container[SentryHttpContextMiddleware::class]
            = fn (SentryHub $sentryHub): SentryHttpContextMiddleware => new SentryHttpContextMiddleware($sentryHub);

        $container[SentryContextMiddleware::class]
            = fn (SentryHub $sentryHub): SentryContextMiddleware => new SentryContextMiddleware($sentryHub);

        $container[MonologContextMiddleware::class]
            = fn (Logger $logger): MonologContextMiddleware => new MonologContextMiddleware($logger);

        return $container;
    }

    private function validateAllSettings(Dotenv $dotenv): void
    {
        $dotenv->required('DEBUG')->notEmpty()->isBoolean();
        $dotenv->required('TEMPLATE_CACHE')->notEmpty()->isBoolean();
        $dotenv->required('DEFAULT_LOCALE')->notEmpty()->allowedValues(self::LOCALES_AVAILABLE);
        $dotenv->required('LOGGER_FILENAME')->notEmpty();
        $dotenv->required('LOGGER_LEVEL')->notEmpty()->allowedValues(array_flip(Logger::getLevels()));
        $dotenv->required('MAIL_DSN');
        $dotenv->required('MAIL_SEND_MAIL_TO_MAIN_RECIPIENT');
        $dotenv->required('FILE_HANDLER_TYPE')->allowedValues([
            'local',
            's3bucket',
        ]); // cannot use const - container won't compile
        $dotenv->required('S3_BUCKET');
        $dotenv->required('S3_KEY');
        $dotenv->required('S3_SECRET');
        $dotenv->required('S3_REGION');
        $dotenv->required('S3_ENDPOINT');
        $dotenv->required('DATABASE_HOST');
        $dotenv->required('POSTGRES_USER');
        $dotenv->required('POSTGRES_PASSWORD');
        $dotenv->required('POSTGRES_DB');
        $dotenv->required('SENTRY_DSN');
        $dotenv->required('SENTRY_PROFILING_RATE')->notEmpty();
        $dotenv->required('SKAUTIS_APP_ID')->notEmpty();
        $dotenv->required('SKAUTIS_USE_TEST')->isBoolean();
    }
}
