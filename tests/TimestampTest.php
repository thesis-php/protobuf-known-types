<?php

declare(strict_types=1);

namespace Google\Protobuf;

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversFunction('Google\Protobuf\Timestamp\now')]
#[CoversFunction('Google\Protobuf\Timestamp\fromDateTime')]
#[CoversFunction('Google\Protobuf\Timestamp\toDateTime')]
final class TimestampTest extends TestCase
{
    #[DataProvider('dates')]
    public function testFromDateTime(string $iso, int $seconds, int $nanos): void
    {
        $timestamp = Timestamp\fromDateTime(
            new \DateTimeImmutable($iso),
        );

        self::assertSame($seconds, $timestamp->seconds);
        self::assertSame($nanos, $timestamp->nanos);
    }

    #[DataProvider('dates')]
    public function testToDateTime(string $iso, int $seconds, int $nanos): void
    {
        $time = Timestamp\toDateTime(
            new Timestamp($seconds, $nanos),
        );

        self::assertSame($iso, $time->format('Y-m-d\TH:i:s.u\Z'));
        self::assertSame('UTC', $time->getTimezone()->getName());
    }

    #[DataProvider('dates')]
    public function testRoundTrip(string $iso, int $seconds, int $nanos): void
    {
        $original = new \DateTimeImmutable($iso);

        self::assertEquals(
            $original,
            Timestamp\toDateTime(Timestamp\fromDateTime($original)),
        );
    }

    /**
     * @return iterable<string, array{string, int, int}>
     */
    public static function dates(): iterable
    {
        yield 'epoch' => ['1970-01-01T00:00:00.000000Z', 0, 0];
        yield 'epoch with micros' => ['1970-01-01T00:00:00.000001Z', 0, 1_000];
        yield 'positive' => ['2026-01-15T10:30:00.123456Z', 1_768_473_000, 123_456_000];
        yield 'one second before epoch' => ['1969-12-31T23:59:59.000000Z', -1, 0];
        yield 'half second before epoch' => ['1969-12-31T23:59:59.500000Z', -1, 500_000_000];
        yield 'min supported' => ['0001-01-01T00:00:00.000000Z', -62_135_596_800, 0];
        yield 'max supported' => ['9999-12-31T23:59:59.999999Z', 253_402_300_799, 999_999_000];
    }

    public function testNanosecondPrecisionIsTruncatedToMicroseconds(): void
    {
        $time = Timestamp\toDateTime(
            new Timestamp(0, 123_456_789),
        );

        self::assertSame('123456', $time->format('u'));
    }

    public function testTimezoneIsNormalizedToUtc(): void
    {
        $time = new \DateTimeImmutable('2026-01-15T13:30:00', new \DateTimeZone('Europe/Riga'));

        $timestamp = Timestamp\fromDateTime($time);

        self::assertSame(1_768_476_600, $timestamp->seconds);
    }

    public function testNowIsCloseToCurrentTime(): void
    {
        $before = time();
        $timestamp = Timestamp\now();
        $after = time();

        self::assertGreaterThanOrEqual($before, $timestamp->seconds);
        self::assertLessThanOrEqual($after, $timestamp->seconds);
    }

    #[DataProvider('provideToDateTimeRejectsMalformedTimestampsCases')]
    public function testToDateTimeRejectsMalformedTimestamps(Timestamp $timestamp, string $_): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Timestamp\toDateTime($timestamp);
    }

    /**
     * @return iterable<string, array{Timestamp, string}>
     */
    public static function provideToDateTimeRejectsMalformedTimestampsCases(): iterable
    {
        yield 'negative nanos' => [
            new Timestamp(0, -1),
            'nanos must be between',
        ];

        yield 'nanos overflow' => [
            new Timestamp(0, 1_000_000_000),
            'nanos must be between',
        ];

        yield 'seconds below min' => [
            new Timestamp(-62_135_596_801, 0),
            'seconds must be between',
        ];

        yield 'seconds above max' => [
            new Timestamp(253_402_300_800, 0),
            'seconds must be between',
        ];
    }

    public function testFromDateTimeRejectsOutOfRangeDates(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Timestamp\fromDateTime(new \DateTimeImmutable('@253402300800'));
    }

    public function testFromSecondsAcceptsScalarInput(): void
    {
        self::assertEquals(
            new Timestamp(1_768_473_000, 0),
            Timestamp\fromSeconds(1_768_473_000),
        );

        self::assertEquals(
            new Timestamp(1_768_473_000, 500_000_000),
            Timestamp\fromSeconds(1_768_473_000, 500_000_000),
        );
    }
}
