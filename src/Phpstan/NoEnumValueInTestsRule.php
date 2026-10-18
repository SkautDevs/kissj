<?php

declare(strict_types=1);

namespace kissj\Phpstan;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<PropertyFetch>
 */
class NoEnumValueInTestsRule implements Rule
{
    public function getNodeType(): string
    {
        return PropertyFetch::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $namespace = $scope->getNamespace() ?? '';
        if (
            $node->name instanceof Identifier === false
            || $node->name->name !== 'value'
            || $node->var instanceof ClassConstFetch === false
            || ($namespace !== 'Tests' && str_starts_with($namespace, 'Tests\\') === false)
        ) {
            return [];
        }

        $enumType = $scope->getType($node->var);
        if ($enumType->isEnum()->yes() === false) {
            return [];
        }

        $testClassShortName = $scope->getClassReflection()?->getNativeReflection()->getShortName();
        foreach ($enumType->getObjectClassReflections() as $enumReflection) {
            if ($testClassShortName === $enumReflection->getNativeReflection()->getShortName() . 'Test') {
                return [];
            }
        }

        return [
            RuleErrorBuilder::message(
                'Use the literal backing value in tests instead of Enum::Case->value, so a changed enum value fails the test.',
            )->identifier('kissj.enumValueInTest')->build(),
        ];
    }
}
