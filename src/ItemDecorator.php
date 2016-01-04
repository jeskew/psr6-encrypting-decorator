<?php
namespace Jsq\CacheEncryption;

use Psr\Cache\CacheItemInterface;

abstract class ItemDecorator implements CacheItemInterface
{
    /** @var CacheItemInterface */
    private $decorated;
    /** @var mixed */
    private $decrypted;
    /** @var string */
    private $cipher;

    public function __construct(CacheItemInterface $decorated, $cipher)
    {
        $this->decorated = $decorated;
        $this->cipher = $cipher;
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

    protected function getCipherMethod()
    {
        return $this->cipher;
    }

    protected function generateIv()
    {
        return openssl_random_pseudo_bytes(
            openssl_cipher_iv_length($this->cipher)
        );
    }

    protected function encryptString($string, $key, $iv)
    {
        return openssl_encrypt($string, $this->cipher, $key, 0, $iv);
    }

    protected function decryptString($string, $method, $key, $iv)
    {
        return openssl_decrypt($string, $method, $key, 0, $iv);
    }
}
