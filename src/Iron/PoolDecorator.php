<?php
namespace Jsq\CacheEncryption\Iron;

use Jsq\CacheEncryption\PoolDecorator as BasePoolDecorator;
use Jsq\Iron;
use Jsq\Iron\PasswordInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class PoolDecorator extends BasePoolDecorator
{
    /** @var PasswordInterface */
    private $password;
    /** @var string */
    private $cipher;

    public function __construct(
        CacheItemPoolInterface $decorated,
        $password,
        $cipher = Iron\Iron::DEFAULT_ENCRYPTION_METHOD
    ) {
        parent::__construct($decorated);
        $this->password = Iron\normalize_password($password);
        $this->cipher = $cipher;
    }

    protected function decorate(CacheItemInterface $inner)
    {
        return new ItemDecorator(
            $inner,
            $this->password,
            $this->cipher
        );
    }
}
