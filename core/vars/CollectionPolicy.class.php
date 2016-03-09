<?php

class CollectionPolicy extends Model
{
    private $data;

    public function __construct($policy)
    {
        $this->data = $this->json->parse
        (
            file_get_content(Config::APP_ROOT() . 'data/' . $policy . '.json')
        );
    }

    public function getType()
    {
        return $this->data['type'];
    }

    public function getModel()
    {
        $class = $this->data['type'];

        ClassLoader::loadModel($class);

        return $class::fromPolicy($this->data);
    }
}

