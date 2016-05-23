<?php

namespace Centaur\Middleware;

use Lang;

trait TranslationHelper
{
    /**
     * Helper method for facilitating string translation
     * @param  string $key
     * @param  string $message
     * @return string
     */
    protected function translate($key, $message)
    {
        $key = 'centaur.' . $key;

        if (Lang::has($key)) {
            $message = trans($key);
        }

        return $message;
    }
}
