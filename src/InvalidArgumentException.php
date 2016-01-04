<?php
namespace Jsq\CacheEncryption;

use InvalidArgumentException as BaseIAE;
use Psr\Cache\InvalidArgumentException as CacheIAE;

class InvalidArgumentException extends BaseIAE implements CacheIAE {}
