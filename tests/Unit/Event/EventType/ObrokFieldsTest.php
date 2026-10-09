<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\ContentArbiter\AgeGroup;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use kissj\Participant\Troop\TroopLeader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class ObrokFieldsTest extends TestCase
{
    public function testIstArrivalDateHasHelpText(): void
    {
        $ca = (new EventTypeObrok())->getContentArbiterIst();

        self::assertSame('detail.arrivalDate-helptext', $ca->arrivalDate->helpText);
    }

    public function testIstLanguagesAreOptionalWithBuddyHelpText(): void
    {
        $ca = (new EventTypeObrok())->getContentArbiterIst();

        self::assertTrue($ca->languages->allowed);
        self::assertFalse($ca->languages->required);
        self::assertSame('detail.language-helptext', $ca->languages->helpText);
    }

    public function testIstNotesHaveKindergartenHelpText(): void
    {
        $ca = (new EventTypeObrok())->getContentArbiterIst();

        self::assertSame('detail.notice-helptext', $ca->notes->helpText);
    }

    public function testIstConsentsLinkTemplate(): void
    {
        $ca = (new EventTypeObrok())->getContentArbiterIst();

        self::assertSame('detail.parentalConsent-helptext', $ca->parentalConsent->helpText);
        self::assertSame('detail.hospitalConsent-helptext', $ca->hospitalConsent->helpText);
    }

    public function testTroopRolesHaveUnder18GuardianContact(): void
    {
        $eventType = new EventTypeObrok();

        foreach ([$eventType->getContentArbiterTroopLeader(), $eventType->getContentArbiterTroopParticipant()] as $ca) {
            self::assertTrue($ca->emergencyContact->allowed);
            self::assertTrue($ca->emergencyContact->required);
            self::assertTrue($ca->emergencyContact->editableAfterLock);
            self::assertSame(AgeGroup::Under18, $ca->emergencyContact->ageGroup);
            self::assertSame('detail.parentalConsent-helptext', $ca->parentalConsent->helpText);
            self::assertSame('detail.hospitalConsent-helptext', $ca->hospitalConsent->helpText);
        }
    }

    public function testOrganizingTeamNotesHaveKindergartenHelpText(): void
    {
        $ca = (new EventTypeObrok())->getContentArbiterOrganizingTeam();

        self::assertSame('detail.notice-helptext', $ca->notes->helpText);
    }

    public function testGuardianContactIsRelabelled(): void
    {
        $translations = Yaml::parseFile(__DIR__ . '/../../../../src/Event/EventType/Obrok/cs_obrok.yaml');
        self::assertIsArray($translations);
        self::assertIsArray($translations['detail']);

        self::assertSame('Kontakt na zákonného zástupce (jméno + telefon)', $translations['detail']['emergencyContact']);
    }

    public function testOffersNoDeals(): void
    {
        $troopLeader = new TroopLeader();
        $troopLeader->tieCode = 'ABC123';

        self::assertSame([], (new EventTypeObrok())->getEventDeals($troopLeader));
    }

    public function testPrintedHandbookLabelPrefersPrint(): void
    {
        $translations = Yaml::parseFile(__DIR__ . '/../../../../src/Event/EventType/Obrok/cs_obrok.yaml');
        self::assertIsArray($translations);
        self::assertIsArray($translations['detail']);

        self::assertSame('Preferuji tištěný handbook namísto digitálního', $translations['detail']['printedHandbook-detail']);
    }

    public function testNoLinksToOldObrokWebsite(): void
    {
        $contents = file_get_contents(__DIR__ . '/../../../../src/Event/EventType/Obrok/cs_obrok.yaml');

        self::assertIsString($contents);
        self::assertStringNotContainsString('obrok24.cz', $contents);
        self::assertStringContainsString("href='https://obrok.skaut.cz/registrace'", $contents);
    }
}
