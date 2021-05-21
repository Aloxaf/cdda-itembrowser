<?php

namespace Repositories\Indexers;

use Repositories\RepositoryWriterInterface;

class ItemGroup implements IndexerInterface
{
    protected $database;

    const DEFAULT_INDEX = "itemgroup";

    public function onFinishedLoading(RepositoryWriterInterface $repo)
    {
        $starttime = microtime(true);

        $repo->sort(self::DEFAULT_INDEX);

        $timediff = microtime(true) - $starttime;
        echo "ItemGroup post-processing ".number_format($timediff, 3)." s.\n";
    }

    private function parseEntry(RepositoryWriterInterface $repo, $entry, $id)
    {
        if (isset($entry->item)) {
            $repo->append("item.dropfrom.{$entry->item}", $id);
        } else if (isset($entry->group)) {
            $repo->append("itemgroup.dropfrom.{$entry->group}", $id);
        } else if (isset($entry->distribution)) {
            foreach ($entry->distribution as $tmp) {
                $this->parseEntry($repo, $tmp, $id);
            }
        } else if (isset($entry->collection)) {
            foreach ($entry->collection as $tmp) {
                $this->parseEntry($repo, $tmp, $id);
            }
        } else if (is_string($entry)) {
            $repo->append("item.dropfrom.{$entry}", $id);
        } else {
            echo "ERROR: ".$id."\n";
        }
        $keys = array("contents", "ammo", "container");
        foreach ($keys as $k) {
            if (isset($entry->{$k."-item"})) {
                $tmp = $entry->{$k."-item"};
                if (!is_array($tmp)) {
                    $tmp = array($tmp);
                }
                foreach ($tmp as $tk) {
                    $repo->append("item.dropfrom.{$tk}", $id);
                }
            }
            if (isset($entry->{$k."-group"})) {
                $tmp = $entry->{$k."-group"};
                if (!is_array($tmp)) {
                    $tmp = array($tmp);
                }
                foreach ($tmp as $tk) {
                    $repo->append("itemgroup.dropfrom.{$tk}", $id);
                }
            }
        }
    }

    public function onNewObject(RepositoryWriterInterface $repo, $object)
    {
        if ($object->type != "item_group" && $object->type != "harvest") {
            return;
        }
        $repo->appendUnique(self::DEFAULT_INDEX, $object->id);
        $repo->set(self::DEFAULT_INDEX.".".$object->id, $object->repo_id);

        if ($object->type == "item_group") {
            if (isset($object->entries)) {
                foreach ($object->entries as $entry) {
                    $this->parseEntry($repo, $entry, $object->id);
                }
            }
            if (isset($object->items)) {
                foreach ($object->items as $item) {
                    if (is_array($item)) {
                        $repo->append("item.dropfrom.{$item[0]}", $object->id);
                    } else {
                        $this->parseEntry($repo, $item, $object->id);
                    }
                }
            }
            if (isset($object->groups)) {
                foreach ($object->groups as $group) {
                    if (is_array($group)) {
                        $repo->append("item.dropfrom.{$group[0]}", $object->id);
                    } else {
                        $this->parseEntry($repo, $group, $object->id);
                    }
                }
            }
        }
        if ($object->type == "harvest") {
            foreach ($object->entries as $entry) {
                if (strpos($entry->type ?? "", "_group") == false) {
                    $repo->append("item.harvestfrom.{$entry->drop}", $object->id);
                } else {
                    $repo->append("itemgroup.harvestfrom.{$entry->drop}", $object->id);
                }
            }
        }
    }
}
