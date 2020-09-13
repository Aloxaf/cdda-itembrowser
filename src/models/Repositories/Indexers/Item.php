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
            "archery" => "range",
            "handguns" => "range",
            "markmanship" => "range",
            "launcher" => "range",
            "firearms" => "range",
            "throw" => "range",
            "rifle" => "range",
            "shotgun" => "range",
            "smg" => "range",
            "pistol" => "range",
            "gun" => "range",
            "bashing" => "combat",
            "cutting" => "combat",
            "stabbing" => "combat",
            "dodge" => "combat",
            "melee" => "combat",
            "unarmed" => "combat",
            "computer" => "engineering",
            "electronics" => "engineering",
            "fabrication" => "engineering",
            "mechanics" => "engineering",
            "construction" => "engineering",
            "carpentry" => "engineering",
            "traps" => "engineering",
            "tailor" => "crafts",
            "firstaid" => "crafts",
            "cooking" => "crafts",
            "barter" => "social",
            "speech" => "social",
            "driving" => "survival",
            "survival" => "survival",
            "swimming" => "survival",
            "none" => "fun",
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

    public function onNewObject(RepositoryWriterInterface $repo, $object)
    {
        // capitalize type name to avoid failing on lowercase types
        if (!isset($this->types[strtoupper($object->type)]) || !isset($object->id)) {
            return;
        }

        $repo->append(self::DEFAULT_INDEX, $object->id);
        $repo->set(self::DEFAULT_INDEX.".".$object->id, $object->repo_id);

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

        // flatten weight values (g, mg, kg) to g
        if (isset($object->weight) && is_string($object->weight)) {
            $object->weight = $this->flattenWeight($object->weight);
        }

        // handle "ml" indicators in volume/container volume and flatten volume values into numbers
        if (isset($object->volume) && is_string($object->volume)) {
            $object->volume = $this->flattenVolume($object->volume);
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
        if ($object->type == "CONTAINER" || isset($object->container_data)) {
            $repo->append("container", $object->id);
            if (isset($object->container_data)) {
                if (isset($object->container_data->contains)) {
                    $object->contains = $object->container_data->contains;
                }
                if (isset($object->container_data->watertight)) {
                    $object->watertight = $object->container_data->watertight;
                }
                if (isset($object->container_data->seals)) {
                    $object->seals = $object->container_data->seals;
                }
            }

            if (stripos($object->contains, "ml") !== false) {
                $object->contains = floatval($object->contains) / 1000.0;
            }
            $object->contains = floatval($object->contains);
        }

        // handle properties that are modified by addition/multiplication
        // the property is removed after application, since each template reference can have its own modifiers
        if (isset($object->relative)) {
            foreach ($object->relative as $relkey => $relvalue) {
                if (isset($object->{$relkey})) {
                    // echo $relkey."\n";
                    if ($relkey == "//") {
                        continue;
                    }

                    // handle values containing unit measurements
                    if ($relkey == "volume" || $relkey == "barrel_length" || $relkey == "barrel_volume") {
                        $tempval = $this->flattenVolume($relvalue);
                        $object->{$relkey} += $tempval;
                    } elseif ($relkey == "weight") {
                        $tempval = $this->flattenWeight($relvalue);
                        $object->{$relkey} += $tempval;
                    } elseif ($relkey == "vitamins" && is_array($relvalue)) {
                        // special processing for vitamins (array with 2 indices, vitamin and count)
                        foreach ($relvalue as $vitamin_unit_key => $vitamin_unit) {
                            $found_vitamin = false;
                            foreach ($object->{$relkey} as $dest_vitamin_unit_key => $dest_vitamin_unit) {
                                if ($dest_vitamin_unit[0] == $vitamin_unit[0]) {
                                    $found_vitamin = true;
                                    $object->{$relkey}[$dest_vitamin_unit_key][1] += $vitamin_unit[1];
                                    break;
                                }
                            }
                            if (!$found_vitamin) {
                                array_push($object->{$relkey}, $vitamin_unit);
                            }
                        }
                    } elseif (($relkey == "ranged_damage" || $relkey == "damage") && is_object($relvalue)) {
                        $found_ranged_damage=false;
                        if (!isset($object->{$relkey}) || $object->{$relkey} === 0) {
                            $object->{$relkey} = $relvalue;
                        } else {
                            if (is_object($object->{$relkey})) {
                                if ($object->{$relkey}->damage_type == $relvalue->damage_type) {
                                    if (isset($object->{$relkey}->amount) && isset($relvalue->amount)) {
                                        $object->{$relkey}->amount += $relvalue->amount;
                                    }
                                    if (isset($object->{$relkey}->armor_penetration) && isset($relvalue->armor_penetration)) {
                                        $object->{$relkey}->armor_penetration += $relvalue->armor_penetration;
                                    }
                                    $found_ranged_damage = true;
                                }
                            } elseif (is_array($object->{$relkey})) {
                                foreach ($object->{$relkey} as $individual_damage_instance) {
                                    if ($individual_damage_instance->damage_type == $relvalue->damage_type) {
                                        if (isset($individual_damage_instance->amount) && isset($relvalue->amount)) {
                                            $individual_damage_instance->amount += $relvalue->amount;
                                        }
                                        if (isset($individual_damage_instance->armor_penetration) && isset($relvalue->armor_penetration)) {
                                            $individual_damage_instance->armor_penetration += $relvalue->armor_penetration;
                                        }
                                        $found_ranged_damage = true;
                                    }
                                }
                            }
                            if (!$found_ranged_damage) {
                                if (is_object($object->{$relkey})) {
                                    $temp_ranged_damage = $object->{$relkey};
                                    $object->{$relkey} = array();
                                    $object->{$relkey}[] = $temp_ranged_damage;
                                    $object->{$relkey}[] = $relvalue;
                                } elseif (is_array($object->{$relkey})) {
                                    $object->{$relkey}[] = $relvalue;
                                }
                            }
                        }
                    } else {
                        try {
                            $object->{$relkey} += $relvalue;
                        } catch (\Exception $e) {
                            echo "$object->id has a relative key that did not process correctly: $relkey"."\n";
                            throw $e;
                        }
                    }
                }
            }
            unset($object->relative);
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
                } elseif ($proportionkey == "damage" && is_object($proportionvalue)) {
                    if (is_array($object->damage)) {
                        foreach ($object->damage as $individual_damage_instance) {
                            if ($individual_damage_instance->damage_type == $proportionvalue->damage_type) {
                                if (isset($individual_damage_instance->amount) && isset($proportionvalue->amount)) {
                                    $individual_damage_instance->amount = floor($individual_damage_instance->amount * $proportionvalue->amount);
                                }
                                if (isset($individual_damage_instance->armor_penetration) && isset($proportionvalue->armor_penetration)) {
                                    $individual_damage_instance->armor_penetration = floor($individual_damage_instance->armor_penetration * $proportionvalue->armor_penetration);
                                }
                            }
                        }
                    } elseif (is_object($object->damage) && $object->damage->damage_type == $proportionvalue->damage_type) {
                        if (isset($object->damage->amount) && isset($proportionvalue->amount)) {
                            $object->damage->amount = floor($object->damage->amount * $proportionvalue->amount);
                        }
                        if (isset($object->damage->armor_penetration) && isset($proportionvalue->armor_penetration)) {
                            $object->damage->armor_penetration = floor($object->damage->armor_penetration * $proportionvalue->armor_penetration);
                        }
                    }
                } else {
                    $object->{$proportionkey} = floor($object->{$proportionkey} * $proportionvalue);
                }
            }
            unset($object->proportional);
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
            $damagecheck += $object->to_hit;
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
                $skill = "other";
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

        if (isset($object->material)) {
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
