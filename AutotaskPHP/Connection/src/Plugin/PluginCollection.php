<?php

namespace AutotaskPHP\Connection\Plugin;

use AutotaskPHP\Connection\Exceptions\InvalidPlugin;
use Http\Client\Common\Plugin;
use Illuminate\Support\Collection;

class PluginCollection extends Collection
{
    public function offsetGet($key): Plugin
    {
        return parent::offsetGet($key);
    }

    public function offsetSet($key, $value)
    {
        if (! ($value instanceof Plugin)) {
            throw new InvalidPlugin(
                sprintf(
                'Expecting instance of [%s] received [%s].',
                Plugin::class,
                    gettype($value)
                )
            );
        }

        parent::offsetSet($key, $value);
    }
}