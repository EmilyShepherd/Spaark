<?php namespace Spaark\Core\Output;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */

use \Spaark\Core\Instance;
use \Spaark\Core\Output;
use \Spaark\Core\View\Page;

/**
 * Handles Spaark outputs, for example the Spaark javascript, logo and
 * licence information.
 */
class SpaarkOutput extends \Spaark\Core\Base\Controller
{
    /**
     * All .spaark requests go through here, the appropriate ones are
     * routed to the appropriate functions.
     *
     * + js.spaark      - Spaark's javascript file
     * + logo.spaark    - Spaark's logo
     * + licence.spaark - This instance's licence information
     *
     * @see SpaarkOutput::js()
     * @see SpaarkOutput::logo()
     * @see SpaarkOutput::licence()
     */
    public function router()
    {
        $uri     = strtolower(Instance::getRequest());
        $uri     = substr($uri, 1, strlen($uri) - 8);
        $methods = array
        (
            'js', 'logo', 'licence' , 'about'
        );

        if (in_array($uri, $methods))
        {
            $this->$uri();
        }
        else
        {
            throw new \Exception
            (
                '.spaark files are reserved for Spaark'
            );
        }
    }

    /**
     * Joins every javascript file in the SPAARK_PATH/default/js/
     * directory and outputs it with an almost 10 year max-age
     *
     * @response text/javascript
     */
    private function js()
    {
        //header('Cache-Control: public, max-age=320000000');
        Output::mime('text/javascript');
        Output::ttl(0);

        $path  = SPAARK_PATH . '/default/js/';
        $files = glob($path . '*.js');

        $js = file_get_contents($path . 'js.js');

        foreach ($files as $file)
        {
            if ($file != $path . 'js.js')
            {
                $js .= file_get_contents($file);
            }
        }

        $js = new \Spaark\Core\View\JavaScript($js);

        echo $js;
    }

    /**
     * Outputs Spaark's logo (stored at
     * SPAARK_PATH/default/images/logo.png)
     *
     * @response image/png
     */
    private function logo()
    {
        Output::mime('image/png');
        Output::ttl(0);

        echo file_get_contents(SPAARK_PATH . '/default/images/logo.png');
    }

    /**
     * Outputs this instance's licence information
     */
    private function licence()
    {
        $licence = file_get_contents(SPAARK_PATH . '/licence.txt');

        Page::load
        (
            'spaark_licence',
            array
            (
                'licence'   => $licence
            )
        );
    }

    private function about()
    {
        Page::load('spaark_about');
    }
}

?>
