<?php

declare(strict_types=1);

namespace kissj\Phpstan;

use kissj\Application\ApplicationGetter;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Settings\Settings;
use kissj\User\UserController;
use kissj\User\UserRegeneration;
use kissj\User\UserService;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Variable>
 */
class NoSuperglobalsRule implements Rule
{
    private const array SUPERGLOBALS = [
        '_GET',
        '_POST',
        '_COOKIE',
        '_FILES',
        '_REQUEST',
        '_SERVER',
        '_SESSION',
        '_ENV',
    ];

    private const array ALLOWED_CLASSES = [
        Settings::class,
        ApplicationGetter::class,
        FlashMessagesBySession::class,
        UserRegeneration::class,
        UserController::class,
        UserService::class,
    ];

    private const string ALLOWED_FILE_NAME = 'phinxConfiguration.php';

    public function getNodeType(): string
    {
        return Variable::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (is_string($node->name) === false || in_array($node->name, self::SUPERGLOBALS, true) === false) {
            return [];
        }

        $namespace = $scope->getNamespace() ?? '';
        if (
            $namespace === 'Tests'
            || str_starts_with($namespace, 'Tests\\')
            || basename($scope->getFile()) === self::ALLOWED_FILE_NAME
            || in_array($scope->getClassReflection()?->getName(), self::ALLOWED_CLASSES, true)
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Read request/session/env data through the PSR-7 request, EnvSettings, FlashMessagesBySession or '
                    . 'UserRegeneration, not superglobals.',
            )->identifier('kissj.superglobal')->build(),
        ];
    }
}
