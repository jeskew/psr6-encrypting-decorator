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

        return is_array($data)
            && $this->arrayHasKeys($data, [
                'encrypted',
                'key',
                'iv',
                'cipher',
                'signature',
            ])
            && openssl_verify(
                $this->getKey() . $data['encrypted'],
                base64_decode($data['signature']),
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

        return [
            'encrypted' => $cipherText,
            'cipher' => $this->cipher,
            'iv' => base64_encode($iv),
            'signature' => base64_encode($signature),
            'key' => base64_encode($sealedKey),
        ];
    }

    protected function decrypt($data)
    {
        openssl_private_decrypt(
            base64_decode($data['key']),
            $key,
            $this->privateKey
        );

        return unserialize(openssl_decrypt(
            $data['encrypted'],
            $data['cipher'],
            $key,
            0,
            base64_decode($data['iv'])
        ));
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
