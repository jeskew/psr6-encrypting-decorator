<?php
namespace Jeskew\Cache;

use Jeskew\Cache\Fixtures\ArrayCacheItem;
use Psr\Cache\CacheItemInterface;

class EnvelopeEncryptingItemDecoratorTest extends EncryptingItemDecoratorTest
{
    use PkiUtils;

    /**
     * @dataProvider invalidParameterProvider
     *
     * @param $cert
     * @param $key
     *
     * @expectedException InvalidArgumentException
     */
    public function testVerifiesCertificateAndKey($cert, $key)
    {
        new EnvelopeEncryptingItemDecorator(
            new ArrayCacheItem('key'),
            $cert,
            $key,
            null,
            'aes256'
        );
    }

    public function invalidParameterProvider()
    {
        return [
            ['not a certificate', 'not a PEM-formatted key'],
            [self::getCertificate(), 'not a PEM-formatted key'],
            ['not a certificate', self::getKey()],
        ];
    }

    protected function getInstance(CacheItemInterface $decorated)
    {
        return new EnvelopeEncryptingItemDecorator(
            $decorated,
            self::getCertificate(),
            self::getKey(),
            null,
            'aes256'
        );
    }
}
