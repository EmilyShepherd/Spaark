<?php
/**
 * SystemException
 *
 * The main Exception class for the Framework
 */
class SystemException extends Exception
{
    /**
     * The relavent stack trace
     * The full stack trace
     */
    protected $trace;
    
    /**
     * The full message that describes the error
     */
    protected $debug_message;
    
    /**
     *
     */
    protected $full;
    
    /**
     *
     */
    private $spaark_classes = array
    (
        'Router', 'LoadedClass', 'Request'
    );
    
    /**
     * __construct
     *
     */
    public function __construct($debug_message, $message = 'An internal error occured', $previous = NULL)
    {
        parent::__construct($debug_message, 0);
        
        $this->debug_message = $debug_message;
    }
    
    public function getDebugMessage()
    {
        return $this->debug_message;
    }
    
    /**
     * Builds the stack trace, removing irrelvent information, to
     * make debugging easier
     */
    protected function genStackTrace()
    {
        $trace     = $this->getTrace();
        
        foreach ($trace as $i => $part)
        {
            if
            (
                (
                    isset($part['class'])                           &&
                    in_array($part['class'], $this->spaark_classes)
                )                                                   ||
                (
                    $part['function'] == 'call_user_func_array'     &&
                    isset($trace[$i + 1])                           &&
                    $trace[$i + 1]['class'] == 'LoadedClass'
                )
            )
            {
                unset($trace[$i]);
            }
            elseif ($part['function'] == 'Spaark')
            {
                unset($trace[$i - 1]);
            }
            else
            {
                if (!isset($part['file']))
                {
                    $part['file'] = 'Spaark';
                    $part['line'] = ' internal';
                }
                else
                {
                    $part['file'] = './' . $part['file'];
                }
                $trace[$i] = $part;
            }
        }
        
        $this->trace = array_values($trace);
    }
    
    /**
     * getStackTrace
     *
     * Returns the stack trace
     *
     * @return The stack trace
     */
    final public function getStackTrace()
    {
        $this->genStackTrace();
        $trace = $this->trace;
        $textTrace = array( );
        
        foreach ($trace as $part)
        {
            if ($part['function'] == '__construct')
            {
                $textTrace[] = $this->traceStep('new ' . $part['class'], $part);
            }
            else if ($part['function'] == '__clone')
            {
                $textTrace[] = $this->traceStep('clone ' . $part['class'], $part, false);
            }
            else
            {
                $textTrace[] = $this->traceStep
                (
                    (isset($part['class']) ? $part['class'].$part['type'] : '').$part['function'],
                    $part
                );
            }
        }
            
        return
              $this->drawDiv
              (
                    '<b>' . nl2br($this->debug_message) . '</b>'
                  . '<br />'
                  . '&nbsp;'
                  . '<span style="float: right;">'
                  .   $this->file . ':' . $this->line
                  . '</span>'
              )
              
            . '<br />'
            
            . '<b style="font-size: 18px;">'
            .   'Stack Trace:'
            . '</b>'
            
            . $this->drawDiv(implode('<hr />', $textTrace));
    }
    
    /**
     * getErrorMessage
     *
     * Returns the error message, in a box
     *
     * @return The error message, in a box
     */
    public function getErrorMessage()
    {
        return $this->drawDiv
        (
              '<center>'
            .   $this->message
            . '</center>'
        );
    }
    
    /**
     * drawDiv
     *
     * Returns the passed content, with a formatted
     * div box around it
     *
     * @param $content The HTML to put in the div
     * @return         The HTML
     */
    protected function drawDiv($content)
    {
         return
              '<div '
            .     'style="'
            .         'margin: 5px;'
            .         'background: white;'
            .         'border: 2px solid black;'
            .         'padding: 5px;'
            .     '"'
            . '>'
            .   $content
            . '</div>';
    }
    
    /**
     * traceStep
     *
     * Returns the HTML for a step in the stack trace
     *
     * @param $text The text to display
     * @param $part The trace step
     * @param $args If true, the arguments will be shown
     * @return      The HTML for that step in the trace
     */
    protected function traceStep($text, $part, $args = true)
    {
        if ($args && isset($part['args']))
        {
            foreach ($part['args'] as $j => $arg)
            {
                if (is_a($arg, 'BaseObj'))
                {
                    $part['args'][$j] = $arg->__toString();
                }
                else if (is_string($arg))
                {
                    $part['args'][$j] = '<font style="color:green;">"'.htmlspecialchars($arg).'"</font>';
                }
                else if (is_bool($arg))
                {
                    $part['args'][$j] = '<font style="color:purple;font-weight:bold;">' . ($arg ? 'true' : 'false') . '</font>';
                }
                else if ($arg === NULL)
                {
                    $part['args'][$j] = '<font style="color:purple;font-weight:bold;">NULL</font>';
                }
                else if (is_array($arg))
                {
                    //$part['args'][$j] = htmlspecialchars(var_export($arg, true));
                }
                else if (is_object($arg))
                {
                    $part['args'][$j] = get_class($arg);
                }
            }
            
            $text = '<b>'
                  .   $text . ' ('
                  . '</b> '
               // . implode(' <b>,</b> ', $part['args'])
                  . ' <b>)</b>';
        }
        else
        {
            $text = '<b>' . $text . '</b>';
        }
        
        return
              $text
            . '<br />'
            . '&nbsp;'
            . '<span style="float: right;">'
            .   $part['file'] . ':' . $part['line']
            . '</span>';
    }
}

?>