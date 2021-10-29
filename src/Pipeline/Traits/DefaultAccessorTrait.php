<?php 

namespace Pipeline\Traits;

trait DefaultAccessorTrait 
{
    public function tryGet(&$var, $default = null)
    {
        return (isset($var) ? $var : $default);
    }

    public static function staticTryGet(&$var, $default = null)
    {
        return (isset($var) ? $var : $default);
    }
}