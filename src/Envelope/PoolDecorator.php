<?php
namespace Jsq\CacheEncryption\Envelope;

use Jsq\CacheEncryption\PoolDecorator as BasePoolDecorator;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class PoolDecorator extends BasePoolDecorator
{
    /** @var resource|string */
    private $certificate;
    /** @var string */
    private $key;
    /** @var null|string */
    private $passPhrase;
    /** @var string */
    private $cipher;

    /**
     * @param CacheItemPoolInterface $decorated
     * @param string|resource $certificate
     * @param string $key
     * @param string|null $passPhrase
     * @param string $cipher
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function __construct(
        CacheItemPoolInterface $decorated,
        $certificate,
        $key,
        $passPhrase = null,
        $cipher = 'aes-256-cbc'
    ) {
        parent::__construct($decorated);
        $this->cipher = $cipher;
        $this->certificate = $certificate;
        $this->key = $key;
        $this->passPhrase = $passPhrase;
    }

    protected function decorate(CacheItemInterface $inner)
    {
        return new ItemDecorator(
            $inner,
            $this->certificate,
            $this->key,
            $this->passPhrase,
            $this->cipher
        );
    }
}
