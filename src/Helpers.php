<?php

namespace Clrkz;

class Helpers
{
    static function getValueIndex(string $value = null, array $array)
    {
        if (empty($value)) {
            return false;
        }

        if (in_array($value, $array)) {
            return array_search($value, $array);
        }

        // In depth search
        $index = false;
        foreach ($array as $key => $val) {
            $sub_array = array_map('trim', explode(',', $val));
            if (count($sub_array) > 1) {
                if (in_array($value, $sub_array)) {
                    $index = $key;
                    break;
                }
            }
        }
        return $index;
    }

    static function key(string $string)
    {
        $characters_to_replace = array(",", " ");
        return strtolower(str_replace($characters_to_replace, "_", $string));
    }
}
