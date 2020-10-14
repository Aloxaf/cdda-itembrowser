<?php

namespace Repositories\Indexers;

use Repositories\RepositoryWriterInterface;
use CustomUtility\ValueUtil;

class Item implements IndexerInterface
{
    protected $types;

    const DEFAULT_INDEX = "item";

    public function __construct()
    {
        // this is a hash with the valid item types
        $this->types = array_flip(array(
            "AMMO", "GUN", "ARMOR", "TOOL", "TOOL_ARMOR", "BOOK", "COMESTIBLE",
            "CONTAINER", "GUNMOD", "GENERIC", "BIONIC_ITEM", "VAR_VEH_PART",
            "_SPECIAL", "MAGAZINE", "WHEEL", "TOOLMOD", "ENGINE", "VEHICLE_PART",
            "PET_ARMOR",
        ));

        $this->book_types = array(
            "archery" => "远程",
            "handguns" => "远程",
            "markmanship" => "远程",
            "launcher" => "远程",
            "firearms" => "远程",
            "throw" => "远程",
            "rifle" => "远程",
            "shotgun" => "远程",
            "smg" => "远程",
            "pistol" => "远程",
            "gun" => "远程",
            "bashing" => "近战",
            "cutting" => "近战",
            "stabbing" => "近战",
            "dodge" => "近战",
            "melee" => "近战",
            "unarmed" => "近战",
            "computer" => "工程",
            "electronics" => "工程",
            "fabrication" => "工程",
            "mechanics" => "工程",
            "construction" => "工程",
            "carpentry" => "工程",
            "traps" => "工程",
            "tailor" => "工艺",
            "firstaid" => "工艺",
            "cooking" => "工艺",
            "barter" => "社交",
            "speech" => "社交",
            "driving" => "生存",
            "survival" => "生存",
            "swimming" => "生存",
            "none" => "娱乐",
        );

        $this->book_skill_trans = array(
            "archery" => "弓术",
            "handguns" => "手枪",
            "markmanship" => "枪法",
            "launcher" => "重武器",
            "firearms" => "枪法",
            "throw" => "投掷武器",
            "rifle" => "步枪",
            "shotgun" => "霰弹枪",
            "smg" => "冲锋枪",
            "pistol" => "手枪",
            "gun" => "枪法",
            "bashing" => "钝击武器",
            "cutting" => "斩击武器",
            "stabbing" => "近战",
            "dodge" => "闪避",
            "melee" => "近战",
            "unarmed" => "徒手格斗",
            "computer" => "计算机学",
            "electronics" => "电子学",
            "fabrication" => "制造",
            "mechanics" => "机械学",
            "construction" => "制造",
            "carpentry" => "制造",
            "traps" => "陷阱",
            "tailor" => "裁缝",
            "firstaid" => "急救",
            "cooking" => "烹饪",
            "barter" => "交易",
            "speech" => "口才",
            "driving" => "驾驶",
            "survival" => "生存",
            "swimming" => "游泳",
        );
    }

