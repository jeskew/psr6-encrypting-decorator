<?php
namespace Jsq\Cache;

use InvalidArgumentException as BaseIAE;
use Psr\Cache\InvalidArgumentException as CacheIAE;

class InvalidArgumentException extends BaseIAE implements CacheIAE {}
