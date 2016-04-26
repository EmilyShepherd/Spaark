<?php namespace Spaark\Core;

use Spaark\Core\Util\Encoding\JSON;

class Config extends Model\Base\Entity
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

    public function __fromId($id)
    {
        $json = new JSON();
        $this->loadArray
        (
            \tree_merge_recursive
            (
                $json->parseFile(ROOT . '/' . $id, false),
                $json->parseFile(SPAARK_PATH . '/default/config'),
                array('app' => array('root' => ROOT))
            )
        );
    }
}
