<?php
namespace Jsq\CacheEncryption\Iron;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Iron\Iron;
use Iron\PasswordInterface;
use Iron\Token;
use Psr\Cache\CacheItemInterface;
use Throwable;

class ItemDecorator implements CacheItemInterface
{
    /** @var PasswordInterface */
    private $password;
    /** @var Iron */
    private $iron;
    /** @var CacheItemInterface */
    private $decorated;
    /** @var mixed */
    private $plaintext;
    /** @var Token|null */
    private $token;
    /** @var int|null */
    private $expiration;

    public function __construct(
        CacheItemInterface $decorated,
        PasswordInterface $password,
        $cipher
    ) {
        if (!class_exists(Iron::class)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('You must install'
                . ' jsq/iron-php to use the Iron decorator.');
            // @codeCoverageIgnoreEnd
        }

        $this->decorated = $decorated;
        $this->password = $password;
        $this->iron = new Iron($cipher);
    }

    public function get()
    {
        if ($this->plaintext) {
            return $this->plaintext;
        }
        
        if ($this->tryToSetTokenFromDecorated()) {
            return json_decode(
                $this->iron->decryptToken($this->token, $this->password),
                true
            );
        }
    }

    public function getKey()
    {
        return $this->decorated->getKey();
    }

    public function isHit()
    {
        return $this->tryToSetTokenFromDecorated();
    }

    public function expiresAfter($time)
    {
        if ($time instanceof DateInterval) {
            $this->expiration = (new DateTime)->add($time)->getTimestamp();
        } elseif (is_int($time)) {
            $this->expiration = time() + $time;
        }
        
        $this->decorated->expiresAfter($time);
        
        return $this;
    }

    public function expiresAt($expiration)
    {
        if ($expiration instanceof DateTimeInterface) {
            $this->expiration = $expiration->getTimestamp();
        }
        
        $this->decorated->expiresAt($expiration);
        
        return $this;
    }
    
    public function set($value)
    {
        $this->plaintext = $value;   
        return $this;
    }

    public function getDecorated()
    {
        return $this->decorated;
    }

    public function finalize()
    {
        $this->decorated->set((string) $this->iron->encrypt(
            $this->password,
            json_encode($this->plaintext),
            $this->expiration ? time() - $this->expiration : 0
        ));
    }

    /**
     * @return bool
     */
    private function tryToSetTokenFromDecorated()
    {
        if (empty($this->token)) {
            try {
                $this->token = Token::fromSealed(
                    $this->password,
                    $this->decorated->get()
                );
            } catch (Throwable $t) {}
        }

        return isset($this->token);
    }
}
