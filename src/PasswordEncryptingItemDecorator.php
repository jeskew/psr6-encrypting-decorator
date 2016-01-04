<?php
namespace Jsq\Cache;

use Psr\Cache\CacheItemInterface;

class PasswordEncryptingItemDecorator extends EncryptingItemDecorator
{
    /** @var string */
    private $password;
    /** @var string */
    private $cipher;

    public function __construct(CacheItemInterface $decorated, $pass, $cipher)
    {
        parent::__construct($decorated);
        $this->password = $pass;
        $this->cipher = $cipher;
    }

    protected function isDecryptable()
    {
        $data = $this->getDecorated()->get();

        return $data instanceof PasswordEncryptedValue
            && $data->getMac() === $this->authenticate(
                $this->getKey(),
                $data->getCipherText()
            );
    }

    protected function encrypt($data)
    {
        $iv = openssl_random_pseudo_bytes(
            openssl_cipher_iv_length($this->cipher)
        );
        $encrypted = openssl_encrypt(
            serialize($data),
            $this->cipher,
            $this->password,
            0,
            $iv
        );

        return new PasswordEncryptedValue(
            $encrypted,
            $this->cipher,
            $iv,
            $this->authenticate($this->getKey(), $encrypted)
        );
    }

    protected function decrypt(EncryptedValue $data)
    {
        return unserialize(openssl_decrypt(
            $data->getCipherText(),
            $data->getMethod(),
            $this->password,
            0,
            $data->getInitializationVector()
        ));
    }

    private function authenticate($key, $cipherText)
    {
        return hash_hmac(
            'sha256',
            $cipherText,
            hash_hmac('sha256', $key, $this->password)
        );
    }
}
