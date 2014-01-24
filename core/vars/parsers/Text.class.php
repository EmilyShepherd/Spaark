<?php

class Text
{
    private static $lowerCase = array
    (
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
        'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't',
        'u', 'v', 'w', 'x', 'y', 'z'
    );
    
    private static $upperCase = array
    (
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
        'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',
        'U', 'V', 'W', 'X', 'Y', 'Z'
    );
    
    private static $numbers = array
    (
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
    );
    
    private static $punctuation = array
    (
        ' ', ',', '.', '-', '(', ')', '&', '"', '\'',
        '/', '\\', '!', '£', '$', '%', '^', '*'
    );
    
    public static function word($text)
    {
        return self::checkMask
        (
            $text,
            array_merge(self::$lowerCase, self::$upperCase)
        );
    }
    
    public static function wordWithNumbers($text)
    {
        return self::checkMask
        (
            $text,
            array_merge(self::$lowerCase, self::$upperCase, self::$numbers)
        );
    }
    
    public static function texts($text)
    {
        return self::checkMask
        (
            $text,
            array_merge
            (
                self::$lowerCase, self::$upperCase,
                self::$numbers, self::$punctuation
            )
        );
    }
    
    private static function checkMask($text, $mask)
    {
        for ($i = 0; $i < strlen($text); $i++)
        {
            if (!in_array($text[$i], $mask))
            {
                return false;
            }
        }
        
        return true;
    }
}

?>