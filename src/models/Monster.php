<?php

class Monster implements Robbo\Presenter\PresentableInterface
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

    public function loadDefault($id)
    {
        $data = json_decode('{"id":"'.$id.'","name":"'.$id.'?","type":"invalid"}');
        $this->load($data);
    }

    public function getPresenter()
    {
        return new Presenters\Monster($this);
    }

    public function getMinDamage()
    {
        return $this->melee_dice + $this->melee_cut;
    }

    public function getMaxDamage()
    {
        return ($this->melee_dice * $this->melee_dice_sides) + $this->melee_cut;
    }

    public function getAvgDamage()
    {
        return ($this->minDamage + $this->maxDamage) / 2;
    }

    public function getSpecies()
    {
        return (array) $this->data->species;
    }

    public function getFlags()
    {
        if (!isset($this->data->flags)) {
            return array();
        }

        return (array) $this->data->flags;
    }

    public function getId()
    {
        return $this->data->id;
    }

    public function matches($search)
    {
        $search = trim($search);
        if ($search == "" || !isset($this->data->name)) {
            return false;
        }

        $name = $this->data->name;
        if(is_object($this->data->name)){
            $name = $this->data->name->str;
        }
        return stristr($name, $search);
    }

    public function getModName()
    {
        if (isset($this->data->modname)) {
            $ident = $this->data->modname;
            return $this->repo->raw("modname.$ident");
        }
    }

    public function getJson()
    {
        return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function getMaterial()
    {
        if (!isset($this->data->material)) {
            return array();
        }
        $materials = array();
        foreach ($this->data->material as $material) {
            if ($material != "null") {
                $materials[] = $this->repo->getModel("Material", $material);
            }
        }

        return $materials;
    }

    public function getUpgradesTo()
    {
        if (isset($this->data->upgrades->into)) {
            $mon = $this->data->upgrades->into;
            $mon = $this->repo->getModel("Monster", $mon);
            return array((object)array(
                "monster" => $mon,
                "freq" => 1000,
                "cost_multiplier" => 1,
            ));
        } else {
            $group = $this->data->upgrades->into_group;
            $group = $this->repo->getModel("MonsterGroup", $group);
            return array_map(
                function ($mon) {
                    $model = $this->repo->getModel("Monster", $mon->monster);
                    $mon->monster = $model;
                    return $mon;
                },
                $group->monsters
            );
        }
    }
}
