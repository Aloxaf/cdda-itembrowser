<?php

// https://github.com/laravel/framework/issues/26229
// 添加完新 Model 后需要 composer -dsrc update
// php src/artisan make:model Customer
// php src/artisan make:controller CustomersController --resource 
// OR
// php src/artisan make:controller CustomersController --model=Customer

use function DeepCopy\deep_copy;

class ItemGroup implements Robbo\Presenter\PresentableInterface
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
        $this->json = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function getPresenter()
    {
        return new Presenters\ItemGroup($this);
    }

    public function getModName()
    {
        if (isset($this->data->modname)) {
            $id = $this->data->modname;
            return $this->repo->raw("modname.$id");
        }
    }

    public function getId()
    {
        if (isset($this->data->id)) {
            return $this->data->id;
        }
    }

    public function parseEntries($entries)
    {
        $entries = deep_copy($entries);
        foreach ($entries as &$entry) {
            if (isset($entry->group)) {
                $entry->group = $this->repo->getModel("ItemGroup", $entry->group);
            } else if (isset($entry->item) && is_string($entry->item)) {
                $entry->item = $this->repo->getModel("Item", $entry->item);
                if (isset($entry->container_group)) {
                    $entry->container_group = $this->repo->getModel("ItemGroup", $entry->container_group);
                }
            } else if (isset($entry->distribution)) {
                $entry->distribution = $this->parseEntries($entry->distribution);
            } else if (isset($entry->collection)) {
                $entry->collection = $this->parseEntries($entry->collection);
            }
            $keys = array(
                "contents-item" => "Item",
                "contents-group" => "ItemGroup",
                "container-item" => "Item",
                "container-group" => "ItemGroup",
                "ammo-item" => "Item",
                "ammo-group" => "ItemGroup",
            );
            foreach ($keys as $k => $v) {
                if (isset($entry->{$k})) {
                    if (is_string($entry->{$k})) {
                        $tmp = $this->repo->getModel($v, $entry->{$k});
                        $entry->{$k} = $tmp;
                    } else {
                        foreach ($entry->{$k} as $tk => $tv) {
                            $tmp = $this->repo->getModel($v, $tv);
                            $entry->{$k}[$tk] = $tmp;
                        }
                    }
                }
            }
            $keys = array("damage", "count", "charges");
            foreach ($keys as $k) {
                if (isset($entry->{$k."-min"}) && isset($entry->{$k."-max"})) {
                    $entry->{$k} = array($entry->{$k."-min"}, $entry->{$k."-max"});
                }
            }
        }
        return $entries;
    }

    public function getEntries() {
        if (isset($this->data->groups)) {
            if (!isset($this->data->entries)) {
                $this->data->entries = array();
            }
            foreach ($this->data->groups as $group) {
                if (!is_array($group)) {
                    $group = array($group);
                }
                $this->data->entries[] = (object)array(
                    "group" => $group[0],
                    "prob" => count($group) == 2 ? $group[1] : 100,
                );
            }
        }
        if (isset($this->data->items)) {
            if (!isset($this->data->entries)) {
                $this->data->entries = array();
            }
            foreach ($this->data->items as $item) {
                if (is_array($item)) {
                    $this->data->entries[] = (object)array(
                        "item" => $item[0],
                        "prob" => count($item) == 2 ? $item[1] : 100,
                    );
                } else {
                    $this->data->entries[] = $item;
                }
            }
        }
        if (isset($this->data->entries)) {
            return $this->parseEntries($this->data->entries);
        }
    }

    public function getHarvest()
    {
        $entries = $this->data->entries;
        foreach ($entries as $entry) {
            if (strpos($entry->type, "_group") == false) {
                $entry->drop = $this->repo->getModel("Item", $entry->drop);
            } else {
                $entry->drop = $this->repo->getModel("ItemGroup", $entry->drop);
            }
        }
        return $entries;
    }

    public function getJson()
    {
        return $this->json;
    }

    public function getDropFrom()
    {
        return array_map(
            function ($id) {
                try {
                    // TODO: This is hack
                    if (strpos($id, "mon") === false) {
                        return $this->repo->getModel("ItemGroup", $id);
                    } else {
                        throw new Exception("Not Found");
                    }
                } catch (\Exception $e) {
                    try {
                        return $this->repo->getModel("Monster", $id);
                    } catch (\Exception $e) {
                        return $this->repo->getModel("ItemGroup", $id);
                    }
                }
            },
            $this->repo->raw("itemgroup.dropfrom.$this->id")
        );
    }

    public function getHarvestFrom()
    {
        return array_map(
            function ($id) {
                if (strpos($id, "mon") === false) {
                    return $this->repo->getModel("ItemGroup", $id);
                } else {
                    try {
                        return $this->repo->getModel("Monster", $id);
                    } catch (\Exception $e) {
                        return $this->repo->getModel("ItemGroup", $id);
                    }
                }
            },
            $this->repo->raw("itemgroup.harvestfrom.$this->id")
        );
    }
}
