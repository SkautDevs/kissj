<?php

declare(strict_types=1);

namespace kissj\Settings;

use Aws\S3\S3Client;
use Dotenv\Dotenv;
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
use kissj\Event\EventService;
use kissj\Export\ExportController;
use kissj\Export\ExportService;
use kissj\FileHandler\SaveFileHandler;
use kissj\FileHandler\LocalSaveFileHandler;
use kissj\FileHandler\S3BucketSaveFileHandler;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Logging\Sentry\SentryCollector;
use kissj\Mailer\MailerSettings;
use kissj\Mailer\Mailer;
use kissj\Middleware\AdminsOnlyMiddleware;
use kissj\Middleware\CheckLeaderParticipants;
use kissj\Middleware\ChoosedRoleOnlyMiddleware;
use kissj\Middleware\EventInfoMiddleware;
use kissj\Middleware\LocalizationResolverMiddleware;
use kissj\Middleware\LoggedOnlyMiddleware;
use kissj\Middleware\MonologContextMiddleware;
use kissj\Middleware\NonChoosedRoleOnlyMiddleware;
use kissj\Middleware\NonLoggedOnlyMiddleware;
use kissj\Middleware\OpenStatusOnlyMiddleware;
use kissj\Middleware\PaidCancelledStatusOnlyMiddleware;
use kissj\Middleware\PatrolLeadersOnlyMiddleware;
use kissj\Middleware\SentryContextMiddleware;
use kissj\Middleware\SentryHttpContextMiddleware;
use kissj\Middleware\TroopLeadersOnlyMiddleware;
use kissj\Middleware\TroopParticipantsOnlyMiddleware;
use kissj\Middleware\UserAuthenticationMiddleware;
use kissj\Orm\Mapper;
use kissj\Participant\Admin\AdminController;
use kissj\Participant\Admin\AdminJsonController;
use kissj\Participant\Admin\AdminRepository;
use kissj\Participant\Admin\AdminService;
use kissj\Participant\Guest\GuestRepository;
use kissj\Participant\Guest\GuestService;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\ParticipantController;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
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
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\DebugExtension;

use function DI\autowire;
use function DI\get;

class Settings
{
    /** @var string[] */
    private const array LOCALES_AVAILABLE = ['en', 'cs', 'sk'];

