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
    static fn(X $x) => 'x',
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
    new Protobuf\Any('type.googleapis.com/x', '...'),
    Decoder\Builder::buildDefault(),
    static fn(string $type) => X::class,
);
```

In both cases, in `encodeAny` and `decodeAny`, your resolvers may return null to fall back to default type resolution using `Pool\Registry`.

### Timestamp

`google.protobuf.Timestamp` stores seconds since the Unix epoch and non-negative nanoseconds, which is precise but inconvenient. Use the helpers under `Google\Protobuf\Timestamp` to convert to and from native dates:

```php
use Google\Protobuf\Timestamp;

$timestamp = Timestamp\now();

$timestamp = Timestamp\fromDateTime(new DateTimeImmutable('2026-01-15 10:30:00'));

$timestamp = Timestamp\fromSeconds(1768473000);
$timestamp = Timestamp\fromSeconds(1768473000, nanos: 500_000_000);

$date = Timestamp\toDateTime($timestamp);
```

`toDateTime` always returns a `DateTimeImmutable` in UTC. Nanoseconds are truncated to microseconds, since PHP dates cannot represent anything finer.

Timestamps must stay within the range defined by the specification — from `0001-01-01T00:00:00Z` to `9999-12-31T23:59:59Z`, with nanos between 0 and 999999999.
All the functions above validate this and throw an `InvalidArgumentException` otherwise. 