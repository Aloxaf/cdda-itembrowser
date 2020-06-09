<?php

class Mutation implements Robbo\Presenter\PresentableInterface
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

    public function getPresenter()
    {
        return new Presenters\Mutation($this);
    }

    public function getJson()
    {
        return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function getId()
    {
        return $this->data->id;
    }

    public function hasKey($name)
    {
        return isset($this->data->$name);
    }

    public function mutation_list($name)
    {
        if (!isset($this->data->$name)) {
            return NULL;
        }
        return implode("，", array_map(function ($id) {
            $mut = $this->repo->getModel("Mutation", $id);
            return '<a href="'.route('special.mutation', $mut->id).'">'.$mut->name.'</a>';
        }, $this->data->$name));
    }

    public function getName() {
        $name = $this->data->name;
        if (is_object($name)) {
            $name = $name->str;
        }
        return $name;
    }
}
