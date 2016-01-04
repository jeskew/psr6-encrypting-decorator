<?php
namespace Jeskew\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class PasswordEncryptingPoolDecorator extends EncryptingPoolDecorator
{
    /** @var string */
    private $password;
    /** @var string */
    private $cipher;

    public function __construct(
        CacheItemPoolInterface $decorated,
        $password,
        $cipher = 'aes-256-cbc'
    ) {
        parent::__construct($decorated);
        $this->password = $password;
        $this->cipher = $cipher;
    }

    protected function decorate(CacheItemInterface $item)
    {
        return new PasswordEncryptingItemDecorator(
            $item,
            $this->password,
            $this->cipher
        );
    }
}
