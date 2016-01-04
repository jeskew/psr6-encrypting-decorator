<?php
namespace Jeskew\Cache;

trait CacheDataProviderTrait
{
    public function cacheableDataProvider()
    {
        return [
            [1],
            ['string'],
            [['key' => 'value']],
            [['one', 2, 3.0]],
            [new \ArrayObject()],
            [[
                'one' => str_repeat('x', 1024*1024),
                'two' => str_repeat('y', 1024*1024),
                'three' => str_repeat('z', 1024*1024),
            ]],
        ];
    }
}
