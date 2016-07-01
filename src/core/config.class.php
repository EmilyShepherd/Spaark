<?php namespace Spaark\Core;

use Spaark\Core\Util\Encoding\JSON;

class Config extends Model\Config
{
    /**
     * The Namespace
     *
     * @var string
     * @readable
     */
    private $namespace = '\\';

    /**
     * The Admin's name
     *
     * @var string
     * @readable
     */
    private $admin;

    /**
     * Default Timezone
     *
     * @var string
     * @readable
     */
    private $timezone = 'Europe/London';

    /**
     * Application Name
     *
     * @var var string
     * @readable
     */
    private $name = 'Untitled Application';

    /**
     * The root
     *
     * @var string
     * @readable
     */
    private $hrefRoot = '/';

    /**
     * The Path to the configuration file
     *
     * @var string
     * @readable
     */
    private $configPath = 'config/';

    /**
     * The root of the system
     *
     * @var string
     * @readable
     */

    public function __fromId($id)
    {
        $json       = new JSON();
        $this->root = ROOT;
        $this->loadArray($json->parseFile(ROOT . '/' . $id, false));

        $this->app  = $this;
    }
}
