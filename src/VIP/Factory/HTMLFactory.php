<?php

namespace VIP\Factory;

use VIP\Component\HTMLObject;
use VIP\Utilities\ArrayHelper;
use VIP\Utilities\StringHelper;

class HTMLFactory implements FactoryInterface
{
    public static function create(string $string_input, array $array_input = []): HTMLObject
    {
        $tag_att_split = explode(" ", $string_input, 2);
        $tag = $tag_att_split[0];
        $attributes = [];

        if (isset($tag_att_split[1])) {
            $attributes_split = StringHelper::quotedExplode($tag_att_split[1]);

            foreach ($attributes_split as $attribute) {
                $key_value_split = StringHelper::multiExplode(["='", "=\""], $attribute);
                $key = $key_value_split[0];

                if (isset($key_value_split[1])) {
                    $value = $key_value_split[1];
                } else {
                    $value = NULL;
                }

                if (isset($value)) {
                    if ($value[strlen($value) - 1] == "\"" || $value[strlen($value) - 1] == "'") {
                        $value = substr($value, 0, -1);
                    } else {
                        (ResponseFactory::createError("Internal Server Error: Invalid key value. Check your view files.", 500))->handle();
                    }
                }

                $attributes["$key"] = $value;
            }
        }

        $attributes = ArrayHelper::merge($array_input, $attributes);
        ksort($attributes);
        return new HTMLObject($tag, $attributes);
    }
}
