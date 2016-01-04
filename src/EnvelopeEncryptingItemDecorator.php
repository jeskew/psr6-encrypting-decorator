<?php
namespace Jeskew\Cache;

use Psr\Cache\CacheItemInterface;

class EnvelopeEncryptingItemDecorator extends EncryptingItemDecorator
{
    private $publicKey;
    private $privateKey;
    private $cipher;

    public function __construct(
        CacheItemInterface $decorated,
        $certificate,
        $key,
        $passPhrase,
        $cipher
    ) {
        parent::__construct($decorated);
        $this->cipher = $cipher;
        $this->setPublicKey($certificate);
        $this->setPrivateKey($key, $passPhrase);
    }

    public function __destruct()
    {
        openssl_pkey_free($this->publicKey);
        openssl_pkey_free($this->privateKey);
    }

    protected function isDecryptable()
    {
        $data = $this->getDecorated()->get();

        return $data instanceof EnvelopeEncryptedValue
            && openssl_verify(
                $this->getKey() . $data->getCipherText(),
                $data->getSignature(),
                $this->publicKey
            );
    }

    protected function encrypt($data)
    {
        $key = openssl_random_pseudo_bytes(
            openssl_cipher_iv_length($this->cipher)
        );
        $iv = openssl_random_pseudo_bytes(
            openssl_cipher_iv_length($this->cipher)
        );
        $cipherText = openssl_encrypt(
            serialize($data),
            $this->cipher,
            $key,
            0,
            $iv
        );

        openssl_sign(
            $this->getKey() . $cipherText,
            $signature,
            $this->privateKey
        );
        openssl_public_encrypt($key, $sealedKey, $this->publicKey);

        return new EnvelopeEncryptedValue(
            $cipherText,
            $this->cipher,
            $iv,
            $sealedKey,
            $signature
        );
    }

    protected function decrypt(EncryptedValue $data)
    {
        if ($data instanceof EnvelopeEncryptedValue) {
            openssl_private_decrypt($data->getEnvelopeKey(), $key, $this->privateKey);

            return unserialize(openssl_decrypt(
                $data->getCipherText(),
                $data->getMethod(),
                $key,
                0,
                $data->getInitializationVector()
            ));
        }

        return null;
    }

    private function setPublicKey($cert)
    {
        $publicKey = @openssl_pkey_get_public($cert);
        if (!$this->validateOpenSslKey($publicKey)) {
            throw new InvalidArgumentException('Unable to create public key'
                . ' from provided certificate. Certificate must be a valid x509'
                . ' certificate, a PEM encoded certificate, or a path to a file'
                . ' containing a PEM encoded certificate.');
        }

        $this->publicKey = $publicKey;
    }

    private function setPrivateKey($key, $passPhrase)
    {
        $this->privateKey = @openssl_pkey_get_private($key, $passPhrase);
        if (!$this->validateOpenSslKey($this->privateKey)) {
            throw new InvalidArgumentException('Unable to create private key'
                . ' from provided key. Key must be a PEM encoded private key or'
                . ' a path to a file containing a PEM encoded private key.');
        }
    }

    private function validateOpenSslKey($key)
    {
        return is_resource($key) && 'OpenSSL key' === get_resource_type($key);
    }
}
