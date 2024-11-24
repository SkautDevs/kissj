<?php

declare(strict_types=1);

namespace kissj\Phpstan;

use kissj\Application\DateTimeUtils;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<New_>
 */
class NoConstructorUseDatetimeRule implements Rule
{
    public function getNodeType(): string
    {
        return New_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->class instanceof Name) {
            return [];
        }

        if (
            (new ObjectType(\DateTimeInterface::class))->isSuperTypeOf($scope->getType($node))->yes()
            && $scope->getClassReflection()?->getName() !== DateTimeUtils::class
        ) {
            return [
                RuleErrorBuilder::message(
                    'Do not use constructor of DateTimeInterface() directly, use DateTimeUtils::getDateTime() instead.',
                )->identifier('new.DateTime')->build(),
            ];
        }

        return [];
    }
}
