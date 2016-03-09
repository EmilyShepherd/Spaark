<?php namespace Spaark\Core;

use \Spaark\Core\Cache\Cache;
use \Spaark\Core\Cache\CacheMiss;
use \Spaark\Core\Vars\Vars;
use \Spaark\Core\View\URLParser;
use \Spaark\Core\Model\HTML\FormGetter;

const VALIDATOR = 0;
const REQUIRED  = 1;
const FROM      = 2;

class Form extends Vars implements \Spaark\Core\Cache\Cacheable
{
    private $vars = array( );

    private $dirty = false;

    private $output;

    public static function get($name)
    {
        try
        {
            return Cache::load($name);
        }
        catch (CacheMiss $cm)
        {
            $form = new FormGetter($name);
            return $form->getForm();
        }
    }

    public static function create($name, $action)
    {
        $form = new Form($name);

        Cache::save($name, $form, URLParser::normalizeURL($action));

        return $form;
    }

    public static function form()
    {
        if (empty(Vars::$saved[POST]))         return false;
        if (!isset(Vars::$saved[POST]['_f']))  return false;

        $form = self::get(Vars::$saved[POST]['_f']);

        $_POST = $form->validate(Vars::$saved[POST]);

        return true;
    }

    public static function getValidationJs($class)
    {
        $class  = new ReflectionClass($class);

        $method = $class->getMethod('validate');

        $tokens = token_get_all('<?'.implode('', array_slice
        (
            file($method->getFileName()),
            $method->getStartLine(),
            $method->getEndLine() - $method->getStartLine()
        )) . '?>');

        $code   = '';
        $vars   = array( );
        $params = array( );

        foreach ($tokens as $token)
        {
            if (!is_array($token))
            {
                $code .= $token == '.' ? '+' : $token;
            }
            elseif ($token[0] == T_CONCAT_EQUAL)
            {
                $code .= '+=';
            }
            elseif ($token[0] == T_VARIABLE)
            {
                $vars[substr($token[1], 1)] = true;
                $code .= substr($token[1], 1);
            }
            elseif (!in_array
            (
                $token[0],
                array
                (
                    T_OPEN_TAG, T_CLOSE_TAG, T_COMMENT,
                    T_DOC_COMMENT
                )
            ))
            {
                $code .= $token[1];
            }
        }

        $code = preg_replace
        (
            '/substr([\s\t\n\r]*?)\(([\s\t\n\r]*?)([a-zA-Z0-9]*)([\s\t\n\r]*?),/',
            '$3.substr(',
            $code
        );

        foreach ($method->getParameters() as $param)
        {
            if (isset($vars[$param->getName()]))
            {
                unset($vars[$param->getName()]);
            }

            $params[] = $param->getName();
        }

        if (!empty($vars))
        {
            $code = preg_replace
            (
                '/\{/',
                '{var ' . implode(',', array_keys($vars)) . ';',
                $code,
                1
            );
        }

        $code =
              'function(' . implode(',', $params) . ')'
            . $code;

        return $code;
    }

    public function __construct($name)
    {
        //
    }

    public function addInput($name, $validate, $required, $from)
    {
        $this->vars[$name] = array($validate, (bool)$required, $from);
        $this->dirty       = true;
    }

    public function validate($vars)
    {
        $output = array( );

        foreach ($this->vars as $name => $settings)
        {
            if (!isset($vars[$name]))
            {
                if ($settings[REQUIRED])
                {
                    throw new MissingRequiredField($name);
                }
                else
                {
                    continue;
                }
            }
            else
            {
                if (!$settings[VALIDATOR]::validate($vars[$name]))
                {
                    throw new SystemException('Failed to Validate: ' . $name);
                }

                if (isset($settings[FROM]))
                {
                    $class         = $settings[VALIDATOR];
                    $from          = 'from' . $settings[FROM];
                    $output[$name] = $class::$from($vars[$name]);
                }
                else
                {
                    $output[$name] = new $settings[VALIDATOR]($vars[$name]);
                }

                unset($vars[$name]);
            }
        }

        return $output + $vars;
    }

    public function serialize()
    {
        return serialize($this->vars);
    }

    public function unserialize($str)
    {
        $this->vars = unserialize($str);
    }

    public function valid()
    {
        return true;
    }

    public function dirty()
    {
        return $this->dirty;
    }
}


?>