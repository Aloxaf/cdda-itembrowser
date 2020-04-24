<?php

namespace Repositories\Indexers;

use Repositories\RepositoryWriterInterface;
use CustomUtility\ValueUtil;

class Monster implements IndexerInterface
{
    protected $database;

    const DEFAULT_INDEX = "monsters";

    public function onNewObject(RepositoryWriterInterface $repo, $object)
    {
        if ($object->type == "MONSTER") {
            if (!isset($object->species)) {
                $object->species = array();
                $object->species[] = "none";
            }

            $repo->append(self::DEFAULT_INDEX, $object->id);
            $repo->set(self::DEFAULT_INDEX.".".$object->id, $object->repo_id);
            $objspecies = (array) $object->species;
            foreach ($objspecies as $species) {
                $repo->append("monster.species.$species", $object->id);
                $repo->addUnique("monster.species", $species);
            }

            $default_values = array(
                "melee_skill" => 0,
                "melee_dice_sides" => 0,
                "melee_cut" => 0,
                "melee_dice" => 0,
                "aggression" => 0,
                "morale" => 0,
                "dodge" => 0,
                "diff" => 0,
                "armor_cut" => 0,
                "armor_bash" => 0,
                "emit_fields" => array(),
                "vision_day" => 40,
                "vision_night" => 1,
                "special_attacks" => array(),
                "speed" => 100,
                "attack_cost" => 100,
            );
            foreach ($default_values as $k => $v) {
                ValueUtil::SetDefault($object, $k, $v);
            }

            $diff = ($object->melee_skill + 1) * $object->melee_dice * ($object->melee_cut + $object->melee_dice_sides) * 0.04 +
                ($object->dodge + 1) * (3 + $object->armor_bash + $object->armor_cut) * 0.04 +
                ($object->diff + count($object->special_attacks) + 8 * count($object->emit_fields));
            $diff *= ($object->hp + $object->speed - $object->attack_cost + ($object->morale + $object->aggression) * 0.1) * 0.01 +
                ($object->vision_day + 2 * $object->vision_night) * 0.01;
            $object->difficulty = $diff;

            if (isset($object->death_drops)) {
                if (is_string($object->death_drops)) {
                    $repo->append("itemgroup.dropfrom.{$object->death_drops}", $object->id);
                } else if (is_object($object->death_drops) && isset($object->death_drops->id)) {
                    $repo->append("itemgroup.{$object->death_drops->id}", $object->id);
                } else if (is_array($object->death_drops)) {
                    foreach ($object->death_drops as $tmp) {
                        $this->parseEntry($repo, $tmp, $object->id);
                    }
                } else if (isset($object->death_drops->entries)) {
                    foreach ($object->entries as $tmp) {
                        $this->parseEntry($repo, $tmp, $object->id);
                    }
                } else if (isset($object->death_drops->item)) {
                    $repo->append("item.dropfrom.{$entry->death_drops->item}", $object->id);
                } else if (isset($object->death_drops->group)) {
                    $repo->append("itemgroup.dropfrom.{$entry->death_drops->group}", $object->id);
                } else if (isset($object->death_drops->groups)) {
                    foreach ($object->death_drops->groups as $group) {
                        if (is_array($group)) {
                            $repo->append("itemgroup.dropfrom.{$group[0]}", $object->id);
                        } else {
                            $this->parseEntry($repo, $group, $object->id);
                        }
                    }
                } else if (isset($object->death_drops->items)) {
                    foreach ($object->death_drops->items as $item) {
                        if (is_array($item)) {
                            $repo->append("item.dropfrom.{$item[0]}", $object->id);
                        } else {
                            $this->parseEntry($repo, $item, $object->id);
                        }
                    }
                } else {
                    echo "ERROR Monster:".$object->id."\n";
                }
            }
            if (isset($object->harvest)) {
                $repo->append("itemgroup.harvestfrom.{$object->harvest}", $object->id);
            }

            return;
        }
    }

    private function parseEntry(RepositoryWriterInterface $repo, $entry, $id)
    {
        // TODO: container 等玩意儿？
        if (isset($entry->group)) {
            $repo->append("itemgroup.dropfrom.{$entry->group}", $id);
        } else if (isset($entry->item)) {
            $repo->append("item.dropfrom.{$entry->item}", $id);
        } else if (isset($entry->distribution)) {
            foreach ($entry->distribution as $tmp) {
                $this->parseEntry($repo, $tmp, $id);
            }
        } else if (isset($entry->collection)) {
            foreach ($entry->collection as $tmp) {
                $this->parseEntry($repo, $tmp, $id);
            }
        } else {
            echo "ERROR Monster:".$id."\n";
        }
    }

    public function onFinishedLoading(RepositoryWriterInterface $repo)
    {
        $starttime = microtime(true);

        $repo->sort("monster.species");

        $timediff = microtime(true) - $starttime;
        echo "Monster post-processing ".number_format($timediff, 3)." s.\n";
    }
}
