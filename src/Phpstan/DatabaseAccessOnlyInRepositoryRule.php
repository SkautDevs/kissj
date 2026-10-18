<?php

declare(strict_types=1);

namespace kissj\Phpstan;

use kissj\Orm\Repository;
use kissj\Settings\Settings;
use LeanMapper\Connection;
use LeanMapper\Fluent;
use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\Type;

/**
 * @implements Rule<CallLike>
 */
class DatabaseAccessOnlyInRepositoryRule implements Rule
{
    private const array DATABASE_CLASSES = [Connection::class, Fluent::class];

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $type = $this->databaseCandidateType($node, $scope);
        if ($type === null || $this->isDatabaseType($type) === false || $this->isAllowedScope($scope)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Access the database through a Repository class (src/Orm/Repository.php), never via Connection/Fluent directly.',
            )->identifier('kissj.databaseAccess')->build(),
        ];
    }

    private function databaseCandidateType(CallLike $node, Scope $scope): ?Type
    {
        if ($node instanceof New_) {
            return $scope->getType($node);
        }

        if ($node instanceof MethodCall) {
            return TypeCombinator::removeNull($scope->getType($node->var));
        }

        return null;
    }

    private function isDatabaseType(Type $type): bool
    {
        foreach (self::DATABASE_CLASSES as $className) {
            if ((new ObjectType($className))->isSuperTypeOf($type)->yes()) {
                return true;
            }
        }

        return false;
    }

    private function isAllowedScope(Scope $scope): bool
    {
        $namespace = $scope->getNamespace() ?? '';
        if ($namespace === 'Tests' || str_starts_with($namespace, 'Tests\\') || $namespace === 'kissj\\Orm') {
            return true;
        }

        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return false;
        }

        return $classReflection->getName() === Settings::class || $classReflection->is(Repository::class);
    }
}
