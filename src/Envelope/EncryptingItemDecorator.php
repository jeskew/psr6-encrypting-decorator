<?php
namespace Jsq\Cache\Envelope;

use Jsq\Cache\EncryptedValue as BaseEncryptedValue;
use Jsq\Cache\EncryptingItemDecorator as BaseItemDecorator;
use Jsq\Cache\InvalidArgumentException;
use Psr\Cache\CacheItemInterface;

class EncryptingItemDecorator extends BaseItemDecorator
{
    /** @var resource */
    private $publicKey;
    /** @var resource */
    private $privateKey;

    public function __construct(
        CacheItemInterface $decorated,
        $certificate,
        $key,
        $passPhrase,
        $cipher
    ) {
        parent::__construct($decorated, $cipher);
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

        return $data instanceof EncryptedValue
            && $this->validateSignature(
                $this->getKey() . $data->getCipherText(),
                $data->getSignature()
            );
    }

    protected function encrypt($data)
    {
        $key = $this->generateIv();
        $iv = $this->generateIv();
        $cipherText = $this->encryptString(serialize($data), $key, $iv);

        return new EncryptedValue(
            $cipherText,
            $this->getCipherMethod(),
            $iv,
            $this->encryptEnvelopeKey($key),
            $this->signString($this->getKey() . $cipherText)
        );
    }

    protected function decrypt(BaseEncryptedValue $data)
    {
        if (!$data instanceof EncryptedValue) return null;

        return unserialize($this->decryptString(
            $data->getCipherText(),
            $data->getMethod(),
            $this->decryptEnvelopeKey($data->getEnvelopeKey()),
            $data->getInitializationVector()
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

    private function signString($string)
    {
        openssl_sign($string, $signature, $this->privateKey);

        return $signature;
    }

    private function validateSignature($signed, $signature)
    {
        return openssl_verify($signed, $signature, $this->publicKey);
    }

    private function encryptEnvelopeKey($key)
    {
        openssl_public_encrypt($key, $sealedKey, $this->publicKey);

        return $sealedKey;
    }

    private function decryptEnvelopeKey($sealedKey)
    {
        openssl_private_decrypt($sealedKey, $key, $this->privateKey);

        return $key;
    }
}
