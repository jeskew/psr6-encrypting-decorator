<?php
namespace Jeskew\Cache;

use Psr\Cache\CacheItemInterface;

abstract class EncryptingItemDecorator implements CacheItemInterface
{
    /** @var CacheItemInterface */
    private $decorated;
    /** @var mixed */
    private $decrypted;

    public function __construct(CacheItemInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function getKey()
    {
        return $this->decorated->getKey();
    }

    public function get()
    {
        if (empty($this->decrypted) && $this->isDecryptable()) {
            $this->decrypted = $this->decrypt($this->decorated->get());
        }

        return $this->decrypted;
    }

    public function getDecorated()
    {
        return $this->decorated;
    }

    public function set($value)
    {
        $this->decorated->set($this->encrypt($value));
        $this->decrypted = $value;

        return $this;
    }

    public function isHit()
    {
        return $this->decorated->isHit()
            && $this->isDecryptable();
    }

    public function expiresAt($expiresAt)
    {
        $this->decorated->expiresAt($expiresAt);

        return $this;
    }

    public function expiresAfter($expiresAfter)
    {
        $this->decorated->expiresAfter($expiresAfter);

        return $this;
    }

    abstract protected function encrypt($data);

    abstract protected function decrypt(EncryptedValue $data);

    abstract protected function isDecryptable();
}
