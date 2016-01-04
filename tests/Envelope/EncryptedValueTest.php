<?php
namespace Jsq\CacheEncryption\Envelope;

use Jsq\CacheEncryption\PkiUtils;

class EncryptedValueTest extends \PHPUnit_Framework_TestCase
{
    use PkiUtils;

    public function testCanSurviveSerialization()
    {
        $privateKey = openssl_get_privatekey(self::getKey());
        $publicKey = openssl_get_publickey(self::getCertificate());

        $method = 'aes-256-cbc';
        $password = 'abc123';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
        $plaintext = 'The sparrow flies at midnight';
        $cipherText = openssl_encrypt($plaintext, $method, $password, 0, $iv);

        openssl_sign($cipherText, $signature, $privateKey);
        openssl_public_encrypt($password, $sealedKey, $publicKey);

        $item = new EncryptedValue($cipherText, $method, $iv, $sealedKey, $signature);

        $this->assertEquals($item, unserialize(serialize($item)));
    }
}
