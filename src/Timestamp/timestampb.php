<?php

declare(strict_types=1);

namespace Google\Protobuf\Timestamp;

use BcMath\Number;
use Google\Protobuf\Timestamp;

/** 0001-01-01T00:00:00Z */
const MIN_SECONDS = -62_135_596_800;

/** 9999-12-31T23:59:59Z */
const MAX_SECONDS = 253_402_300_799;
const NANOS_PER_SECOND = 1_000_000_000;
const NANOS_PER_MICROSECOND = 1_000;

/**
 * @api
 * @throws \InvalidArgumentException if the resulting timestamp is out of the range supported by protobuf.
 */
function fromDateTime(\DateTimeInterface $time): Timestamp
{
    $seconds = new Number($time->format('U'));
    $nanos = ((int) $time->format('u')) * NANOS_PER_MICROSECOND;

    return createTimestamp($seconds, $nanos);
}

/**
 * Converts a protobuf timestamp to a native date.
 *
 * Nanosecond precision is truncated to microseconds: PHP's date objects
 * cannot represent anything finer.
 *
 * @api
 * @throws \InvalidArgumentException if the timestamp is malformed or out of range.
 * @throws \DateMalformedStringException
 */
function toDateTime(Timestamp $timestamp): \DateTimeImmutable
{
    assertValid($timestamp);

    $seconds = (int) (string) $timestamp->seconds;
    $micros = intdiv($timestamp->nanos, NANOS_PER_MICROSECOND);

    $time = new \DateTimeImmutable('@' . $seconds)
        ->setTimezone(new \DateTimeZone('UTC'));

    // Protobuf keeps nanos non-negative and always counting forward in time,
    // so the fraction is added to the (possibly negative) whole second.
    if ($micros > 0) {
        $time = $time->modify(\sprintf('+%d microseconds', $micros));
    }

    return $time;
}

/**
 * @api
 */
function now(): Timestamp
{
    return fromDateTime(new \DateTimeImmutable('now'));
}

/**
 * @api
 * @throws \InvalidArgumentException
 */
function fromSeconds(Number|int|string $seconds, int $nanos = 0): Timestamp
{
    return createTimestamp($seconds instanceof Number ? $seconds : new Number((string) $seconds), $nanos);
}

/**
 * @internal
 * @throws \InvalidArgumentException
 */
function createTimestamp(Number $seconds, int $nanos): Timestamp
{
    $timestamp = new Timestamp(seconds: $seconds, nanos: $nanos);
    assertValid($timestamp);

    return $timestamp;
}

/**
 * @internal
 * @throws \InvalidArgumentException if the timestamp violates the protobuf contract.
 */
function assertValid(Timestamp $timestamp): void
{
    if ($timestamp->nanos < 0 || $timestamp->nanos >= NANOS_PER_SECOND) {
        throw new \InvalidArgumentException(
            \sprintf('Timestamp nanos must be between 0 and 999999999, got %d.', $timestamp->nanos),
        );
    }

    if ($timestamp->seconds < MIN_SECONDS || $timestamp->seconds > MAX_SECONDS) {
        throw new \InvalidArgumentException(
            \sprintf(
                'Timestamp seconds must be between %d and %d, got %s.',
                MIN_SECONDS,
                MAX_SECONDS,
                $timestamp->seconds,
            ),
        );
    }
}
