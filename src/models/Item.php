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
        if (!isset($data->material)) {
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

    public function getRawName()
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

    public function getDeconstructFrom()
    {
        return $this->repo->allModels("Furniture", "item.deconstructFrom.$this->id");
    }

    public function getBashFromTerrain()
    {
        return $this->repo->allModels("Terrain", "item.bashFromTerrain.$this->id");
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

    public function getIsBionic()
    {
        return $this->data->type == "bionic";
    }

    public function getIsBionicItem()
    {
        return $this->data->type == "BIONIC_ITEM";
    }

    public function getDifficulty()
    {
        return isset($this->data->difficulty) ? $this->data->difficulty : 0;
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
        if ($mat2 == "null" || $mat2->ident == "null") {
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
        $material1 = $this->material1->ident;
        $material2 = $this->material2->ident;

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
                "item" => $this->repo->getModel("Item", $this->cut_pairs[$material->ident]),
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

        return stristr($this->id, $text) ||
            stristr($this->name, $text);
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
            return "自体光环";
        } elseif (isset($this->data->flags["SKINTIGHT"])) {
            return "贴身";
        } elseif (isset($this->data->flags["WAIST"])) {
            return "腰部";
        } elseif (isset($this->data->flags["OUTER"])) {
            return "外套";
        } elseif (isset($this->data->flags["BELTED"])) {
            return "背系";
        } elseif (isset($this->data->flags["AURA"])) {
            return "外部光环";
        } else {
            return "普通";
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
        if ($this->material2->ident != "null") {
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
                    $strval.=" (".gettext("damage type\004{$this->data->damage->damage_type}").")";
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

        $brewresults = array("* 一旦在瓮中设置，将会需要约 $brewtime 小时来发酵");
        foreach ($this->data->brewable->results as $output) {
            $brewitem = $this->repo->getModel("Item", $output);
            $brewresults[] = '* 这件物品发酵后将产出 <a href="'.route("item.view", array("id" => $brewitem->id)).'">'.$brewitem->name.'</a>';
        }

        $brewproducts = implode("<br>", $brewresults);

        return $brewproducts;
    }

    public function getIsGunMod()
    {
        return $this->type == "GUNMOD";
    }

    public function getIsContainer()
    {
        return $this->type == "CONTAINER";
    }

    public function getIsPetArmor()
    {
        return $this->type == "PET_ARMOR" || isset($this->pet_armor_data);
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
            return strtolower(gettext($cover));
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
                $result = "<yellow>".$enc."</yellow> (不合身), <yellow>".max(floor($enc / 2), $enc - 10)."</yellow> (合身)";
            } else {
                $result = $enc;
            }
        }

        if ($this->data->max_encumbrance > 0) {
            $result = $result." - ".$this->data->max_encumbrance." (0% - 100% 体积)";
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
            return $this->data->ranged_damage." (".gettext("damage type\004{$this->data->ranged_damage_type}").")";
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

    public function getModName()
    {
        if (isset($this->data->modname)) {
            $ident = $this->data->modname;
            return $this->repo->raw("modname.$ident");
        }
    }

    public function getUsedby()
    {
        if (!isset($this->data->ammo_type)) {
            return array();
        }
        $guns = $this->repo->allModels("Item", "ammo.{$this->data->ammo_type}.usedby");
        return $guns;
    }

    public function getName()
    {
        $name = $this->repo->raw("item_multi.name.$this->id");
        if ($name) {
            return implode(" / ", $name);
        } else {
            return $this->rawname;
        }
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

    public function getJson()
    {
        return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function getTechniques()
    {
        if (isset($this->data->techniques)) {
            return array_map(
                function($id) {
                    $model = $this->repo->getMultiModelOrFail("Item", $id);
                    return "<stat>".$model[0]->name."</stat>：<info>".$model[0]->description."</info>";
                },
                $this->data->techniques
            );
        }
    }

    public function getAmmoModifier()
    {
        if (!isset($this->data->ammo_modifier)) {
            return;
        }
        $ammo_modifier = $this->data->ammo_modifier;
        if (!is_array($ammo_modifier)) {
            $ammo_modifier = array($ammo_modifier);
        }
        if (isset($this->data->ammo_modifier)) {
            return array_map(
                function ($id) {
                    $model = $this->repo->getModel("Item", $id);
                    return "<a href=\"".route("item.view", $id)."\">".$model->name."</a>";
                },
                $ammo_modifier
            );
        }
    }

    public function getModSkills()
    {
        if (isset($this->data->mod_targets)) {
            return implode(",", array_map(
                function ($id) {
                    try {
                        $item = $this->repo->getModelOrFail("Item", $id);
                        return '<a href="'.route("item.view", $id, $item->name).'">'.$item->name.'</a>';
                    } catch (\Exception $e) {
                        return '<a href="'.route("item.guns", $id, $id).'">'.$id.'</a>';
                    }
                },
                $this->data->mod_targets
            ));
        }
    }

    public function getDropFrom()
    {
        return array_map(
            function ($id) {
                try {
                    return $this->repo->getModel("Monster", $id);
                } catch (\Exception $e) {
                    return $this->repo->getModel("ItemGroup", $id);
                }
            },
            $this->repo->raw("item.dropfrom.$this->id")
        );
    }

    public function getHarvestFrom()
    {
        return array_map(
            function ($id) {
                return $this->repo->getModel("ItemGroup", $id);
            },
            $this->repo->raw("item.harvestfrom.$this->id")
        );
    }

    public function getbodyparts($data)
    {
        $trans = array(
            "TORSO" => "躯干",
            "HEAD" => "头部",
            "EYES" => "眼部",
            "MOUTH" => "嘴部",
            "ARM_L" => "左臂",
            "ARM_R" => "右臂",
            "HAND_L" => "左手",
            "HAND_R" => "右手",
            "LEG_L" => "左腿",
            "LEG_R" => "右腿",
            "FOOT_L" => "左脚",
            "FOOT_R" => "右脚",
        );
        if (isset($this->data->{$data})) {
            return implode(",", array_map(
                function($t) use($trans) {
                    $idx = strtoupper($t[0]);
                    return "{$trans[$idx]}（<yellow>{$t[1]}</yellow>）";
                },
                $this->data->{$data}
            ));
        }
    }

    public function getFuelOptions()
    {
        if (isset($this->data->fuel_options)) {
            return implode(",", array_map(
                function ($id) {
                    $model = $this->repo->getModel("Item", $id);
                    return '<a href="'.route("item.view", $id).'">'.$model->name.'</a>';
                },
                $this->data->fuel_options
            ));
        }
    }

    public function getFlagDescriptions()
    {
        if (!is_array($this->data->flags)) {
            return "";
        }
        $trans = array(
            "DIMENSIONAL_ANCHOR" => "这件装备能 <good>稳定</good> 你周围的空间。",
            "PSYSHIELD_PARTIAL" => "这件装备能 <good>部分防护</good> 你 <info>免受精神攻击</info>。"
        );
        $ret = array();
        foreach ($this->data->flags as $flag => $v) {
            try {
                $raw = $this->repo->getMultiModelOrFail("Item", $flag);
                // echo "item.$flag".var_dump($raw[0]->data);
                if (isset($raw[0]->data->info)) {
                    $ret[] = "* ".$raw[0]->data->info."<br>";
                }
            } catch (\Exception $e) {
                if (array_key_exists($flag, $trans)) {
                    $ret[] = "* ".$trans[$flag]."<br>";
                }
            }
        }
        return implode("", $ret);
    }

    public function getFakeItem()
    {
        if(isset($this->data->fake_item)) {
            return $this->repo->getModel("Item", $this->data->fake_item);
        }
    }

    public function getBreaksInto()
    {
        if(isset($this->data->breaks_into)) {
            return array_map(
                function($item) {
                    return $this->repo->getModel("Item", $item->item);
                },
                $this->data->breaks_into
            );
        }
    }

    public function getVitamins()
    {
        if(isset($this->data->vitamins)) {
            return implode("，", array_map(
                function($id) {
                    $model = $this->repo->getModel("Item", $id[0]);
                    return "{$model->name}（<yellow>{$id[1]}</yellow>%）";
                },
                $this->data->vitamins
            ));
        }
    }

    public function hasKey($key)
    {
        return isset($this->data->{$key});
    }

    public function effective_dps($mon)
    {
        $hits_by_accuracy = array(
            0,    1,   2,   3,   7, // -20 to -16
            13,   26,  47,   82,  139, // -15 to -11
            228,   359,  548,  808, 1151, // -10 to -6
            1587, 2119, 2743, 3446, 4207, // -5 to -1
            5000,  // 0
            5793, 6554, 7257, 7881, 8413, // 1 to 5
            8849, 9192, 9452, 9641, 9772, // 6 to 10
            9861, 9918, 9953, 9974, 9987, // 11 to 15
            9993, 9997, 9998, 9999, 10000 // 16 to 20
        );
        $mon_dodge = $mon->dodge;
        $base_hit = 8 / 4 + (4 / 3) + (4 / 2) + $this->data->to_hit;
        $base_hit *= max(0.25, 1 - 20 / 100.0);
        $mon_defense = $mon->dodge + 0 / 5.0;
        $hit_trials = 10000.0;
        $rng_mean = max(min(intval($base_hit - $mon_defense), 20), -20) + 20;

        $num_all_hits = $hits_by_accuracy[$rng_mean];
        $rng_high_mean = max(min(intval($base_hit - 1.5 * $mon->dodge), 20), -20) + 20;
        $rng_high_hits = $hits_by_accuracy[$rng_high_mean] * $num_all_hits / $hit_trials;
    }
}
