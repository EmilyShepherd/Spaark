<?php namespace Spaark\Core\Model\Sources;


class FileSource extends BaseSource
{
    protected function _create($data)
    {
        $encoding    = static::load($this->config->encoding);
        $encodingObj = new $encoding();

        file_put_contents($this->config->path, $encodingObj->encode($data));
    }

    protected function _count()
    {
        $this->read();
        return $this->_countRes();
    }

    protected function _countRes()
    {
        return count($this->res);
    }

    protected function _delete()
    {
        if (!$this->hasResult())
        {
            $this->read();
        }
    }

    protected function _get($pos)
    {
        return $this->res[$pos];
    }

    protected function _read()
    {
        $encoding    = static::load($this->config->encoding);
        $encodingObj = new $encoding();
        $result      =
            $encodingObj->decode(file_get_contents($this->config->path));

        foreach ($result as $key => $value)
        {

        }
    }
}

namespace Spaark\Core\Model\Sources\FileSource;

class Config extends Spaark\Core\Model\Config
{
    /**
     *
     * @type Path
     * @readable
     * @writable
     */
    protected $path;

    /**
     *
     * @type ClassName
     * @readable
     * @writable
     */
    protected $encoding;

    /**
     *
     * @type string
     * @readable
     * @writable
     */
    protected $assoc = NULL;
}

class Path extends Spaark\Core\Model\Base\Model
{
    public function __construct($string)
    {

    }
}