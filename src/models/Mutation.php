<?php

use function DeepCopy\deep_copy;

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

    public function getModName()
    {
        if (isset($this->data->modname)) {
            $id = $this->data->modname;
            return $this->repo->raw("modname.$id");
        }
    }

    public function getName() {
        $name = $this->data->name;
        if (is_object($name)) {
            $name = $name->str;
        }
        return $name;
    }

    public function getWetProtection() {
        if (!isset($this->data->wet_protection))
            return array();

        $ret = array();
        foreach ($this->data->wet_protection as $wet) {
            $part = $this->repo->getModel("Item", $wet->part);
            if (isset($wet->good)) {
                $tmp = array("parts" => $part, "good" => $wet->good);
            } elseif (isset($wet->neutral)) {
                $tmp = array("parts" => $part, "neutral" => $wet->neutral);
            } else {
                $tmp = array("parts" => $part, "good" => $wet->ignored, "neutral" => $wet->ignored);
            }
            $ret[$wet->part] = $tmp;
        }
        return $ret;
    }

    public function getArmor()
    {
        if (!isset($this->data->armor))
            return array();
        $ret = array();
        foreach ($this->data->armor as $armor) {
            if (is_array($armor->parts)) {
                foreach ($armor->parts as $part) {
                    $tmp = (array)deep_copy($armor);
                    $tmp["parts"] = $this->repo->getModel("Item", $part);
                    $ret[$part] = $tmp;
                }
            } else {
                $ret[$armor->parts] = $armor;
            }
        }
        return $ret;
    }

    public function getEncumbrance($key) {
        if (!isset($this->data->{$key}))
            return array();
        $ret = array();
        foreach ($this->data->{$key} as $en) {
            $part = $this->repo->getModel("Item", $en[0]);
            $ret[$en[0]] = array("parts" => $part, $key => $en[1]);
        }
        return $ret;
    }

    public function getAllArmor()
    {
        $ret = $this->getArmor();
        $rest = array(
            $this->getWetProtection(),
            $this->getEncumbrance("encumbrance_covered"),
            $this->getEncumbrance("encumbrance_always")
        );
        foreach ($rest as $item) {
            foreach ($item as $name => $prot) {
                if (!isset($ret[$name]))
                    $ret[$name] = $prot;
                else
                    $ret[$name] = array_merge($ret[$name], $prot);
            }
        }
        return $ret;
    }
}
