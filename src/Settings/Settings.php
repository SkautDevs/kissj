<?php

declare(strict_types=1);

namespace kissj\Settings;

use Aws\S3\S3Client;
use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;
use kissj\Application\HealthController;
use kissj\BankPayment\BankPaymentRepository;
use kissj\BankPayment\FioBankPaymentService;
use kissj\BankPayment\FioBankReaderFactory;
use kissj\BankPayment\TatraBankPaymentService;
use kissj\Entry\EntryController;
use kissj\Event\ContentArbiterGuest;
use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\Event\ContentArbiterTroopLeader;
use kissj\Event\ContentArbiterTroopParticipant;
use kissj\Event\EventController;
use kissj\Event\EventRepository;
use kissj\Event\EventScope;
use kissj\Event\EventService;
use kissj\Export\ExportController;
use kissj\Export\ExportService;
use kissj\FileHandler\SaveFileHandler;
use kissj\FileHandler\LocalSaveFileHandler;
use kissj\FileHandler\S3BucketSaveFileHandler;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Telemetry\Sentry\Collector;
use kissj\Mailer\MailerSettings;
use kissj\Mailer\Mailer;
use kissj\Middleware\AdminsOnlyMiddleware;
use kissj\Middleware\CheckLeaderParticipants;
use kissj\Middleware\ChoosedRoleOnlyMiddleware;
use kissj\Middleware\DealApiKeyMiddleware;
use kissj\Middleware\EntryApiKeyMiddleware;
use kissj\Middleware\EventInfoMiddleware;
use kissj\Middleware\LocalizationResolverMiddleware;
use kissj\Middleware\LockedStatusOnlyMiddleware;
use kissj\Middleware\LoggedOnlyMiddleware;
use kissj\Middleware\MonologContextMiddleware;
use kissj\Middleware\NonChoosedRoleOnlyMiddleware;
use kissj\Middleware\NonLoggedOnlyMiddleware;
use kissj\Middleware\OpenStatusOnlyMiddleware;
use kissj\Middleware\OwnerTicketTransferAllowedOnlyMiddleware;
use kissj\Middleware\PaidCancelledStatusOnlyMiddleware;
use kissj\Middleware\PaidStatusOnlyMiddleware;
use kissj\Middleware\PatrolLeadersOnlyMiddleware;
use kissj\Telemetry\Sentry\ContextMiddleware;
use kissj\Telemetry\Sentry\HttpContextMiddleware;
use kissj\Telemetry\Sentry\TransactionMiddleware;
use kissj\Middleware\TroopLeadersOnlyMiddleware;
use kissj\Middleware\TroopParticipantsOnlyMiddleware;
use kissj\Middleware\UserAuthenticationMiddleware;
use kissj\Middleware\VendorApiKeyMiddleware;
use kissj\Orm\Mapper;
use kissj\Participant\Admin\AdminController;
use kissj\Participant\Admin\AdminJsonController;
use kissj\Participant\Admin\AdminRepository;
use kissj\Participant\Admin\PaymentTransferService;
use kissj\Participant\Guest\GuestRepository;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\ParticipantController;
use kissj\Participant\ParticipantFileService;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\ParticipantStatisticsService;
use kissj\Participant\Patrol\PatrolController;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\Participant\Patrol\PatrolService;
use kissj\Participant\Troop\TroopController;
use kissj\Participant\Troop\TroopLeaderRepository;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\Participant\Troop\TroopService;
use kissj\ParticipantVendor\ParticipantVendorController;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use kissj\Payment\QrCodeService;
use kissj\PdfGenerator\mPdfGenerator;
use kissj\PdfGenerator\PdfGenerator;
use kissj\Session\RedisSessionHandler;
use kissj\Skautis\SkautisController;
use kissj\Skautis\SkautisFactory;
use kissj\Skautis\SkautisService;
use kissj\Telemetry\Sentry\DibiSpanListener;
use kissj\Telemetry\Metrics;
use kissj\Translation\CurrentTranslator;
use kissj\Translation\TranslatorFactory;
use kissj\User\UserRegeneration;
use LeanMapper\Connection;
use LeanMapper\DefaultEntityFactory;
use LeanMapper\IEntityFactory;
use LeanMapper\IMapper;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Negotiation\LanguageNegotiator;
use Psr\Log\LoggerInterface;
use Redis;
use Sentry\Client as SentryClient;
use Sentry\ClientBuilder;
use Sentry\Event as SentryEvent;
use Sentry\Monolog\Handler as SentryHandler;
use Sentry\SentrySdk;
use Sentry\State\Hub as SentryHub;
use SessionHandlerInterface;
use Slim\Views\Twig;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\DebugExtension;
use Twig\TwigFilter;

