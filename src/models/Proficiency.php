<?php

class Proficiency implements Robbo\Presenter\PresentableInterface
{
    use MagicModel;

    protected $data;

    public function load($data)
    {
        $this->data = $data;
    }

    public function getPresenter()
    {
        return new Presenters\Proficiency($this);
    }

    public function getId()
    {
        return $this->data->id;
    }

    public function getName() {
        if (!isset($this->data->name)) {
            return NULL;
        }
        $name = $this->data->name;
        if (is_object($name)) {
            $name = $name->str;
        }
        return $name;
    }
}
