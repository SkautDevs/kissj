<?php

declare(strict_types=1);

namespace Tests\Unit\Templates;

use kissj\Event\Event;
use kissj\Participant\ParticipantRole;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class ChooseRoleTranslationsTest extends TestCase
{
    public function testEverySelectableRoleHasChooserTranslationsInAllLocales(): void
    {
        $event = new Event();
        $event->allowPatrols = true;
        $event->allowTroops = true;
        $event->allowIsts = true;
        $event->allowGuests = true;
        $event->allowOrganizingTeam = true;
        $event->allowTechnicalTeams = true;

        $missing = [];
        foreach (['cs', 'en', 'sk'] as $locale) {
            $translations = Yaml::parseFile(__DIR__ . '/../../../src/Templates/' . $locale . '.yaml');
            foreach ($event->getSelectableRoles(true, true) as $role) {
                $capitalized = ucfirst(strtolower($role->value));
                foreach ($this->keysFor($role, $capitalized) as $key) {
                    if ($this->resolve($translations, $key) === null) {
                        $missing[] = $locale . ':' . $key;
                    }
                }
            }
        }

        self::assertSame([], $missing);
    }

    /**
     * @return list<string>
     */
    private function keysFor(ParticipantRole $role, string $capitalized): array
    {
        return [
            'chooseRole.registerAs' . $capitalized,
            'chooseRole.advice' . $capitalized . 'Text',
            'role.' . $role->value,
        ];
    }

    private function resolve(mixed $translations, string $key): ?string
    {
        foreach (explode('.', $key) as $part) {
            if (is_array($translations) === false || array_key_exists($part, $translations) === false) {
                return null;
            }
            $translations = $translations[$part];
        }

        return is_string($translations) ? $translations : null;
    }
}
