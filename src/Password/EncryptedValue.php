<?php
namespace Jsq\CacheEncryption\Password;

use Jsq\CacheEncryption\EncryptedValue as BaseEncryptedValue;

class EncryptedValue extends BaseEncryptedValue
{
    /** @var string */
    private $mac;

    /**
     * @param string $cipherText
     * @param string $method
     * @param string $iv
     * @param string $mac
     */
    public function __construct($cipherText, $method, $iv, $mac)
    {
        parent::__construct($cipherText, $method, $iv);
        $this->mac = $mac;
    }

    public function jsonSerialize()
    {
        return parent::jsonSerialize() + ['mac' => $this->mac];
    }

    public function unserialize($serialized)
    {
        parent::unserialize($serialized);

        $data = json_decode($serialized);
        $this->mac = $data->mac;
    }

    /**
     * @return string
     */
    public function getMac()
    {
        return $this->mac;
    }
}
