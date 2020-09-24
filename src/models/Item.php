<?php

class Item implements Robbo\Presenter\PresentableInterface
{
    use MagicModel;

    protected $data;
    protected $repo;

    private $cut_pairs = array(
      "cotton" => "rag",
      "leather" => "leather",
      "fur" => "fur",
      "nomex" => "nomex",
      "plastic" => "plastic_chunk",
      "kevlar" => "kevlar_plate",
      "wood" => "skewer",
    );

    public function __construct(Repositories\RepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function load($data)
    {
        if (!isset($data->material) || (is_array($data->material) && count($data->material) === 0)) {
            $data->material = array("null", "null");
        }
        if (!is_array($data->material)) {
            $data->material = array($data->material, "null");
        }
        if (!isset($data->material[1])) {
            $data->material[1] = "null";
        }

        if (!isset($data->flags)) {
            $data->flags = array();
        } else {
            if (isset($data->flags[0])) {
                $data->flags = array_flip((array) $data->flags);
            }
        }
        if (!isset($data->qualities)) {
            $data->qualities = array();
        }

        $this->data = $data;
    }

    public function loadDefault($id)
    {
        $data = json_decode('{"id":"'.$id.'","name":"'.$id.'?","type":"invalid"}');
        $this->load($data);
    }

    public function getColor()
    {
        if (!isset($this->data->color)) {
            return "white";
        }
        $color = str_replace("_", "", $this->data->color);
        $colorTable = array(
            "lightred" => "indianred",
        );
        if (isset($colorTable[$color])) {
            return $colorTable[$color];
        }

        return $color;
    }

    public function getSymbol()
    {
        if (!isset($this->data->symbol)) {
            return " ";
        }

        return $this->data->symbol;
    }

    public function getName()
    {
        if (!isset($this->data->name)) {
            return;
        }

        $name = $this->data->name;
        if (is_object($this->data->name)) {
            if (isset($this->data->name->str)) {
                $name = $this->data->name->str;
            } elseif (isset($this->data->name->str_sp)) {
                $name = $this->data->name->str_sp;
            } else {
                $name = '';
            }
        }

        return ($this->type == "bionic" ? "CBM: " : "").$name; //." (".$this->data->id.")";
    }

    public function getRecipes()
    {
        if (isset($this->data->original_id)) {
            return $this->repo->allModels("Recipe", "item.recipes.{$this->data->original_id}");
        }

        return $this->repo->allModels("Recipe", "item.recipes.{$this->data->id}");
    }

    public function getDisassembly()
    {
        return $this->repo->allModels("Recipe", "item.disassembly.{$this->id}");
    }

    public function getDisassembledFrom()
    {
        return $this->repo->allModels("Recipe", "item.disassembledFrom.$this->id");
    }

    public function getToolFor()
    {
        return $this->repo->allModels("Item", "item.toolFor.$this->id");
    }

    public function getHasVpartlist()
    {
        return count($this->repo->allModels("Item", "vpartlist.$this->id"));
    }

    public function getVpartFor()
    {
        $vparts = $this->repo->allModels("Item", "vpartlist.$this->id");
        $string1 = "";
        $inner = array();
        foreach ($vparts as $item) {
            // build link name with name and ID to distinguish between multiple usage of vehicle part names
            $inner[] = '<a href="'.route("item.view", array("id" => $item->id)).'">'.$item->name.(substr($item->id, 6) !== $item->name ? " (".substr($item->id, 6).")" : "").'</a>';
        }

        return "&gt; ".implode("<br>&gt; ", $inner)."\n";
    }

    public function count($type)
    {
        return $this->repo->raw("item.count.$this->id.$type", 0);
    }

    public function flatcount($type)
    {
        return $this->repo->raw("item.count.$this->original_id.$type", 0);
    }

    public function getToolCategories()
    {
        $categories = $this->repo->raw("item.categories.{$this->id}");
        if (empty($categories)) {
            return array("CC_NONE" => "CC_NONE");
        }

        return $categories;
    }

    public function getToolForCategory($category)
    {
        return $this->repo->allModels("Recipe", "item.toolForCategory.{$this->data->id}.$category");
    }

    public function getLearn()
    {
        return $this->repo->allModels("Recipe", "item.learn.{$this->data->id}");
    }

    public function getIsArmor()
    {
        return in_array($this->data->type, ["ARMOR", "TOOL_ARMOR"]);
    }

    public function getIsConsumable()
    {
        return $this->data->type == "COMESTIBLE";
    }

    public function getIsAmmo()
    {
        return $this->data->type == "AMMO";
    }

    public function getIsVehiclePart()
    {
        return strtoupper($this->data->type) == "VEHICLE_PART";
    }

    public function getIsBook()
    {
        return $this->data->type == "BOOK";
    }

    public function getIsGun()
    {
        return $this->data->type == "GUN";
    }

    public function protection($type)
    {
        $mat1 = $this->material1;
        $mat2 = $this->material2;

        $variable = "{$type}_resist";
        $thickness = $this->material_thickness;
        if ($thickness < 1 || ($variable == "acid_resist" || $variable == "fire_resist")) {
            $thickness = 1;
        }

        $val = 0;
        if ($mat2 == "null" || $mat2->id == "null") {
            $val = $thickness * $mat1->$variable;
        } else {
            $val = $thickness * (($mat1->$variable + $mat2->$variable) / 2);
        }

        if (($variable == "acid_resist" || $variable == "fire_resist") && $this->environmental_protection < 10) {
            $val = $this->environmental_protection / 10.0 * $val;
        }

        return round($val);
    }

    public function getIsTool()
    {
        return isset($this->data->max_charges) and isset($this->data->ammo);
    }

    public function getStackSize()
    {
        return isset($this->data->stack_size) ? $this->data->stack_size : 1;
    }

    public function getVolume()
    {
        if (!isset($this->data->volume)) {
            return;
        }
//         if ($this->isAmmo) {
//             return round($this->data->volume/$this->stackSize);
//         }

        return $this->data->volume;
    }

    public function getWeight()
    {
        if (!isset($this->data->weight)) {
            return;
        }
        if ($this->isAmmo) {
            return floatval($this->data->weight) * $this->data->count;
        }

        return floatval($this->data->weight);
    }

    public function getMovesPerAttack()
    {
        if (!isset($this->data->weight) || !isset($this->data->volume)) {
            return;
        }

        return floor(65 + 4 * floatval($this->volume) + floatval($this->weight) / 60);
    }

    public function getToHit()
    {
        if (!isset($this->data->to_hit)) {
            return 0;
        }

        return sprintf("%+d", $this->data->to_hit);
    }

    public function getPierce()
    {
        if (isset($this->data->damage->armor_penetration)) {
            return $this->data->damage->armor_penetration;
        } else if (isset($this->data->pierce)) {
            return $this->data->pierce;
        }
        return 0;
    }

    public function getMaterial1()
    {
        return $this->repo->getModel("Material", $this->data->material[0]);
    }

    public function getMaterial2()
    {
        return $this->repo->getModel("Material", $this->data->material[1]);
    }

    public function getCanBeCut()
    {
        if (!$this->volume) {
            return false;
        }
        $material1 = $this->material1->id;
        $material2 = $this->material2->id;

        return in_array($material1, array_keys($this->cut_pairs)) and
              in_array($material2, array_keys($this->cut_pairs));
    }

    public function getCutResult()
    {
        $results = [];
        $materials = $this->materials;

        foreach ($materials as $material) {
            $results[] = [
                "amount" => $this->volume / count($materials),
                "item" => $this->repo->getModel("Item", $this->cut_pairs[$material->id]),
            ];
        }

        return $results;
    }

    public function getIsResultOfCutting()
    {
        return in_array($this->id, array_keys(array_flip($this->cut_pairs)));
    }

    public function getMaterialToCut()
    {
        $pairs = array_flip($this->cut_pairs);

        return $pairs[$this->id];
    }

    public function getHasAmmoTypes()
    {
        return isset($this->data->ammo);
    }

    public function getAmmoTypes()
    {
        $ammolist = $this->data->ammo;
        $ammotypes = array();
        if (is_array($ammolist)) {
            foreach ($ammolist as $ammoitem) {
                $nextammolist = $this->repo->allModels("Item", "ammo.$ammoitem");
                if (is_array($nextammolist)) {
                    $ammotypes = array_merge($ammotypes, $nextammolist);
                }
            }
        } else {
            $ammotypes = $this->repo->allModels("Item", "ammo.$ammolist");
        }

        foreach ($ammotypes as &$ammotype) {
            $ammo_damage_multiplier = 1.0;
            if ($this->data->type == "GUN") {
                if ($ammotype->prop_damage > 0) {
                    $ammo_damage_multiplier = $ammotype->prop_damage;
                } else if (isset($ammotype->data->damage->constant_damage_multiplier)) {
                    $ammo_damage_multiplier = $ammotype->data->damage->constant_damage_multiplier;
                }
            }

            $result = floatval($ammotype->damage);
            if (is_object($result)) {
                $result = $result->amount;
            }
            $rdamage=0;
            if (isset($this->data->ranged_damage)) {
                $rdamage = $this->data->ranged_damage;
            }
            if (is_object($rdamage)) {
                $rdamage = $rdamage->amount;
            }
            if ($this->data->type == "GUN") {
                $result = ($result + $rdamage) * $ammo_damage_multiplier;
            }
            $ammotype->damage = $result;
        }
        unset($ammotype);

        return $ammotypes;
    }

    public function isMadeOf($material)
    {
        return stristr($this->material1->name, $material);
    }

    public function matches($text)
    {
        $text = trim($text);

        if ($text == "") {
            return false;
        }

        $name = $this->name;
        if (is_object($this->name)) {
            $name = $this->name->str;
        }

        return $this->symbol == $text ||
            stristr($this->id, $text) ||
            stristr($name, $text) ||
            array_filter(
                $this->data->qualities,
                function ($q) use ($text) {
                    return stristr($q[0], $text);
                }
            );
    }

    public function getPresenter()
    {
        return new Presenters\Item($this);
    }

    public function getClothingLayer()
    {
        if (!isset($this->data->flags)) {
            return "";
        }
        if (isset($this->data->flags["PERSONAL"])) {
            return "Personal";
        } elseif (isset($this->data->flags["SKINTIGHT"])) {
            return "Skintight";
        } elseif (isset($this->data->flags["WAIST"])) {
            return "Waist";
        } elseif (isset($this->data->flags["OUTER"])) {
            return "Outer";
        } elseif (isset($this->data->flags["BELTED"])) {
            return "Belted";
        } elseif (isset($this->data->flags["AURA"])) {
            return "Aura";
        } else {
            return "Regular";
        }
    }

    public function hasFlag($flag)
    {
        return isset($this->flags[$flag]);
    }

    public function getQualities()
    {
        return array_map(function ($quality) {
            return array(
                "quality" => $this->repo->getModel("Quality", $quality[0]),
                "level" => $quality[1],
            );
        }, $this->data->qualities);
    }

    public function qualityLevel($quality)
    {
        foreach ($this->data->qualities as $q) {
            if ($q[0] == $quality) {
                return $q[1];
            }
        }
    }

    public function getSlug()
    {
        $name = $this->data->name;
        if (is_object($this->data->name)) {
            $name = $this->data->name->str;
        }

        return str_replace(" ", "_", $name);
    }

    public function noise($ammo)
    {
        if (!$this->isGun) {
            return 0;
        }

        if (in_array($ammo->ammo_type, array('bolt', 'arrow', 'pebble', 'fishspear', 'dart'))) {
            return 0;
        }

        $ret = $ammo->damage;
        if (is_object($ret)) {
            $ret = $ret->amount;
        }
        $ret *= 0.8;
        if ($ret > 5) {
            $ret += 20;
        }
        $ret *= 1.5;

        return $ret;
    }

    public function getMaterials()
    {
        $materials = array(
            $this->material1,
        );
        if ($this->material2->id != "null") {
            $materials[] = $this->material2;
        }

        return $materials;
    }

    public function getHasFlags()
    {
        return count($this->flags) > 0;
    }

    public function getHasTechniques()
    {
        if (is_array($this->techniques)) {
            return count($this->techniques) > 0;
        }
        if (is_string($this->techniques)) {
            return true;
        }

        return false;
    }

    public function getDamage()
    {
        if ($this->data->damage !== null) {
            if (is_object($this->data->damage)) {
                $strval = '';
                if (isset($this->data->damage->amount)) {
                    $strval = $this->data->damage->amount;
                }
                if (isset($this->data->damage->constant_damage_multiplier)) {
                    $strval .= 'x'.$this->data->damage->constant_damage_multiplier;
                }
                if (isset($this->data->damage->damage_type)) {
                    $strval.=" (".$this->data->damage->damage_type.")";
                }

                return $strval;
            } else {
                return $this->data->damage;
            }
        }
    }

    public function getDamagePerMove()
    {
        if (!$this->movesPerAttack) {
            return 0;
        }

        return number_format(($this->bashing + $this->cutting + $this->piercing) / ($this->movesPerAttack / 100.0), 2, ".", "");
    }

    public function getIsModdable()
    {
        if (isset($this->data->valid_mod_locations)) {
            if (is_array($this->data->valid_mod_locations)) {
                return count($this->data->valid_mod_locations) > 0;
            } else {
                return true;
            }
        }
        return false;
    }

    public function getIsBrewable()
    {
        return isset($this->data->brewable);
    }

    public function getBrewable()
    {
        $brewtime = $this->data->brewable->time;

        $brewresults = array();
        foreach ($this->data->brewable->results as $output) {
            $brewitem = $this->repo->getModel("Item", $output);
            $brewresults[] = '<a href="'.route("item.view", array("id" => $brewitem->id)).'">'.$brewitem->name.'</a>';
        }

        $brewproducts = implode(", ", $brewresults);

        return "Fermenting this item for ".$brewtime." produces ".$brewproducts.".";
    }

    public function getIsGunMod()
    {
        return $this->type == "GUNMOD";
    }

    public function getIsContainer()
    {
        return $this->type == "CONTAINER";
    }

    public function getModGuns()
    {
        return $this->repo->allModels("Item", "gunmodGuns.{$this->data->id}");
    }

    public function getId()
    {
        return $this->data->id;
    }

    public function getCovers()
    {
        return array_map(function ($cover) {
            return strtolower($cover);
        }, isset($this->data->covers) ? $this->data->covers : []);
    }

    public function getContains()
    {
        return $this->data->contains;
    }

    public function getConstructionUses()
    {
        return $this->repo->allModels('Construction', "construction.{$this->data->id}");
    }

    public function getSourcePart()
    {
        return $this->repo->getModel("Item", $this->data->item);
    }

    public function getEncumbrance()
    {
        $result = 0;
        if (!isset($this->data->encumbrance)) {
            return 0;
        }
        if (is_numeric($this->data->encumbrance) && $this->data->encumbrance > 0) {
            $result = "";
            $foundvarsize = false;
            $enc = $this->data->encumbrance;
            // not sure why index number contains the flag values
            foreach ($this->data->flags as $indexnum => $flag) {
                if (!is_array($indexnum) && $indexnum == "VARSIZE") {
                    $foundvarsize = true;
                }
            }
            if ($foundvarsize == true) {
                $result = $enc." (poor fit), ".max(floor($enc / 2), $enc - 10)." (fitted)";
            } else {
                $result = $enc;
            }
        }

        if ($this->data->max_encumbrance > 0) {
            $result = $result." - ".$this->data->max_encumbrance." (0% - 100% total character volume)";
        }

        return $result;
    }

    public function getRangedDamage()
    {
        $inner = array();
        if (!isset($this->data->ranged_damage)) {
            return 0;
        }
        if (isset($this->data->ranged_damage_type)) {
            return $this->data->ranged_damage." (".$this->data->ranged_damage_type.")";
        }
        if (!is_numeric($this->data->ranged_damage)) {
            if (is_array($this->data->ranged_damage)) {
                foreach ($this->data->ranged_damage as $indexnum => $damageunit) {
                    $inner[] = (is_numeric($damageunit->amount) ? $damageunit->amount : "").(isset($damageunit->damage_type) ? " (".$damageunit->damage_type.")" : "").(isset($damageunit->armor_multiplier) ? " (Armor multiplier $damageunit->armor_multiplier)" : "");
                }

                return implode(", ", $inner);
            } elseif (is_object($this->data->ranged_damage)) {
                $rd = $this->data->ranged_damage;

                return (is_numeric($rd->amount) ? $rd->amount : "").(isset($rd->damage_type) ? " (".$rd->damage_type.")" : "").(isset($rd->armor_multiplier) ? " (Armor multiplier $rd->armor_multiplier)" : "");
            }
        }

        return $this->data->ranged_damage;
    }

    public function getDescription()
    {
        if (!isset($this->data->description)) {
            return "";
        }
        if (is_object($this->data->description)) {
            return $this->data->description->str;
        }
        return $this->data->description;
    }
}
