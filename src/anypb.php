<?php

declare(strict_types=1);

namespace Google\Protobuf;

use Thesis\Protobuf\Decoder;
use Thesis\Protobuf\Encoder;
use Thesis\Protobuf\Registry;

const anyTypeUrlPrefix = 'type.googleapis.com/';

/**
 * @api
 * @template T of object
 * @param T $message
 * @param ?\Closure(T): non-empty-string $resolveClassType
 * @throws Encoder\EncodingError
 */
function encodeAny(object $message, Encoder $encoder, ?\Closure $resolveClassType = null): Any
{
    $resolveClassType ??= static fn(object $message) => Registry\Pool::get()->classType($fqcn = $message::class) ?? throw new \RuntimeException(
        "Associated with class '{$fqcn}' metadata not found in the \\Thesis\\Protobuf\\Registry\\Pool. Perhaps you forgot to include autoload.metadata.php in composer.json or did not call the appropriate descriptor registrar to register types in the pool?",
    );

    return new Any(
        typeUrl: anyTypeUrlPrefix . $resolveClassType($message),
        value: $encoder->encode($message),
    );
}

/**
 * @api
 * @template T of object
 * @param ?\Closure(non-empty-string): class-string<T> $resolveType
 * @throws Decoder\DecodingError
 */
function decodeAny(Any $any, Decoder $decoder, ?\Closure $resolveType = null): object
{
    $resolveType ??= static fn(string $typeUrl) => Registry\Pool::get()
        ->messageDescriptorByType($typeUrl) // @phpstan-ignore argument.type
        ->fqcn ?? throw new \RuntimeException(
            "Type metadata '{$typeUrl}' not found in the \\Thesis\\Protobuf\\Registry\\Pool. Perhaps you forgot to include autoload.metadata.php in composer.json or did not call the appropriate descriptor registrar to register types in the pool?",
        );

    $typeUrl = $any->typeUrl;
    if (str_starts_with($typeUrl, anyTypeUrlPrefix)) {
        $typeUrl = substr($typeUrl, \strlen(anyTypeUrlPrefix));
    }

    if ($typeUrl === '') {
        throw new \RuntimeException('Type url in "google.protobuf.Any" cannot be empty.');
    }

    return $decoder->decode($any->value, $resolveType($typeUrl));
}
