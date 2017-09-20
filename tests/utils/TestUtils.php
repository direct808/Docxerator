<?php

namespace Direct808\Docxerator;


class TestUtils
{
    static function generateTextMapFromString($string, $splitCount)
    {
        $textMap = [];
        $splitIndexes = [];
        $len = mb_strlen($string);
        echo $len . PHP_EOL;

        while (true) {
            $splitIndexes[] = rand(1, $len - 1);
            $splitIndexes = array_unique($splitIndexes);
            if (count($splitIndexes) == $splitCount - 1)
                break;
        }
        asort($splitIndexes);

        $prev = 0;
        foreach ($splitIndexes as $splitIndex) {
            $textMap[] = mb_substr($string, $prev, $splitIndex - $prev);
            $prev = $splitIndex;
        }
        $textMap[] = mb_substr($string, $splitIndex);

        print_r($splitIndexes);
        print_r($textMap);
//        echo $string;

    }

    static function generateTextMapFromArray($array)
    {
        $textMap = [];
        $len = 0;
        foreach ($array as $item) {
            $xml = simplexml_load_string('<t></t>');
            $xml[0] = $item;
            $textMap[$len] = $xml;
            $len += mb_strlen($item);
        }
        return $textMap;
    }

    static function convertTextMapToArray($textMap)
    {
        $result = [];

        foreach ($textMap as $item) {
            $result[] = $item . '';
        }
        return $result;
    }
}