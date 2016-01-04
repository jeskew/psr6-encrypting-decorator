# PSR-6 Cache Encrypter

[![Build Status](https://travis-ci.org/jeskew/psr6-encrypting-decorator.svg?branch=master)](https://travis-ci.org/jeskew/psr6-encrypting-decorator)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jeskew/psr6-encrypting-decorator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jeskew/psr6-encrypting-decorator/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jeskew/psr6-encrypting-decorator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jeskew/psr6-encrypting-decorator/?branch=master)
[![Apache 2 License](https://img.shields.io/packagist/l/jeskew/psr6-encrypting-decorator.svg?style=flat)](https://www.apache.org/licenses/LICENSE-2.0.html)
[![Total Downloads](https://img.shields.io/packagist/dt/jeskew/psr6-encrypting-decorator.svg?style=flat)](https://packagist.org/packages/jeskew/psr6-encrypting-decorator)
[![Author](http://img.shields.io/badge/author-@jreskew-blue.svg?style=flat-square)](https://twitter.com/jreskew)

Having to encrypt your data at rest shouldn't keep you from using the open-source
tools you know and love. If you have data that needs a higher degree of security
than the rest of your cache, you can store and access it via an 
`EncryptingPoolDecorator`.

## Caveats

Encryption and decryption are both expensive operations, and frequent reads from
an encrypted data store can quickly become a bottleneck in otherwise performant
applications. Use encrypted caches sparingly.

## Usage

> This package provides two cache decorators, one that encrypts data using
a pass phrase and one that does so with a key pair.

First, create your PSR-6 cache as you normally would, then wrap your cache with
an encrypting decorator:
```php
$encryptedCache = new \Jeskew\Cache\PasswordEncryptingPoolDecorator(
    $cache, // an instance of \Psr\Cache\CacheItemPoolInterface
    $password,
    $cipher // optional, defaults to 'aes-256-cbc'
);
```

Then use your `$cache` and `$encryptedCache` like you normally would:
```php
$cache->getItem('normal_cache_data')->set('Totally normal!');

$encryptedCache->getItem('api_keys')->set($keys);
```

Though your regular cache and encrypted cache share a storage layer and a
keyspace, they will not be able to read each other's data. The `$encryptedCache`
will return `false` for `isHit` if the underlying data is not encrypted, and the
regular `$cache` will return gibberish if asked to read encrypted data.

## Encrypting your cache with a key pair

If you'd rather not rely on a shared password, the `EnvelopeEncryptionPoolDecorator`
can secure your sensitive cache entries using a public/private key pair.

```php
$encryptedCache = new \Jeskew\Cache\EnvelopeEncryptionPoolDecorator(
    $cache,
    'file:///path/to/certificate.pem',
    'file:///path/to/private/key.pem',
    $passphrase_for_private_key_file, // optional, defaults to null
    $cipher // optional, defaults to 'aes-256-cbc'
);
```

> The certificate can be a valid x509 certificate, a path to a PEM-encoded
certificate file (the path must be prefaced with `file://`), or a PEM-encoded
certificate string. The private key can be a path to a PEM-encoded private key
file (the path must be prefaced with `file://`), or a PEM-encoded certificate
string.
