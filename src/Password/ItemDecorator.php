<?php
namespace Jsq\CacheEncryption\Password;

use Jsq\CacheEncryption\ItemDecorator as BaseItemDecorator;
use Jsq\CacheEncryption\OpenSslEncryptionTrait;
use Psr\Cache\CacheItemInterface;

class ItemDecorator extends BaseItemDecorator
{
    use OpenSslEncryptionTrait;

    /** @var string */
    private $password;

    public function __construct(CacheItemInterface $decorated, $pass, $cipher)
    {
        parent::__construct($decorated);
        $this->cipher = $cipher;
        $this->password = $pass;
    }

    protected function isDecryptable()
    {
        $data = $this->getDecorated()->get();

        return $data instanceof EncryptedValue
            && $data->getMac() === $this->authenticate(
                $this->getKey(),
                $data->getCipherText()
            );
    }

    protected function encrypt($data)
    {
        $iv = $this->generateIv();
        $encrypted = $this->encryptString(serialize($data), $this->password, $iv);

        return new EncryptedValue(
            $encrypted,
            $this->getCipherMethod(),
            $iv,
            $this->authenticate($this->getKey(), $encrypted)
        );
    }

    protected function decrypt($data)
    {
        if (!$data instanceof EncryptedValue) return null;

        return unserialize($this->decryptString(
            $data->getCipherText(),
            $data->getMethod(),
            $this->password,
            $data->getInitializationVector()
        ));
    }

    private function authenticate($key, $cipherText)
    {
        return $this->hmac($cipherText, $this->hmac($key, $this->password));
    }

    private function hmac($data, $key)
    {
        return hash_hmac('sha256', $data, $key);
    }
}