    public function onFinishedLoading(RepositoryWriterInterface $repo)
    {
        $starttime = microtime(true);
        foreach ($repo->raw(self::DEFAULT_INDEX) as $id) {
            $recipes = count($repo->raw("item.toolFor.$id"));
            if ($recipes > 0) {
                $repo->set("item.count.$id.toolFor", $recipes);
            }

            $recipes = count($repo->raw("item.recipes.$id"));
            if ($recipes > 0) {
                $repo->set("item.count.$id.recipes", $recipes);
            }

            $recipes = count($repo->raw("item.learn.$id"));
            if ($recipes > 0) {
                $repo->set("item.count.$id.learn", $recipes);
            }

            $recipes = count($repo->raw("item.disassembly.$id"));
            if ($recipes > 0) {
                $repo->set("item.count.$id.disassembly", $recipes);
            }

            $recipes = count($repo->raw("item.disassembledFrom.$id"));
            if ($recipes > 0) {
                $repo->set("item.count.$id.disassembledFrom", $recipes);
            }

            $recipes = count($repo->raw("item.uncraftToolFor.$id"));
            if ($recipes > 0) {
                $repo->set("item.count.$id.uncraftToolFor", $recipes);
            }

            $construction = $repo->raw("construction.$id");
            if (is_array($construction)) {
                $count = count($construction);
                if ($count > 0) {
                    $repo->set("item.count.$id.construction", $count);
                }
            }

            $recipes = count($repo->raw("item.deconstructFrom.$id"));
            if ($recipes > 0) {
                $repo->set("item.count.$id.deconstructFrom", $recipes);
            }

            $recipes = count($repo->raw("item.bashFromTerrain.$id"));
            if ($recipes > 0) {
                $repo->set("item.count.$id.bashFromTerrain", $recipes);
            }

            // sort item recipes, by difficulty
            $categories = $repo->raw("item.categories.$id");
            foreach ($categories as $category) {
                $recipes = $repo->raw("item.toolForCategory.$id.$category");
                usort($recipes, function ($a, $b) use ($repo) {
                    $a = $repo->get("recipe.$a");
                    $b = $repo->get("recipe.$b");

                    return $a->difficulty - $b->difficulty;
                });
                $repo->set("item.toolForCategory.$id.$category", $recipes);
            }

            // build item/vehicle part installation cross reference list per item
            if (strpos($id, "vpart_") === 0) {
                $vpart = $repo->get(self::DEFAULT_INDEX.".".$id);
                if (isset($vpart->item)) {
                    $repo->append("vpartlist.$vpart->item", $id);
                }
            }
        }

        $repo->sort("flags");
        $repo->sort("gunmodParts");
        $repo->sort("gunmodSkills");
        $repo->sort("armorParts");
        $repo->sort("gunSkills");
        $repo->sort("bookSkills");
        $repo->sort("consumableTypes");

        $timediff = microtime(true) - $starttime;
        echo "Item post-processing ".number_format($timediff, 3)." s.\n";
    }

    public function flattenVolume($val)
    {
        if (isset($val) && is_string($val)) {
            if (stripos($val, "ml") !== false) {
                $val = floatval($val) / 1000.0;
            } else {
                $val = floatval($val);
            }
        }

        return $val;
    }

    public function flattenWeight($val)
    {
        if (isset($val) && is_string($val)) {
            if (stripos($val, "kg") !== false) {
                $val = floatval($val) * 1000.0;
            } elseif (stripos($val, "mg") !== false) {
                $val = floatval($val) / 1000.0;
            } else {
                $val = floatval($val);
            }
        }

        return $val;
    }

    public function calc_to_hit($to_hit)
    {
        $val = -2;
        $types = array(
            "grip" => [-1, "bad", "none", "solid", "weapon"],
            "length" => [0, "hand", "short", "long" ],
            "surface" => [-2, "point", "line", "any", "every"],
            "balance" => [-2, "clumsy", "uneven", "neutral", "good"],
        );
        foreach ($to_hit as $k => $v) {
            $val += array_search($v, $types[$k], TRUE) + $types[$k][0] - 1;
        }
        return $val;
    }

