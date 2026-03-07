<?php

declare(strict_types=1);

namespace Google\Protobuf;

use Thesis\Protobuf\Decoder;
use Thesis\Protobuf\Encoder;
use Thesis\Protobuf\Pool;

const anyTypeUrlPrefix = 'type.googleapis.com/';

/**
 * @api
 * @template T of object
 * @param T $message
 * @param ?\Closure(T): ?non-empty-string $resolveClassType
 * @throws Encoder\EncodingError
 */
function encodeAny(object $message, Encoder $encoder, ?\Closure $resolveClassType = null): Any
{
    $resolveClassType ??= static fn(object $_) => null;

    return new Any(
        typeUrl: $resolveClassType($message) ?? anyTypeUrlPrefix . Pool\Registry::get()->classType($message::class),
        value: $encoder->encode($message),
    );
}

/**
 * @api
 * @template T of object
 * @param ?\Closure(non-empty-string): ?class-string<T> $resolveType
 * @throws Decoder\DecodingError
 */
function decodeAny(Any $any, Decoder $decoder, ?\Closure $resolveType = null): object
{
    $resolveType ??= static fn(string $_) => null;

    $typeUrl = $any->typeUrl;
    if (str_starts_with($typeUrl, anyTypeUrlPrefix)) {
        $typeUrl = substr($typeUrl, \strlen(anyTypeUrlPrefix));
    }

    if ($typeUrl === '') {
        throw new \RuntimeException('Type url in "google.protobuf.Any" cannot be empty.');
    }

    $type = $resolveType($typeUrl) ?? Pool\Registry::get()
        ->messageDescriptorByType($typeUrl)
        ->fqcn;

    return $decoder->decode($any->value, $type);
}
