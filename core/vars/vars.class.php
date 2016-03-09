<?php namespace Spaark\Core\Vars;

define('POST',   0);
define('GET',    1);
define('COOKIE', 2);
define('SERVER', 3);

class Vars extends \Spaark\Core\Base\StaticClass
{
    protected static $initialized = false;

    protected static $saved = array( );

    public static function init()
    {
        self::grabVar(GET,    '_GET');
        self::grabVar(POST,   '_POST');
        self::grabVar(COOKIE, '_COOKIE');
        self::grabVar(SERVER, '_SERVER');
    }

    private static function grabVar($varName, $var)
    {
        self::$saved[$varName] = $GLOBALS[$var];
        $GLOBALS[$varName]     = array( );
    }

    public static function checkFlag($type, $name)
    {
        return isset(self::$saved[$type][$name]);
    }

    public static function checkValue($type, $name, $value)
    {
        return
            self::checkFlag($type, $name) &&
            self::$saved[$type][$name] == $value;
    }

    public static function sanitize($val, $checker)
    {
        $class = Main::getClassLoader()->load($checker);

        if (!call_user_func($class['class'] . '::' . $class['method'], $val))
        {
            throw new InvalidInputException();
        }
    }

    public static function getValue($type, $name, $checker)
    {
        $val = self::$saved[$type][$name];

        //self::sanitize($val, $checker);

        return $val;
    }

    public static function box($type, $name, $box)
    {
        if (!class_exists($box))
        {
            $path = conf('CONTROLLER_PATH') . $box . '.class.php';
            if (file_exists($path))
            {
                require_once $path;
            }
            else
            {
                $path = FRAMEWORK . 'Vars/boxes/' . $box . '.class.php';
                if (file_exists($path))
                {
                    require_once $path;
                }
                else
                {
                    throw new SystemException('No such box: ' . $box);
                }
            }
        }

        $value =
            self::checkFlag($type, $name)
              ? self::$saved[$type][$name]
              : '';

        return new $box($value);
    }
}

?>