<?php
namespace Jsq\Cache\Envelope;

use Cache\Adapter\Common\CacheItem;
use Jsq\Cache\EncryptingItemDecoratorTest as BaseItemDecoratorTest;
use Jsq\Cache\PkiUtils;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;

class EncryptingItemDecoratorTest extends BaseItemDecoratorTest
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
        new EncryptingItemDecorator(
            new CacheItem('key'),
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
        return new EncryptingItemDecorator(
            $decorated,
            self::getCertificate(),
            self::getKey(),
            null,
            'aes256'
        );
    }
}
