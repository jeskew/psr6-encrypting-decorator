<?php
namespace Jsq\CacheEncryption\Iron;

use Jsq\CacheEncryption\ItemDecorator as BaseItemDecorator;
use Jsq\Iron\Iron;
use Jsq\Iron\PasswordInterface;
use Jsq\Iron\Token;
use Psr\Cache\CacheItemInterface;

class ItemDecorator extends BaseItemDecorator
{
    /** @var PasswordInterface */
    private $password;
    /** @var Iron */
    private $iron;
    /** @var Token|null */
    private $token;

    public function __construct(
        CacheItemInterface $decorated,
        PasswordInterface $password,
        $cipher
    ) {
        parent::__construct($decorated);
        $this->password = $password;
        $this->iron = new Iron($cipher);
    }

    public function get()
    {
        $this->setTokenFromDecorated();

        return isset($this->token)
            ? $this->iron->decryptToken($this->token, $this->password)
            : null;
    }

    protected function isDecryptable()
    {
        $this->setTokenFromDecorated();

        return isset($this->token);
    }

    protected function encrypt($data)
    {
        return (string) $this->iron->encrypt($this->password, $data);
    }

    private function setTokenFromDecorated()
    {
        if (empty($this->token)) {
            try {
                $this->token = Token::fromSealed(
                    $this->password,
                    $this->getDecorated()->get()
                );
            } catch (\Exception $e) {
                // not a valid token!
            }
        }
    }
}
