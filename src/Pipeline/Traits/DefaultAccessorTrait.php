<?php 

namespace Pipeline\Traits;

trait DefaultAccessorTrait 
{
    public function tryGet(&$var, $default = NULL)
    {
        return (isset($var) ? $var : $default);
    }

    public static function staticTryGet(&$var, $default = NULL)
    {
        return (isset($var) ? $var : $default);
    }
}