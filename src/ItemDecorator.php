<?php
namespace Jsq\CacheEncryption;

use Psr\Cache\CacheItemInterface;

abstract class ItemDecorator implements CacheItemInterface
{
    /** @var string */
    private $cipher;
    /** @var mixed */
    private $decrypted;
    /** @var CacheItemInterface */
    private $decorated;

    public function __construct($cipher, CacheItemInterface $decorated)
    {
        $this->cipher = $cipher;
        $this->decorated = $decorated;
    }

    public function getKey()
    {
        return $this->decorated->getKey();
    }

    /**
     * @return CacheItemInterface
     */
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

    public function get()
    {
        if (empty($this->decrypted) && $this->isHit()) {
            $this->decrypted = $this->decrypt($this->getDecorated()->get());
        }

        return $this->decrypted;
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

    protected function generateIv()
    {
        return random_bytes(openssl_cipher_iv_length($this->cipher));
    }

    protected function encryptString($string, $key, $iv)
    {
        return openssl_encrypt($string, $this->cipher, $key, 0, $iv);
    }

    protected function decryptString($string, $method, $key, $iv)
    {
        return openssl_decrypt($string, $method, $key, 0, $iv);
    }

    protected function getCipherMethod()
    {
        return $this->cipher;
    }

    /**
     * @param $data
     * 
     * @return EncryptedValue
     */
    abstract protected function encrypt($data);

    /**
     * @param EncryptedValue $data
     * 
     * @return mixed
     */
    abstract protected function decrypt($data);

    /**
     * @return bool
     */
    abstract protected function isDecryptable();
}
