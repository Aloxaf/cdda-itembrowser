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
                "vision_day" => 40,
                "armor_cut" => 0,
                "armor_bash" => 0,
                "emit_fields" => array(),
                "vision_night" => 1, // TODO: This is not correct!
                "special_attacks" => array(),
                "speed" => 100,
                "attack_cost" => 100,
            );
            foreach ($default_values as $k => $v) {
                ValueUtil::SetDefault($object, $k, $v);
            }

            $diff_base = isset($object->diff) ? $object->diff : 0;
            $diff = ($object->melee_skill + 1) * $object->melee_dice *
                ($object->melee_cut + $object->melee_dice_sides) * 0.04 +
                ($object->dodge + 1) * (3 + $object->armor_bash + $object->armor_cut) * 0.04 +
                ($diff_base + count($object->special_attacks) + 8 * count($object->emit_fields));
            $diff *= ($object->hp + $object->speed - $object->attack_cost + ($object->morale + $object->aggression) * 0.1) * 0.01 +
                ($object->vision_day + 2 * $object->vision_night) * 0.01;
            $object->difficulty = $diff;

            return;
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
