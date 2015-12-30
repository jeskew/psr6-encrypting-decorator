<?php
namespace Jeskew\Cache;

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

        return is_array($data)
            && $this->arrayHasKeys($data, ['encrypted', 'iv', 'cipher', 'mac'])
            && $data['cipher'] === $this->cipher
            && $data['mac'] === $this->authenticate(
                $this->getKey(),
                $data['encrypted']
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

        return [
            'cipher' => $this->cipher,
            'iv' => base64_encode($iv),
            'mac' => $this->authenticate($this->getKey(), $encrypted),
            'encrypted' => $encrypted,
        ];
    }

    protected function decrypt($data)
    {
        return unserialize(openssl_decrypt(
            $data['encrypted'],
            $this->cipher,
            $this->password,
            0,
            base64_decode($data['iv'])
        ));
    }

    protected function authenticate($key, $cipherText)
    {
        return hash_hmac(
            'sha256',
            $cipherText,
            hash_hmac('sha256', $key, $this->password)
        );
    }
}
