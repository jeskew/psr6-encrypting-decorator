<?php
namespace Jsq\Cache;

class PasswordEncryptedValueTest extends \PHPUnit_Framework_TestCase
{
    public function testCanSurviveSerialization()
    {
        $method = 'aes-256-cbc';
        $password = 'abc123';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
        $plaintext = 'The sparrow flies at midnight';
        $cipherText = openssl_encrypt($plaintext, $method, $password, 0, $iv);
        $mac = hash('sha256', $cipherText);
        $item = new PasswordEncryptedValue($cipherText, $method, $iv, $mac);

        $this->assertEquals($item, unserialize(serialize($item)));
    }
}
