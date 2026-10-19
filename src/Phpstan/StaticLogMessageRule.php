<?php

declare(strict_types=1);

namespace kissj\Phpstan;

use Monolog\Logger;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use Psr\Log\LoggerInterface;

/**
 * @implements Rule<MethodCall>
 */
class StaticLogMessageRule implements Rule
{
    private const array LEVEL_METHODS = [
        'debug',
        'info',
        'notice',
        'warning',
        'error',
        'critical',
        'alert',
        'emergency',
    ];

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->name instanceof Identifier === false) {
            return [];
        }

        $methodName = $node->name->toLowerString();
        $isAddRecord = $methodName === 'addrecord';
        if ($methodName === 'log' || $isAddRecord) {
            $messageArgumentPosition = 1;
        } elseif (in_array($methodName, self::LEVEL_METHODS, true)) {
            $messageArgumentPosition = 0;
        } else {
            return [];
        }

        $namespace = $scope->getNamespace() ?? '';
        if ($namespace === 'Tests' || str_starts_with($namespace, 'Tests\\')) {
            return [];
        }

        $callerType = $scope->getType($node->var);
        $loggerClass = $isAddRecord ? Logger::class : LoggerInterface::class;
        if ((new ObjectType($loggerClass))->isSuperTypeOf($callerType)->yes() === false) {
            return [];
        }

        $messageArgument = $this->findMessageArgument($node, $messageArgumentPosition);
        if ($messageArgument === null) {
            return [];
        }

        $messageType = $scope->getType($messageArgument->value);
        if (count($messageType->getConstantStrings()) === 1 && $messageType->isConstantScalarValue()->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message('Log message must be a static string, put variables into the context array.')
                ->identifier('kissj.staticLogMessage')
                ->build(),
        ];
    }

    private function findMessageArgument(MethodCall $node, int $position): ?Arg
    {
        foreach ($node->getArgs() as $argument) {
            if ($argument->name?->toString() === 'message') {
                return $argument;
            }
        }

        $argument = $node->getArgs()[$position] ?? null;

        return $argument?->name === null ? $argument : null;
    }
}
