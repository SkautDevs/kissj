<?php

declare(strict_types=1);

namespace Tests\Unit\Telemetry\Sentry;

use kissj\Telemetry\Sentry\SqlParameterizer;
use PHPUnit\Framework\TestCase;

final class SqlParameterizerTest extends TestCase
{
    public function testPassesThroughSqlWithNoLiterals(): void
    {
        self::assertSame(
            'SELECT * FROM participant',
            SqlParameterizer::parameterize('SELECT * FROM participant'),
        );
    }

    public function testReplacesStringLiteralWithPlaceholder(): void
    {
        self::assertSame(
            "SELECT * FROM participant WHERE first_name = ?",
            SqlParameterizer::parameterize("SELECT * FROM participant WHERE first_name = 'Alice'"),
        );
    }

    public function testHandlesEscapedSingleQuoteInStringLiteral(): void
    {
        self::assertSame(
            "SELECT * FROM participant WHERE last_name = ?",
            SqlParameterizer::parameterize("SELECT * FROM participant WHERE last_name = 'O''Brien'"),
        );
    }

    public function testReplacesIntegerLiteralWithPlaceholder(): void
    {
        self::assertSame(
            'SELECT * FROM participant WHERE id = ?',
            SqlParameterizer::parameterize('SELECT * FROM participant WHERE id = 42'),
        );
    }

    public function testReplacesDecimalLiteralWithPlaceholder(): void
    {
        self::assertSame(
            'SELECT * FROM payment WHERE amount = ?',
            SqlParameterizer::parameterize('SELECT * FROM payment WHERE amount = 99.50'),
        );
    }

    public function testReplacesMultipleLiteralsInInsert(): void
    {
        self::assertSame(
            'INSERT INTO participant (first_name, age) VALUES (?, ?)',
            SqlParameterizer::parameterize("INSERT INTO participant (first_name, age) VALUES ('Bob', 30)"),
        );
    }

    public function testDoesNotTouchDigitsInsideIdentifiers(): void
    {
        self::assertSame(
            'UPDATE participant SET addr_line1 = ? WHERE id = ?',
            SqlParameterizer::parameterize("UPDATE participant SET addr_line1 = 'X' WHERE id = 5"),
        );
    }

    public function testPreservesNullKeyword(): void
    {
        self::assertSame(
            'SELECT * FROM participant WHERE deleted_at IS NULL',
            SqlParameterizer::parameterize('SELECT * FROM participant WHERE deleted_at IS NULL'),
        );
    }

    public function testHandlesEmptyString(): void
    {
        self::assertSame('', SqlParameterizer::parameterize(''));
    }

    public function testReplacesDollarQuotedStringWithoutTag(): void
    {
        self::assertSame(
            'INSERT INTO secrets VALUES (?)',
            SqlParameterizer::parameterize('INSERT INTO secrets VALUES ($$super-secret$$)'),
        );
    }

    public function testReplacesDollarQuotedStringWithTag(): void
    {
        self::assertSame(
            'INSERT INTO secrets VALUES (?)',
            SqlParameterizer::parameterize('INSERT INTO secrets VALUES ($tag$super-secret$tag$)'),
        );
    }

    public function testReplacesDollarQuotedStringContainingApostrophes(): void
    {
        self::assertSame(
            'INSERT INTO log VALUES (?)',
            SqlParameterizer::parameterize("INSERT INTO log VALUES (\$body\$it's a 'string'\$body\$)"),
        );
    }

    public function testDoesNotEatPostgresBoundParameterPlaceholders(): void
    {
        self::assertSame(
            'SELECT * FROM x WHERE id = $1 AND name = ?',
            SqlParameterizer::parameterize("SELECT * FROM x WHERE id = \$1 AND name = 'Alice'"),
        );
    }

    public function testReplacesScientificNotationCleanly(): void
    {
        self::assertSame(
            'SELECT * FROM payment WHERE amount > ?',
            SqlParameterizer::parameterize('SELECT * FROM payment WHERE amount > 1.5e10'),
        );
        self::assertSame(
            'SELECT * FROM payment WHERE amount = ?',
            SqlParameterizer::parameterize('SELECT * FROM payment WHERE amount = 1E5'),
        );
        self::assertSame(
            'SELECT * FROM payment WHERE amount = ?',
            SqlParameterizer::parameterize('SELECT * FROM payment WHERE amount = 2.5e-3'),
        );
    }

    public function testDoesNotLeakPiiOnPathologicalQuoteEscapes(): void
    {
        $payload = "INSERT INTO participant (notes) VALUES ('" . str_repeat("a''", 50000) . "no_closing_quote";
        $result = SqlParameterizer::parameterize($payload);

        self::assertStringNotContainsString("a''a''", $result);
    }

    public function testFallsThroughToRedactedSentinelOnPcreFailure(): void
    {
        // Invalid UTF-8 (lone continuation byte 0xC0) makes /u-mode preg_replace return null.
        $invalidUtf8 = "SELECT * FROM x WHERE name = '\xC0'";

        self::assertSame(SqlParameterizer::REDACTED, SqlParameterizer::parameterize($invalidUtf8));
    }
}
