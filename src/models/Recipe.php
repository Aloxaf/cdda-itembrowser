<?php

class Recipe implements Robbo\Presenter\PresentableInterface
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

    public function getSkillsRequired()
    {
        if (!isset($this->data->skills_required)) {
            return;
        }

        $skills = $this->data->skills_required;
        if (!isset($skills[0])) {
            return array();
        }
        if (!is_array($skills[0])) {
            return array(array($this->repo->getModel("Item", $skills[0]), $skills[1]));
        }

        return array_map(function ($i) {
            return array($this->repo->getModel("Item", $i[0]), $i[1]);
        }, $skills);
    }

    public function getResult()
    {
        return $this->repo->getModel("Item", $this->data->result);
    }

    public function getHasTools()
    {
        return isset($this->data->tools);
    }

    public function getHasComponents()
    {
        return isset($this->data->components);
    }

    public function getHasByproducts()
    {
        return isset($this->data->byproducts);
    }

    public function getTools()
    {
        return array_map(function ($group) {
            return array_map(function ($tool) {
                list($id, $amount) = $tool;

                return array($this->repo->getModel("Item", $id), $amount);
            }, $group);
        }, $this->data->tools);
    }

    public function getComponents()
    {
        return array_map(function ($group) {
            return array_map(function ($component) {
                list($id, $amount) = $component;

                return array($this->repo->getModel("Item", $id), $amount);
            }, $group);
        }, $this->data->components);
    }

    public function getByproducts()
    {
        return array_map(function ($byproduct) {
            list($id, $amount) = $byproduct;

            return array($this->repo->getModel("Item", $id), $amount);
        }, $this->data->byproducts);
    }

    public function getCanBeLearned()
    {
        return !empty($this->data->book_learn);
    }

    public function getBooksTeaching()
    {
        if (is_array($this->data->book_learn)) {
            return array_map(function ($book) {
                return array($this->repo->getModel("Item", $book[0]), $book[1]);
            }, $this->data->book_learn);
        } else {
            $ret = [];
            foreach ($this->data->book_learn as $book => $info) {
                $ret[] = array($this->repo->getModel("Item", $book), $info->skill_level);
            }
            return $ret;
        }
    }

    public function getHasQualities()
    {
        return !empty($this->data->qualities);
    }

    public function getQualities()
    {
        return array_map(function ($quality) {
            if (!is_array($quality)) {
                $quality = array($quality);
            }
            return array_map(function ($quality) {
                return array(
                    "quality" => $this->repo->getModel("Quality", $quality->id),
                    "level" => $quality->level,
                    "amount" => $quality->amount,
                );
            }, $quality);
        }, $this->data->qualities);
    }

    public function getPresenter()
    {
        return new Presenters\Recipe($this);
    }

    public function getId()
    {
        return $this->data->repo_id;
    }

    public function getModName()
    {
        if (isset($this->data->modname)) {
            $id = $this->data->modname;
            return $this->repo->raw("modname.$id");
        }
    }

    public function getSkillUsed()
    {
        if (!isset($this->data->skill_used))
            return "N/A";
        $skill = $this->repo->getModel("Item", $this->data->skill_used);
        return $skill->name;
    }
}
