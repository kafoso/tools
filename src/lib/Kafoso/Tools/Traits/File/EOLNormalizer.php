<?php
namespace Kafoso\Tools\Traits\File;

trait EOLNormalizer
{
    /**
     * @param string $str
     * @return string
     */
    public function normalizeEOL($str)
    {
        return preg_replace('/\r\n|\r|\n/', "\r\n", $str);
    }
}
