<?php

namespace ITechnoD\ModelFieldsHelper\Helpers;

class StringHelper
{
    /**
     * @param $underscored
     * @param false $capitalizeFirst
     * @return string|string[]|null
     */
    public static function toCamelCase($underscored, $capitalizeFirst = false)
    {
        $res = preg_replace_callback("|.*(_.).*|", "self::uppercase", $underscored);
        $res = preg_replace_callback("|.*(_.).*|", "self::uppercase", $res);

        if ($capitalizeFirst) {
            $res = strToUpper(substr($res, 0, 1)) . substr($res, 1);
        }

        return $res;
    }

    /**
     * @param $matches
     * @return mixed
     */
    public static function uppercase($matches)
    {
        for ($i = 1; $i < count($matches); $i++) {
            $matches[0] = str_replace($matches[$i], strtoupper(substr($matches[$i], 1)), $matches[0]);
        }

        return $matches[0];
    }
}
