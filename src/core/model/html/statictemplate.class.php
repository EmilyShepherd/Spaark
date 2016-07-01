<?php namespace Spaark\Core\Model\HTML;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Represents a page template
 */
class StaticTemplate extends Templateable
{
    /**
     * The file extension for Templates is .htmt
     */
    protected $extension = '.htmt';

    /**
     * Parses the document, using the ContentHandler
     *
     * @param string $name The name of the box
     * @param array $replace Used as $varsIn
     */
    public function __construct($name, $staticVars = array( ))
    {
        $originalName = $name;
        $this->varsIn = $staticVars;
        $this->setName($name);

        while (true)
        {
            try
            {
                $content = $this->loadFromFile();
                break;
            }
            catch (NotFoundException $nfe)
            {
                if (dirname('/' . $this->getName()) == '/')
                {
                    throw new MissingTemplateException($originalName, $nfe);
                }
                else
                {
                    $this->setName
                    (
                          dirname
                          (
                              dirname('/' . $this->getName())
                          )
                        . '/template'
                    );
                }
            }
        }

        $this->init
        (
            str_replace
            (
                '{content}',
                '<content />',
                $content
            )
        );

        $output = explode('<content />', $this->html);

        $this->top    =
              $output[0]
            . '<div id="template_' . $this->name . '" class="spaark_template">';
        $this->bottom =
              '</div>'
            . $output[1];
    }

    /**
     * Returns the top parts of this template
     *
     * @return array The top parts of this template
     */
    public function getTop()
    {
        return $this->top;
    }

    /**
     * Returns the bottom parts of this template
     *
     * @return array The bottom parts of this template
     */
    public function getBottom()
    {
        return $this->bottom;
    }
}