    /**
     * @return array<string, mixed>
     */
    public function getContainerDefinition(string $envPath, string $envFilename): array
    {
        $_ENV['APP_NAME'] = 'KISSJ'; // do not want to be changed soon (:

        $dotenv = Dotenv::createImmutable($envPath, $envFilename);
        if (!isset($_ENV['DEBUG']) || $_ENV['DEBUG'] === 'true') {
            // expensive call, do not want to call this in production
            $dotenv->safeLoad();
        }
        $this->validateAllSettings($dotenv);

        // init every time for capturing performance
        $sentryClient = ClientBuilder::create([
            'dsn' => $_ENV['SENTRY_DSN'],
            'environment' => $_ENV['DEBUG'] !== 'true' ? 'PROD' : 'DEBUG',
            'traces_sample_rate' => (float)$_ENV['SENTRY_PROFILING_RATE'],
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

        // autowired classes are not compiled automatically, hence here we about to tell them to DI
        // https://php-di.org/doc/performances.html#optimizing-for-compilation
        $container = [
            AdminController::class => autowire(),
            AdminJsonController::class => autowire(),
            AdminRepository::class => autowire(),
            AdminService::class => autowire(),
            AdminsOnlyMiddleware::class => autowire(),
            BankPaymentRepository::class => autowire(),
            CheckLeaderParticipants::class => autowire(),
            ChoosedRoleOnlyMiddleware::class => autowire(),
            ContentArbiterGuest::class => autowire(),
            ContentArbiterIst::class => autowire(),
            ContentArbiterPatrolLeader::class => autowire(),
            ContentArbiterPatrolParticipant::class => autowire(),
            ContentArbiterTroopLeader::class => autowire(),
            ContentArbiterTroopParticipant::class => autowire(),
            EntryController::class => autowire(),
            EventController::class => autowire(),
            EventInfoMiddleware::class => autowire(),
            EventRepository::class => autowire(),
            EventService::class => autowire(),
            ExportController::class => autowire(),
            ExportService::class => autowire(),
            FioBankPaymentService::class => autowire(),
            FioBankReaderFactory::class => autowire(),
            GuestRepository::class => autowire(),
            GuestService::class => autowire(),
            IstRepository::class => autowire(),
            LanguageNegotiator::class => autowire(),
            LocalizationResolverMiddleware::class => autowire(),
            LoggedOnlyMiddleware::class => autowire(),
            MonologContextMiddleware::class => autowire(),
            NonChoosedRoleOnlyMiddleware::class => autowire(),
            NonLoggedOnlyMiddleware::class => autowire(),
            OpenStatusOnlyMiddleware::class => autowire(),
            PaidCancelledStatusOnlyMiddleware::class => autowire(),
            ParticipantController::class => autowire(),
            ParticipantRepository::class => autowire(),
            ParticipantService::class => autowire(),
            ParticipantVendorController::class => autowire(),
            PatrolController::class => autowire(),
            PatrolLeaderRepository::class => autowire(),
            PatrolLeadersOnlyMiddleware::class => autowire(),
            PatrolParticipantRepository::class => autowire(),
            PatrolService::class => autowire(),
            PaymentRepository::class => autowire(),
            PaymentService::class => autowire(),
            Mailer::class => autowire(),
            QrCodeService::class => autowire(),
            SentryContextMiddleware::class => autowire(),
            SentryHttpContextMiddleware::class => autowire(),
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
        ];

        $container[Connection::class] = fn () => new Connection([
            'driver' => 'postgre',
            'host' => $_ENV['DATABASE_HOST'],
            'username' => $_ENV['POSTGRES_USER'],
            'password' => $_ENV['POSTGRES_PASSWORD'],
            'database' => $_ENV['POSTGRES_DB'],
        ]);
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
            $logger = new Logger($_ENV['APP_NAME']);
            $logger->pushProcessor(new UidProcessor());
            $logger->pushProcessor(new GitProcessor());
            $logger->pushProcessor(new WebProcessor());
            $logger->pushHandler(
                new StreamHandler('php://stdout', $_ENV['LOGGER_LEVEL']),
            );
            $logger->pushHandler(
                new SentryHandler($sentryHub, Logger::INFO),
            );

            if ($_ENV['DEBUG'] === 'true') {
                $logger->pushHandler(
                    new StreamHandler(__DIR__ . '/../../logs/debug.log', Logger::DEBUG),
                );
            }

            return $logger;
        };
        $container[LoggerInterface::class] = get(Logger::class);
        $container[MailerSettings::class] = fn () => new MailerSettings(
            $_ENV['MAIL_DSN'],
        );
        $container[Mpdf::class] = fn () => new Mpdf([
            'tempDir' => __DIR__ . '/../../temp/mpdf',
        ]);
        $container[SessionHandlerInterface::class] = new RedisSessionHandler(
            new Redis(),
            $_ENV['REDIS_HOST'],
            (int)$_ENV['REDIS_PORT'],
            $_ENV['REDIS_PASSWORD'],
        );
        $container[S3BucketSaveFileHandler::class] = fn (
            S3Client $s3Client,
            SentryCollector $sentryCollector,
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
            $_ENV['SKAUTIS_APP_ID'],
            $_ENV['SKAUTIS_USE_TEST'] !== 'false',
        );
        $container[PdfGenerator::class] = get(mPdfGenerator::class);
        $container[Translator::class] = function () {
            $defLocale = $_ENV['DEFAULT_LOCALE'];
            // https://symfony.com/doc/current/components/translation.html
            $translator = new Translator($defLocale);
            $translator->setFallbackLocales([$defLocale]);

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
            FlashMessagesBySession $flashMessages,
        ) {
            /** @phpstan-ignore shipmonk.checkedExceptionInCallable */
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
            $view->getEnvironment()->addGlobal('user', $user);
            $view->getEnvironment()->addGlobal('debug', $_ENV['DEBUG'] === "true");

            $view->addExtension(new DebugExtension()); // not needed to disable in production
            $view->addExtension(new TranslationExtension($translator));
            $view->addExtension(new TwigExtension());

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
        $dotenv->required('DATABASE_HOST');
        $dotenv->required('POSTGRES_USER');
        $dotenv->required('POSTGRES_PASSWORD');
        $dotenv->required('POSTGRES_DB');
        $dotenv->required('SENTRY_DSN');
        $dotenv->required('SENTRY_PROFILING_RATE')->notEmpty();
        $dotenv->required('SKAUTIS_APP_ID')->notEmpty();
        $dotenv->required('SKAUTIS_USE_TEST')->isBoolean();
        $dotenv->required('GIT_HASH');
        $dotenv->required('REDIS_HOST')->notEmpty();
        $dotenv->required('REDIS_PORT')->notEmpty()->isInteger();
        $dotenv->required('REDIS_PASSWORD');
    }
}
