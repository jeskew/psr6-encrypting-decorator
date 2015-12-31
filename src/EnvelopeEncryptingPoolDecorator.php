<?php
namespace Jeskew\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class EnvelopeEncryptingPoolDecorator extends EncryptingPoolDecorator
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
        return new EnvelopeEncryptingItemDecorator(
            $inner,
            $this->certificate,
            $this->key,
            $this->passPhrase,
            $this->cipher
        );
    }

    protected function validateEncryption(CacheItemInterface $item)
    {
        return $item instanceof EnvelopeEncryptingItemDecorator;
    }
}