    public function onNewObject(RepositoryWriterInterface $repo, $object)
    {
        // capitalize type name to avoid failing on lowercase types
        if (!isset($this->types[strtoupper($object->type)]) || !isset($object->id)) {
            return;
        }

        $repo->append(self::DEFAULT_INDEX, $object->id);
        $repo->set(self::DEFAULT_INDEX.".".$object->id, $object->repo_id);

        // 将对体积和重量的预处理提前，以免 abstract 物品的属性没有被格式化为数字导致后续展示的时候出错
        // flatten weight values (g, mg, kg) to g
        if (isset($object->weight) && is_string($object->weight)) {
            $object->weight = $this->flattenWeight($object->weight);
        }
        // handle "ml" indicators in volume/container volume and flatten volume values into numbers
        if (isset($object->volume) && is_string($object->volume)) {
            $object->volume = $this->flattenVolume($object->volume);
        }

        // nearby fire and integrated toolset are "virtual" items
        // they don't have anything special.
        // also exclude abstract objects
        if ($object->type == "_SPECIAL" || array_key_exists("abstract", $object)) {
            return;
        }

        ValueUtil::SetDefault($object, "reload", 100);
        ValueUtil::SetDefault($object, "to_hit", 0);
        if ($object->type == "ARMOR" || $object->type == "TOOL_ARMOR") {
            ValueUtil::SetDefault($object, "environmental_protection", 0);
            ValueUtil::SetDefault($object, "encumbrance", 0);
            ValueUtil::SetDefault($object, "max_encumbrance", 0);
        }
        if ($object->type == "BOOK") {
            ValueUtil::SetDefault($object, "skill", "none");
            ValueUtil::SetDefault($object, "required_level", 0);
        }
        if ($object->type == "GUN") {
            ValueUtil::SetDefault($object, "skill", "none");
            ValueUtil::SetDefault($object, "ranged_damage", 0);
            ValueUtil::SetDefault($object, "range", 0);
            ValueUtil::SetDefault($object, "recoil", 0);
            ValueUtil::SetDefault($object, "dispersion", 120);
            ValueUtil::SetDefault($object, "burst", 0);
        }
        if ($object->type == "GUNMOD") {
            ValueUtil::SetDefault($object, "location", "unknown");
            ValueUtil::SetDefault($object, "mod_targets", array("unknown_target"));
        }
        if ($object->type == "AMMO") {
            ValueUtil::SetDefault($object, "damage", 0);
            ValueUtil::SetDefault($object, "recoil", 0);
            ValueUtil::SetDefault($object, "loudness", 0);
            ValueUtil::SetDefault($object, "price", 0);
            ValueUtil::SetDefault($object, "pierce", 0);
            ValueUtil::SetDefault($object, "dispersion", 0);
            ValueUtil::SetDefault($object, "count", 1);
        }
        if ($object->type == "COMESTIBLE") {
            ValueUtil::SetDefault($object, "comestible_type", "None");
            ValueUtil::SetDefault($object, "phase", "solid");
            ValueUtil::SetDefault($object, "quench", 0);
            ValueUtil::SetDefault($object, "fun", 0);
            ValueUtil::SetDefault($object, "healthy", 0);
            ValueUtil::SetDefault($object, "addiction_potential", 0);
            ValueUtil::SetDefault($object, "charges", 1);
        }

        // flatten time values (s, m, h) to s
        if (isset($object->time) && is_string($object->time)) {
            if (stripos($object->time, "h") !== false) {
                $object->time = floatval($object->time) * 60.0 * 60.0;
            } elseif (stripos($object->time, "m") !== false) {
                $object->time = floatval($object->time) * 60.0;
            } else {
                $object->time = floatval($object->time);
            }
        }

        if (isset($object->min_pet_vol) && is_string($object->min_pet_vol)) {
            $object->min_pet_vol = $this->flattenVolume($object->min_pet_vol);
        }
        if (isset($object->contains) && is_string($object->contains)) {
            if (stripos($object->contains, "ml") !== false) {
                $object->contains = floatval($object->contains) / 1000.0;
            }

            $object->contains = floatval($object->contains);
        }

        // handle "ml" in barrel length info (legacy)
        if (isset($object->barrel_length) && is_string($object->barrel_length)) {
            $object->barrel_length = $this->flattenVolume($object->barrel_length);
        }

        // handle "ml" in barrel length info (currently known as barrel volume)
        if (isset($object->barrel_volume) && is_string($object->barrel_volume)) {
            $object->barrel_volume = $this->flattenVolume($object->barrel_volume);
        }

        // adjust volume for low volume large stack size ammunition
        if ($object->type == "AMMO") {
            if (isset($object->stack_size) && $object->stack_size > 0) {
                if (!isset($object->volume) && isset($object->id)) {
                    $object->volume = 0;
                    echo $object->id." is missing volume data.\n";
                }
                if ((floatval($object->volume) * 1.0) / $object->stack_size < .004) {
                    $object->volume = .004 * $object->stack_size;
                }
            }
        }

        // handle container values
        if (isset($object->pocket_data)) {
            $is_container = false;
            foreach ($object->pocket_data as $pocket_data) {
                $keys = array(
                    "max_contains_volume", "max_contains_weight", "min_item_volume", "max_item_volume",
                    "max_item_length", "magazine_well",
                );
                foreach ($keys as $key) {
                    if (isset($pocket_data->$key)) {
                        $is_container = true;
                        if (
                            strpos($pocket_data->$key, "kg") !== false 
                            || strpos($pocket_data->$key, "L") !== false
                            || strpos($pocket_data->$key, "meter") !== false) {
                            $pocket_data->$key = floatval($pocket_data->$key);
                        } else {
                            $pocket_data->$key = floatval($pocket_data->$key) / 1000;
                        }
                    }
                }
            }
            if ($is_container) {
                $repo->append("container", $object->id);
            }
        }

        if (isset($object->proportional)) {
            foreach ($object->proportional as $proportionkey => $proportionvalue) {
                if (!isset($object->{$proportionkey}) || is_array($object->{$proportionkey})) {
                    continue;
                }
                if ($proportionkey == "volume") {
                    $tempval = $this->flattenVolume($proportionvalue);
                    $object->{$proportionkey} = floor($object->{$proportionkey} * $proportionvalue);
                } elseif ($proportionkey == "weight") {
                    $tempval = $this->flattenWeight($proportionvalue);
                    $object->{$proportionkey} = floor($object->{$proportionkey} * $proportionvalue);
                } elseif ($proportionkey == "damage") {
                    if (is_numeric($proportionvalue)) {
                        $object->damage = floor($object->damage * $proportionvalue);
                    }
                    if (isset($proportionvalue->amount) && isset($object->damage->amount)) {
                        $object->damage->amount = floor($object->damage->amount * $proportionvalue->amount);
                    }
                    if (isset($proportionvalue->armor_penetration) && isset($object->damage->armor_penetration)) {
                        $object->damage->armor_penetration = floor($object->damage->armor_penetration * $proportionvalue->armor_penetration);
                    }
                } else {
                    try {
                        $object->{$proportionkey} = floor($object->{$proportionkey} * $proportionvalue);
                    } catch (\Exception $e) {
                        echo "ERROR proportionkey: $proportionkey => ".var_dump($object->{$proportionkey});
                    }
                }
            }
            unset($object->proportional);
        }

        if (isset($object->to_hit)) {
            $to_hit = $object->to_hit;
            $object->cib_to_hit = is_object($to_hit) ? $this->calc_to_hit($to_hit) : $to_hit;
        }

        // items with enough damage might be good melee weapons.
        $damagecheck = 0;
        if (isset($object->bashing)) {
            $damagecheck += $object->bashing;
        }
        if (isset($object->cutting)) {
            $damagecheck += $object->cutting;
        }
        if (isset($object->to_hit)) {
            $damagecheck += $object->cib_to_hit;
        }
        if ($damagecheck >= 8 && strtoupper($object->type) != "VEHICLE_PART" && isset($object->weight) && $object->weight < 15000 && (!isset($object->dispersion) || $object->dispersion == 0)) {
            $repo->append("melee", $object->id);

            // handle preprocessing of piercing weapons after melee status is established
            ValueUtil::SetDefault($object, "bashing", 0);
            ValueUtil::SetDefault($object, "cutting", 0);
            ValueUtil::SetDefault($object, "piercing", 0);

            if (isset($object->flags)) {
                $foundpiercing = false;
                if (is_array($object->flags)) {
                    foreach ($object->flags as $indexnum => $flag) {
                        if (!is_array($flag) && $flag == "SPEAR") {
                            $foundpiercing = true;
                            break;
                        }
                    }
                } else {
                    if ($object->flags == "SPEAR") {
                        $foundpiercing = true;
                    }
                }

                if ($foundpiercing) {
                    $object->piercing = $object->cutting;
                    $object->cutting = 0;
                }
            }
        }

        $is_armor = in_array($object->type, ["ARMOR", "TOOL_ARMOR"]);

        // create an index with armor for each body part they cover.
        if ($is_armor and !isset($object->covers)) {
            $repo->append("armor.none", $object->id);
        } elseif ($is_armor and isset($object->covers)) {
            foreach ($object->covers as $part) {
                $part = strtolower($part);
                $repo->append("armor.$part", $object->id);
                $repo->addUnique("armorParts", $part);
            }
        }

        if ($object->type == "COMESTIBLE") {
            $repo->append("food", $object->id);
        }
        if ($object->type == "TOOL") {
            $repo->append("tool", $object->id);
        }

        //apply substitution saving
        if (isset($object->sub)) {
            $repo->add_substitute($object->id, $object->sub);
        }

        // save books per skill
        if ($object->type == "BOOK") {
            if (isset($this->book_types[$object->skill])) {
                $skill = $this->book_types[$object->skill];
            } else {
                $skill = "其他";
            }
            if (isset($this->book_skill_trans[$object->skill])) {
                $object->skill = $this->book_skill_trans[$object->skill];
            }
            $repo->append("book.$skill", $object->id);
            $repo->addUnique("bookSkills", $skill);
        }

        if ($object->type == "GUN") {
            if (is_object($object->ranged_damage)) {
                if (isset($object->ranged_damage->amount)) {
                    $object->ranged_damage_type = $object->ranged_damage->damage_type;
                    $object->ranged_damage = $object->ranged_damage->amount;
                } else {
                    $object->ranged_damage = "N/A";
                }
            }
            if (!isset($object->skill)) {
                $object->skill = "none";
            }
            $repo->append("gun.$object->skill", $object->id);
            $repo->addUnique("gunSkills", $object->skill);
            if (isset($object->ammo)) {
                if (is_array($object->ammo)) {
                    foreach ($object->ammo as $ammo) {
                        $repo->addUnique("ammo.$ammo.usedby", $object->id);
                    }
                } else {
                    $ammo = $object->ammo;
                    $repo->addUnique("ammo.$ammo.usedby", $object->id);
                }
            }
        }

        if ($object->type == "GUNMOD") {
            foreach ($object->mod_targets as $target) {
                $repo->append("gunmods.$target.$object->location", $object->id);
                $repo->addUnique("gunmodSkills", $target);
            }
            $repo->addUnique("gunmodParts", $object->location);
        }

        if ($object->type == "AMMO") {
            if (isset($object->ammo_type)) {
                // some ammunition has multiple types
                if (is_array($object->ammo_type)) {
                    foreach ($object->ammo_type as $minitype) {
                        $repo->append("ammo.$minitype", $object->id);
                    }
                } else {
                    $repo->append("ammo.$object->ammo_type", $object->id);
                }
            }
        }

        if ($object->type == "COMESTIBLE") {
            if (!isset($object->calories) && isset($object->nutrition)) {
                $object->calories = floor($object->nutrition * 2500.0 / 288.0);
            } elseif (isset($object->calories)) {
                $object->calories = floor(floor($object->calories / 2500.0 * 288.0) * 2500.0 / 288.0);
            } else {
                ValueUtil::SetDefault($object, "nutrition", 0);
                ValueUtil::SetDefault($object, "calories", 0);
            }
            $object->nutrition = $object->calories;

            $type = strtolower($object->comestible_type);

            if (isset($object->brewable)) {
                $repo->append("consumables.fermentable", $object->id);
                $repo->addUnique("consumableTypes", "fermentable");
            } else {
                $repo->append("consumables.$type", $object->id);
                $repo->addUnique("consumableTypes", $type);
            }
        }

        if (isset($object->qualities)) {
            foreach ($object->qualities as $quality) {
                $repo->append("quality.$quality[0]", $object->id);
            }
        }

        if (isset($object->material) && !(is_array($object->material) && count($object->material) === 0)) {
            $materials = (array) $object->material;
            $repo->append("material.$materials[0]", $object->id);
            if (count($materials) > 1 and $materials[1] != "null") {
                $repo->append("material.$materials[1]", $object->id);
            }
        }

        if (isset($object->flags)) {
            $flags = (array) $object->flags;
            foreach ($flags as $flag) {
                if ($flag != "") {
                    $repo->append("flag.$flag", $object->id);
                    $repo->addUnique("flags", $flag);
                }
            }
        }
    }
}