use function DI\autowire;
use function DI\get;

class Settings
{
    /** @var string[] */
    private const array LOCALES_AVAILABLE = ['en', 'cs', 'sk'];

    /**
     * @return array<string, mixed>
     */
    public function getContainerDefinition(string $envPath, string $envFilename, string $tempPath): array
    {
        $_ENV['APP_NAME'] = 'KISSJ'; // do not want to be changed soon (:

        $dotenv = Dotenv::createImmutable($envPath, $envFilename);
        if (!isset($_ENV['DEBUG']) || $_ENV['DEBUG'] === 'true') {
            // expensive call, do not want to call this in production
            $dotenv->safeLoad();
        }
        $this->validateAllSettings($dotenv);

        if (($_ENV['DB_TYPE'] ?? 'postgresql') === 'sqlite' && $envFilename !== 'env.testing') {
            throw new ValidationException('DB_TYPE=sqlite is supported only for the test suite (env.testing).');
        }

        // computed here (not in the Connection closure) because compiled DI cannot capture closure vars;
        // default only - must match the phinx target in tests/phinxConfiguration.php
        $_ENV['DATABASE_PATH'] ??= $tempPath . '/db_tests.sqlite';

        // init every time for capturing performance
        /** @var array<string, string> $_ENV */
        $sentryClient = ClientBuilder::create([
            'dsn' => $_ENV['SENTRY_DSN'],
            'environment' => $_ENV['DEBUG'] !== 'true' ? 'PROD' : 'DEBUG',
            'traces_sample_rate' => (float)($_ENV['SENTRY_TRACES_SAMPLE_RATE'] ?? '1'),
            'profiles_sample_rate' => (float)($_ENV['SENTRY_PROFILES_SAMPLE_RATE'] ?? '1'),
            'release' => 'kissj@' . $_ENV['GIT_HASH'],
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
        register_shutdown_function(static function (): void {
            (new Metrics())->flush();
        });

        // autowired classes are not compiled automatically, hence here we about to tell them to DI
        // https://php-di.org/doc/performances.html#optimizing-for-compilation
        $container = [
            AdminController::class => autowire(),
            AdminJsonController::class => autowire(),
            AdminRepository::class => autowire(),
            AdminsOnlyMiddleware::class => autowire(),
            BankPaymentRepository::class => autowire(),
            CheckLeaderParticipants::class => autowire(),
            ChoosedRoleOnlyMiddleware::class => autowire(),
            DealApiKeyMiddleware::class => autowire(),
            ContentArbiterGuest::class => autowire(),
            ContentArbiterIst::class => autowire(),
            ContentArbiterPatrolLeader::class => autowire(),
            ContentArbiterPatrolParticipant::class => autowire(),
            ContentArbiterTroopLeader::class => autowire(),
            ContentArbiterTroopParticipant::class => autowire(),
            EntryApiKeyMiddleware::class => autowire(),
            EntryController::class => autowire(),
            EventController::class => autowire(),
            EventInfoMiddleware::class => autowire(),
            EventRepository::class => autowire(),
            EventScope::class => autowire(),
            EventService::class => autowire(),
            ExportController::class => autowire(),
            ExportService::class => autowire(),
            FioBankPaymentService::class => autowire(),
            FioBankReaderFactory::class => autowire(),
            GuestRepository::class => autowire(),
            HealthController::class => autowire(),
            IstRepository::class => autowire(),
            LanguageNegotiator::class => autowire(),
            LocalizationResolverMiddleware::class => autowire(),
            LockedStatusOnlyMiddleware::class => autowire(),
            LoggedOnlyMiddleware::class => autowire(),
            MonologContextMiddleware::class => autowire(),
            NonChoosedRoleOnlyMiddleware::class => autowire(),
            NonLoggedOnlyMiddleware::class => autowire(),
            OpenStatusOnlyMiddleware::class => autowire(),
            OwnerTicketTransferAllowedOnlyMiddleware::class => autowire(),
            PaidCancelledStatusOnlyMiddleware::class => autowire(),
            PaidStatusOnlyMiddleware::class => autowire(),
            ParticipantController::class => autowire(),
            ParticipantFileService::class => autowire(),
            ParticipantRepository::class => autowire(),
            ParticipantService::class => autowire(),
            ParticipantStatisticsService::class => autowire(),
            ParticipantVendorController::class => autowire(),
            PatrolController::class => autowire(),
            PatrolLeaderRepository::class => autowire(),
            PatrolLeadersOnlyMiddleware::class => autowire(),
            PatrolParticipantRepository::class => autowire(),
            PatrolService::class => autowire(),
            PaymentRepository::class => autowire(),
            PaymentService::class => autowire(),
            PaymentTransferService::class => autowire(),
            Mailer::class => autowire(),
            Metrics::class => autowire(),
            QrCodeService::class => autowire(),
            ContextMiddleware::class => autowire(),
            HttpContextMiddleware::class => autowire(),
            TransactionMiddleware::class => autowire(),
            SkautisController::class => autowire(),
            SkautisService::class => autowire(),
            TatraBankPaymentService::class => autowire(),
            TroopController::class => autowire(),
            TroopLeaderRepository::class => autowire(),
            TroopLeadersOnlyMiddleware::class => autowire(),
            TroopParticipantRepository::class => autowire(),
            TroopParticipantsOnlyMiddleware::class => autowire(),
            TroopService::class => autowire(),
            UserAuthenticationMiddleware::class => autowire(),
            VendorApiKeyMiddleware::class => autowire(),
        ];

        $container[Connection::class] = function () {
            $connection = match ($_ENV['DB_TYPE'] ?? 'postgresql') {
                'sqlite' => new Connection([
                    'driver' => 'sqlite',
                    'database' => $_ENV['DATABASE_PATH'],
                    'formatDateTime' => 'Y-m-d H:i:s', // match the ISO format phinx writes so datetimes round-trip
                    'formatDate' => 'Y-m-d',
                    'onConnect' => ['PRAGMA foreign_keys = ON'],// parity with postgres, which always enforces FKs
                ]),
                default => new Connection([
                    'driver' => 'postgre',
                    'host' => $_ENV['DATABASE_HOST'],
                    'username' => $_ENV['POSTGRES_USER'],
                    'password' => $_ENV['POSTGRES_PASSWORD'],
                    'database' => $_ENV['POSTGRES_DB'],
                ]),
            };
            $connection->onEvent[] = new DibiSpanListener();

            return $connection;
        };
        $container[SaveFileHandler::class] = match ($_ENV['FILE_HANDLER_TYPE']) {
            'local' => new LocalSaveFileHandler(),
            's3bucket' => get(S3BucketSaveFileHandler::class),
            default => throw new \UnexpectedValueException('Got unknown FileHandler type parameter: '
                . $_ENV['FILE_HANDLER_TYPE']),
        };
        $container[FlashMessagesInterface::class] = autowire(FlashMessagesBySession::class);
        $container[IMapper::class] = autowire(Mapper::class);
        $container[IEntityFactory::class] = autowire(DefaultEntityFactory::class);
        $container[SentryClient::class] = $sentryClient;
        $container[SentryHub::class] = $sentryHub;

        $container[Logger::class] = function (SentryHub $sentryHub): LoggerInterface {
            /** @var array<string, string> $_ENV */
            $logger = new Logger($_ENV['APP_NAME']);
            $logger->pushProcessor(new UidProcessor());
            $logger->pushProcessor(new GitProcessor());
            $logger->pushProcessor(new WebProcessor());
            $loggerLevel = Level::fromName($_ENV['LOGGER_LEVEL']);
            $logger->pushHandler(
                new StreamHandler('php://stdout', $loggerLevel),
            );
            $logger->pushHandler(
                new SentryHandler($sentryHub, Level::Info),
            );

            if ($_ENV['DEBUG'] === 'true') {
                $logger->pushHandler(
                    new StreamHandler(__DIR__ . '/../../logs/debug.log', Level::Debug),
                );
            }

            return $logger;
        };
        $container[LoggerInterface::class] = get(Logger::class);
        $container[MailerSettings::class] = fn () => new MailerSettings(
            $_ENV['MAIL_DSN'],
        );
        $container[Mpdf::class] = function () {
            /** @var array{fontDir: list<string>} $configDefaults */
            $configDefaults = (new ConfigVariables())->getDefaults();
            /** @var array{fontdata: array<string, array<string, string>>} $fontDefaults */
            $fontDefaults = (new FontVariables())->getDefaults();

            return new Mpdf([
                'tempDir' => __DIR__ . '/../../temp/mpdf',
                // badges can use only included ttf fonts
                'fontDir' => array_merge($configDefaults['fontDir'], [__DIR__ . '/../../public/fonts']),
                'fontdata' => $fontDefaults['fontdata'] + [
                    'themix' => ['R' => 'TheMixLT.ttf', 'B' => 'TheMixLT-Bold.ttf'],
                    'skautbold' => ['R' => 'SkautBold.ttf', 'B' => 'SkautBold.ttf'],
                ],
            ]);
        };
        $container[SessionHandlerInterface::class] = new RedisSessionHandler(
            new Redis(),
            $_ENV['REDIS_HOST'],
            (int)$_ENV['REDIS_PORT'],
            $_ENV['REDIS_PASSWORD'],
        );
        $container[S3BucketSaveFileHandler::class] = fn (
            S3Client $s3Client,
            Collector $sentryCollector,
        ) => new S3BucketSaveFileHandler(
            $s3Client,
            $_ENV['S3_BUCKET'],
            $sentryCollector,
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
        $container[SkautisFactory::class] = fn () => new SkautisFactory(
            $_ENV['SKAUTIS_USE_TEST'] !== 'false',
        );
        $container[PdfGenerator::class] = get(mPdfGenerator::class);
        $container[TranslatorFactory::class] = function () {
            /** @var array<string, string> $_ENV */
            return new TranslatorFactory(
                $_ENV['DEFAULT_LOCALE'],
                $_ENV['TEMPLATE_CACHE'] !== 'false' ? __DIR__ . '/../../temp/translations' : null,
                $_ENV['DEBUG'] === 'true',
            );
        };
        $container[CurrentTranslator::class] = autowire(CurrentTranslator::class);
        $container[TranslatorInterface::class] = get(CurrentTranslator::class);
        $container[Twig::class] = function (
            UserRegeneration $userRegeneration,
            CurrentTranslator $translator,
            FlashMessagesBySession $flashMessages,
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
                ],
            );

            $view->getEnvironment()->addGlobal('flashMessages', $flashMessages);

            $user = $userRegeneration->getCurrentUser();
            // Twig is a DI singleton; these globals are per-request state mutated on a
            // shared instance. Safe under PHP-FPM (fresh container per request), unsafe
            // under long-lived workers (FrankenPHP/RoadRunner): user/event/flashMessages/
            // locale will leak across requests. Worker-readiness fix: wrap each global in
            // a request-scoped resolver (proxy pattern, like CurrentTranslator) or move
            // registration into middleware that runs per request and resets on the way out.
            $view->getEnvironment()->addGlobal('user', $user);
            $view->getEnvironment()->addGlobal('debug', $_ENV['DEBUG'] === "true");

            $view->addExtension(new DebugExtension()); // not needed to disable in production
            $view->addExtension(new TranslationExtension($translator));
            $view->addExtension(new TwigExtension());

            $view->getEnvironment()->addFilter(new TwigFilter(
                'transGendered',
                static function (string $key, string $suffix, array $params = []) use ($translator): string {
                    if ($suffix === '') {
                        return $translator->trans($key, $params);
                    }

                    // first try to translate genderized trans
                    $gendered = $key . $suffix;
                    $result = $translator->trans($gendered, $params);

                    if ($result !== $gendered) {
                        return $result;
                    }

                    // when result is the same, genderized translation is not found and trans key is returned
                    return $translator->trans($key, $params);
                },
            ));

            return $view;
        };

        return $container;
    }

    private function validateAllSettings(Dotenv $dotenv): void
    {
        $dotenv->required('DEBUG')->notEmpty()->isBoolean();
        $dotenv->required('TEMPLATE_CACHE')->notEmpty()->isBoolean();
        $dotenv->required('DEFAULT_LOCALE')->notEmpty()->allowedValues(self::LOCALES_AVAILABLE);
        $dotenv->required('LOGGER_FILENAME')->notEmpty();
        $dotenv->required('LOGGER_LEVEL')->notEmpty()->allowedValues(Level::NAMES);
        $dotenv->required('MAIL_DSN');
        $dotenv->required('FILE_HANDLER_TYPE')->allowedValues([
            'local',
            's3bucket',
        ]); // cannot use const - container won't compile
        $dotenv->required('S3_BUCKET');
        $dotenv->required('S3_KEY');
        $dotenv->required('S3_SECRET');
        $dotenv->required('S3_REGION');
        $dotenv->required('S3_ENDPOINT');
        $dotenv->ifPresent('DB_TYPE')->allowedValues(['postgresql', 'sqlite']);
        $dotenv->required('DATABASE_HOST');
        $dotenv->required('POSTGRES_USER');
        $dotenv->required('POSTGRES_PASSWORD');
        $dotenv->required('POSTGRES_DB');
        $dotenv->required('SENTRY_DSN');
        $dotenv->required('SKAUTIS_USE_TEST')->isBoolean();
        $dotenv->required('GIT_HASH');
        $dotenv->required('REDIS_HOST')->notEmpty();
        $dotenv->required('REDIS_PORT')->notEmpty()->isInteger();
        $dotenv->required('REDIS_PASSWORD');
    }
}
