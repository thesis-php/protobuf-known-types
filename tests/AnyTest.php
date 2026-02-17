<?php

declare(strict_types=1);

namespace Google\Protobuf;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\TestCase;
use Thesis\Protobuf\Decoder;
use Thesis\Protobuf\Encoder;
use Thesis\Protobuf\Reflection;

#[CoversFunction('Google\Protobuf\encodeAny')]
#[CoversFunction('Google\Protobuf\decodeAny')]
#[CoversClass(Any::class)]
final class AnyTest extends TestCase
{
    public function testEncodeAny(): void
    {
        $any = encodeAny(new FieldMask(['x', 'y']), Encoder\Builder::buildDefault());
        self::assertSame('type.googleapis.com/google.protobuf.FieldMask', $any->typeUrl);
        self::assertNotEmpty($any->value);

        $mask = decodeAny($any, Decoder\Builder::buildDefault());
        self::assertEquals(new FieldMask(['x', 'y']), $mask);
    }

    public function testEncodeAnyCustomResolver(): void
    {
        $any = encodeAny(new X('test'), Encoder\Builder::buildDefault(), static fn(X $x) => 'com.thesis.types/x');
        self::assertSame('com.thesis.types/x', $any->typeUrl);
        self::assertNotEmpty($any->value);

        $x = decodeAny($any, Decoder\Builder::buildDefault(), static fn(string $type) => X::class);
        self::assertEquals(new X('test'), $x);
    }
}

final readonly class X
{
    public function __construct(
        #[Reflection\Field(1, Reflection\StringT::T)]
        public string $name,
    ) {}
}
