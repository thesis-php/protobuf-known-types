## Generated types for protocol buffers [known types](https://github.com/protocolbuffers/protobuf/tree/main/src/google/protobuf).

### Installation

```shell
composer require thesis/protobuf-known-types
```

### Usage

To encode or decode `google.protobuf.Any` type use `encodeAny/decodeAny` respectively.

```php
use Google\Protobuf;
use Thesis\Protobuf\Encoder;

$any = Protobuf\encodeAny(X::class, Encoder\Builder::buildDefault());
```

Note that in this case, `X` should be autoloaded to the descriptor pool (`Thesis\Protobuf\Pool\Registry`) using `autoload.metadata.php` in `composer.json` or any other custom mechanism.
Otherwise, an `RuntimeException` will be thrown. 

If you strongly understand what you are doing and your types are not registered in the `Pool\Registry`, pass your own object type name resolver:
```php
use Google\Protobuf;
use Thesis\Protobuf\Encoder;

$any = Protobuf\encodeAny(
    X::class,
    Encoder\Builder::buildDefault(),
    static fn(X $x) => 'my.own.types/x',
);
```

To decode `google.protobuf.Any` do the opposite using `decodeAny`:
```php
use Google\Protobuf;
use Thesis\Protobuf\Decoder;

$x = Protobuf\decodeAny(
    new Protobuf\Any('type.googleapis.com/x', '...'),
    Decoder\Builder::buildDefault(),
);
```

Again, if you understand what you are doing, you can use custom class resolver:
```php
use Google\Protobuf;
use Thesis\Protobuf\Decoder;

$x = Protobuf\decodeAny(
    new Protobuf\Any('my.own.types/x', '...'),
    Decoder\Builder::buildDefault(),
    static fn(string $type) => X::class,
);
```

In both cases, in `encodeAny` and `decodeAny`, your resolvers may return null to fall back to default type resolution using `Pool\Registry`.