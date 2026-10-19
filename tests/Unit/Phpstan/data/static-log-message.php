<?php

declare(strict_types=1);

namespace Fixture\Phpstan {

    use kissj\FlashMessages\FlashMessagesInterface;
    use Monolog\Level;
    use Monolog\Logger;
    use Psr\Log\LoggerInterface;
    use Psr\Log\LogLevel;

    function logMessage(): string
    {
        return 'Message';
    }

    function logEverything(
        LoggerInterface $logger,
        Logger $monolog,
        FlashMessagesInterface $flash,
        string $x,
        bool $flag,
    ): void {
        $logger->info(sprintf('Value %s', $x));
        $logger->warning("Value $x");
        $logger->error('Value ' . $x);
        $logger->debug($x);
        $logger->notice(logMessage());
        $logger->critical($flag ? 'First' : 'Second');
        $monolog->alert('Value ' . $x);
        $monolog->emergency("Value $x");
        $logger->log(LogLevel::INFO, $x);

        $logger->info('Static message', ['x' => $x]);
        $logger->info(LogLevel::INFO);
        $logger->info('a' . 'b');
        $monolog->error('Static message');
        $logger->log(LogLevel::INFO, 'static');
        $logger->log($x, 'static');
        $flash->error($x);
        $logger->info(context: ['x' => $x], message: 'Static');
        $logger->log(message: 'Bad ' . $x, level: LogLevel::INFO);
        $monolog->addRecord(Level::Info, 'Bad ' . $x);
        $monolog->addRecord(Level::Info, 'Static');
        $logger?->info('a' . $x);
    }

    class PropertyLogger
    {
        public function __construct(private readonly LoggerInterface $logger)
        {
        }

        public function run(string $x): void
        {
            $this->logger->info('a' . $x);
        }
    }
}

namespace Tests\Fixture {

    use Psr\Log\LoggerInterface;

    function logInTest(LoggerInterface $logger, string $x): void
    {
        $logger->info("Value $x");
    }
}
