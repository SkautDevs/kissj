<?php

declare(strict_types=1);

namespace kissj\Phpstan;

use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<Assign>
 */
class UserStatusWriteOnlyInUserServiceRule implements Rule
{
    private const array ALLOWED_CLASSES = [User::class, UserRepository::class, UserService::class];

    public function getNodeType(): string
    {
        return Assign::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $target = $node->var;
        if (
            $target instanceof PropertyFetch === false
            || $target->name instanceof Identifier === false
            || $target->name->name !== 'status'
        ) {
            return [];
        }

        $namespace = $scope->getNamespace() ?? '';
        if ($namespace === 'Tests' || str_starts_with($namespace, 'Tests\\')) {
            return [];
        }

        if (in_array($scope->getClassReflection()?->getName(), self::ALLOWED_CLASSES, true)) {
            return [];
        }

        if ((new ObjectType(User::class))->isSuperTypeOf($scope->getType($target->var))->yes() === false) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Change User::$status only through UserService (setUserOpen/Closed/Approved/Paid/Cancelled), so status '
                    . 'transitions stay in one place.',
            )->identifier('kissj.userStatusWrite')->build(),
        ];
    }
}
