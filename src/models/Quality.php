<?php

class Quality implements Robbo\Presenter\PresentableInterface
{
    use MagicModel;

    protected $data;

    public function load($data)
    {
        $str = $data->name;
        if (is_object($str)) {
            $str = $str->str;
        }
        $data->name = str_replace(" quality", "", $str);
        $this->data = $data;
    }

    public function getPresenter()
    {
        return new Presenters\Quality($this);
    }

    public function getId()
    {
        return $this->data->id;
    }
}
