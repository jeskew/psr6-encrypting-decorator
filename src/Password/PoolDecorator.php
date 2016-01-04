<?php
namespace Jsq\CacheEncryption\Password;

use Jsq\CacheEncryption\PoolDecorator as BasePoolDecorator;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class PoolDecorator extends BasePoolDecorator
{
    /** @var string */
    private $password;
    /** @var string */
    private $cipher;

    /**
     * @param CacheItemPoolInterface $decorated
     * @param string $password
     * @param string $cipher
     */
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
        return new ItemDecorator(
            $item,
            $this->password,
            $this->cipher
        );
    }
}
