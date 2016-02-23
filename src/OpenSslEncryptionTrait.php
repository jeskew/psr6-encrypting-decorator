<?php
namespace Jsq\CacheEncryption;

use Psr\Cache\CacheItemInterface;

trait OpenSslEncryptionTrait
{
    /** @var string */
    private $cipher;
    /** @var mixed */
    private $decrypted;

    public function get()
    {
        if (empty($this->decrypted) && $this->isHit()) {
            $this->decrypted = $this->decrypt($this->getDecorated()->get());
        }

        return $this->decrypted;
    }

    /**
     * @return bool
     */
    abstract public function isHit();

    /**
     * @return CacheItemInterface
     */
    abstract public function getDecorated();

    abstract protected function encrypt($data);

    abstract protected function decrypt($data);

    private function generateIv()
    {
        return openssl_random_pseudo_bytes(
            openssl_cipher_iv_length($this->cipher)
        );
    }

    private function encryptString($string, $key, $iv)
    {
        return openssl_encrypt($string, $this->cipher, $key, 0, $iv);
    }

    private function decryptString($string, $method, $key, $iv)
    {
        return openssl_decrypt($string, $method, $key, 0, $iv);
    }

    private function getCipherMethod()
    {
        return $this->cipher;
    }
}
