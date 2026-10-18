<?php

declare(strict_types=1);

namespace kissj\Phpstan;

use DateTimeInterface;
use kissj\Application\DateTimeUtils;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<Expr>
 */
readonly class NoConstructorUseDatetimeRule implements Rule
{
    private const array CLOCK_FUNCTIONS = [
        'time',
        'date',
        'gmdate',
        'strtotime',
        'mktime',
        'date_create',
        'date_create_immutable',
        'getdate',
        'localtime',
        'idate',
        'gmmktime',
        'strftime',
    ];

    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return Expr::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($scope->getClassReflection()?->getName() === DateTimeUtils::class) {
            return [];
        }

        if ($node instanceof New_) {
            return $this->checkConstructor($node, $scope);
        }

        if ($node instanceof FuncCall) {
            return $this->checkClockFunction($node->name, $scope);
        }

        return [];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function checkConstructor(New_ $node, Scope $scope): array
    {
        if ($node->class instanceof Name === false) {
            return [];
        }

        if ((new ObjectType(DateTimeInterface::class))->isSuperTypeOf($scope->getType($node))->yes() === false) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Do not use constructor of DateTimeInterface() directly, use DateTimeUtils::getDateTime() instead.',
            )->identifier('new.DateTime')->build(),
        ];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function checkClockFunction(Expr|Name $name, Scope $scope): array
    {
        if ($name instanceof Name === false) {
            return [];
        }

        $namespace = $scope->getNamespace() ?? '';
        if ($namespace === 'Tests' || str_starts_with($namespace, 'Tests\\')) {
            return [];
        }

        if ($this->reflectionProvider->hasFunction($name, $scope) === false) {
            return [];
        }

        $functionName = $this->reflectionProvider->getFunction($name, $scope)->getName();
        if (in_array(strtolower($functionName), self::CLOCK_FUNCTIONS, true) === false) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Do not use %s() for dates and times, use DateTimeUtils::getDateTime() instead.',
                $functionName,
            ))->identifier('kissj.clockFunction')->build(),
        ];
    }
}
