<?php

use Psy\VarDumper\Presenter;

class Special implements Robbo\Presenter\PresentableInterface
{
    use MagicModel;

    protected $data;
    protected $repo;

    public function __construct(Repositories\RepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function load($data)
    {
        $this->data = $data;
    }

    public function getRawName()
    {
        if (!isset($this->data->name)) {
            return;
        }

        $name = $this->data->name;
        if (is_object($this->data->name)) {
            if (isset($this->data->name->str)) {
                $name = $this->data->name->str;
            } elseif (isset($this->data->name->str_sp)) {
                $name = $this->data->name->str_sp;
            } else {
                $name = '';
            }
        }

        return $name;
    }

    public function getEffectName()
    {
        return implode(" / ", $this->data->name);
    }

    public function getPresenter()
    {
        return new Presenters\Special($this);
    }

    public function getJson()
    {
        return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function getDeficiency()
    {
        if (isset($this->data->deficiency)) {
            return $this->repo->getModel("Special", $this->data->deficiency);
        }
    }

    public function getExcess()
    {
        if (isset($this->data->excess)) {
            return $this->repo->getModel("Special", $this->data->excess);
        }
    }
}

