<?php namespace Spaark\Core\View;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Represents an output format
 */
abstract class OutputType extends \Spaark\Core\Model\Base\Model
{
    /**
     * The raw input
     */
    protected $raw;
    
    /**
     * The output. This will be compressed / improved for output
     */
    protected $output;
    
    /**
     * Abstract function to remove comments from $this->output. Needs to
     * be told how comments start and end
     *
     * @param string $start How a comment starts (eg "<!--")
     * @param string $end How a comment ends (eg "-->")
     */
    protected function removeComments($start, $end)
    {
        $this->output = preg_replace
        (
            '/' . $start . '(.*?)' . $end . '/si',
            '',
            $this->output
        );
    }
    
    /**
     * Returns the header, surrounded by whatever comment tags this
     * document requires
     *
     * @param string $start How to start the comment header
     * @param string $end How to end the comment header
     * @param string $ls How to start each line of the header
     * @param string $le How to end each line of the header
     */
    public static function getHeader($start, $end, $ls=NULL, $le=NULL)
    {
        return
              $start
            . ' Powered By Spaark | (c) SWEEB 2014 | sweeb.net '
            . $end
            . "\r\n";

        return
              $start . '*'
            . implode
              (
                  '*' . $le . "\r\n" . $ls . '*',
                  array
                  (
                      '***************************************',
                      '           Powered By Spaark           ',
                      '                                       ',
                      ' Copyright (C) 2012 Emily Shepherd ',
                      '     emily@emilyshepherd.me      ',
                      '             www.sweeb.net             ',
                      '***************************************'
                  )
              )
            . '*' . $end . "\r\n";
    }
    
    /**
     * Adds the header to the output
     *
     * @param string $start How to start the comment header
     * @param string $end How to end the comment header
     * @param string $ls How to start each line of the header
     * @param string $le How to end each line of the header
     * @see getHeader()
     */
    protected function addHeader($start, $end, $ls, $le)
    {
        $this->output =
              self::getHeader($start, $end, $ls, $le)
            . $this->output;
    }
    
    /**
     * Returns the output of this OutputType
     *
     * @return string The output of this OutputType
     */
    public function __toString()
    {
        return $this->output;
    }
}

?>