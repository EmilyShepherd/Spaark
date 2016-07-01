<?php namespace Spaark\Core\Model\HTML;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Represents a box
 */
class Box extends Fragment
{
    /**
     * The file extension for Boxes is .htmb
     */
    protected $extension = '.htmb';

    protected $output;

    /**
     * Parses the document, using the ContentHandler
     *
     * @param string $name The name of the box
     * @param array $replace Used as $varsIn
     */
    public function __construct($name, $replace = array( ))
    {
        $this->setName($name);

        $this->buildHTML($this->loadFromFile());

        $this->response  = $replace;
        $this->output    = explode('{content}', $this->html);
        $this->output[0] = $this->replaceVars($this->output[0]);
        $this->output[1] = $this->replaceVars($this->output[1]);
    }

    /**
     * Returns the top part of this box
     *
     * @return string The top part of this box
     */
    public function getTop()
    {
        return $this->output[0];
    }

    /**
     * Returns the bottom part of this box
     *
     * @return string The bottom part of this box
     */
    public function getBottom()
    {
        return $this->output[1];
    }

    /**
     * Runs a part of the output array
     *
     * @param int $i The index of the array to run (0 or 1)
     * @return string The part of the box
     */
    private function run($i)
    {
        extract($this->varsIn);

        ob_clean();
        eval('?>' . $this->output[$i]);

        return ob_get_contents();
    }
}

