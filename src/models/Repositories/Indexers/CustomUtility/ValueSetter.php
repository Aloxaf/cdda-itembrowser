<?php

namespace CustomUtility;

class ValueUtil
{
    public static function SetDefault($object, $key, $value)
    {
        if (!isset($object->{$key})) {
            $object->{$key} = $value;
        }
    }
}
