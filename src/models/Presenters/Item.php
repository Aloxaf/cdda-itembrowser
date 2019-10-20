<?php

namespace Presenters;

class Item extends \Robbo\Presenter\Presenter
{
    public function presentSymbol()
    {
        $symbol = $this->object->symbol;
        if ($symbol == " ") {
            return "&nbsp;";
        }

        return "<span style=\"color: $this->color\">".htmlspecialchars($symbol)."</span>";
    }

    public function presentRawName()
    {
        return ucfirst($this->object->name);
    }

    public function presentVolume()
    {
        return $this->object->volume === null ? "N/A" : $this->object->volume;
    }

    public function presentStorage()
    {
        $storage = $this->object->storage;
        if (stripos($storage, "ml")) {
            return (floatval($storage) / 1000.0)." L";
        }
        
        if (strpos($storage, "L")) {
            return (floatval($storage)* 1.0)." L";
        }

        return (floatval($storage) / 4.0)." L";
    }

    public function presentWeight()
    {
        $weight = $this->object->weight;
        if ($weight === null) {
            return;
        }

        return number_format($weight / 453.6, 2);
    }

    public function presentWeightMetric()
    {
        $weight = $this->object->weight;
        if ($weight === null) {
            return;
        }

        return number_format($weight / 1000, 2);
    }

    public function presentBashing()
    {
        return $this->object->bashing ?: "0";
    }

    public function presentCutting()
    {
        return $this->object->cutting ?: "0";
    }

    public function presentToHit()
    {
        return $this->object->to_hit ?: "N/A";
    }

    public function presentMovesPerAttack()
    {
        return $this->object->moves_per_attack ?: "N/A";
    }

    public function presentRecipes()
    {
        return array_map(function ($recipe) {
            return $recipe->getPresenter();
        }, $this->object->recipes);
    }

    public function presentDisassembly()
    {
        return array_map(function ($recipe) {
            return $recipe->getPresenter();
        }, $this->object->disassembly);
    }

    public function presentMaterials()
    {
        return implode(", ", array_map(function ($material) {
            return '<a href="'.route("item.materials", $material->ident).'">'.$material->name.'</a>';
        }, $this->object->materials));
    }

    public function presentFlags()
    {
        $invert = array_flip($this->object->flags);

        if (empty($invert)) {
            return "None";
        }

        return implode(", ", array_map(function ($flag) {
            return '<a href="'.route("item.flags", $flag).'">'.$flag.'</a>';
        }, $invert));
    }

    public function presentFeatureLabels()
    {
        $badges = array();
        if ($this->count("toolFor")) {
            $badges[] = '<a href="'.route("item.recipes", $this->object->id).'"><span class="label label-success">recipes: '.$this->count("toolFor").'</span></a>';
        }
        if ($this->count("disassembly")) {
            $badges[] = '<a href="'.route("item.disassemble", $this->object->id).'"><span class="label label-info">disassemble</span></a>';
        }
        if ($this->count("recipes")) {
            $badges[] = '<a href="'.route("item.craft", $this->object->id).'"><span class="label label-default">craft: '.$this->count("recipes").'</span></a>';
        }
        if ($this->count("construction")) {
            $badges[] = '<a href="'.route("item.construction", $this->object->id).'"><span class="label label-warning">construction: '.$this->count("construction").'</span></a>';
        }
        if ($this->count("uncraftToolFor")) {
            $badges[] = '<span class="label label-warning">item disassembly: '.$this->count("uncraftToolFor").'</span>';
        }
        if ($this->object->modspace != "_dda_" && $this->object->modspace != "") {
            $badges[] = '<span class="label label-warning">mod</span>';
        }
        if ($this->object->override == true) {
            $badges[] = '<span class="label label-warning">overrides base item</span>';
        }
        if (is_string($this->object->abstract)) {
            $badges[] = '<span class="label label-danger">abstract JSON</span>';
        }

        return implode(" ", $badges);
    }

    public function presentModLabel()
    {
        $badges = array();
        if ($this->object->modspace != "") {
            $badges[] = '<span class="label label-warning">'.$this->object->modfoldername.'</span>';
        }

        return implode(" ", $badges);
    }

    public function presentCraftingRecipes()
    {
        $recipes = array();
        foreach ($this->object->learn as $recipe) {
            $recipes[] = '<a href="'.route('item.view', $recipe->result->id).'">'.$recipe->result->name.'</a>';
        }

        return implode(", ", $recipes);
    }

    public function presentCovers()
    {
        if (!$this->object->covers) {
            return "none";
        }

        return implode(", ", array_map(function ($cover) {
            return '<a href="'.route('item.armors', $cover).'">'.$cover.'</a>';
        }, $this->object->covers));
    }

    public function presentSpoilsIn()
    {
        if (is_numeric($this->object->spoils_in)) {
            if ($this->object->spoils_in < 24) {
                return $this->object->spoils_in." hours";
            }

            $weeks = 0;
            $days = floor($this->object->spoils_in / 24);
            $hours = $this->object->spoils_in % 24;
            while ($days > 6) {
                $weeks++;
                $days -= 7;
            }
            $result = "";
            if ($weeks > 0) {
                $result = $result."$weeks weeks ";
            }
            if ($days > 0) {
                $result = $result."$days days ";
            }
            if ($hours > 0) {
                $result = $result."$hours hours ";
            }
//            return ($this->object->spoils_in / 24)." days";
            return $result;
        }

        return $this->object->spoils_in;
    }

    public function presentStim()
    {
        return ($this->object->stim * 5)." mins";
    }

    public function presentValidModLocations()
    {
        $ret = array();
        $parts = $this->object->valid_mod_locations;
        foreach ($parts as $part) {
            $ret[] = "$part[1] ".'<a href="'.route("item.gunmods", array($this->object->skill, $part[0])).'">'.$part[0].'</a>';
        }

        return implode("; ", $ret);
    }

    public function presentModSkills()
    {
        $ret = array();
        foreach ($this->mod_targets as $target) {
            $ret[] = '<a href="'.route("item.guns", $target, $target).'">'.$target.'</a>';
        }

        return implode(", ", $ret);
    }

    public function presentClipSizeModifier()
    {
        return sprintf("%+d", $this->object->clip_size_modifier);
    }

    public function presentDamageModifier()
    {
        return sprintf("%+d", $this->object->damage_modifier);
    }

    public function presentBurstModifier()
    {
        return sprintf("%+d", $this->object->burst_modifier);
    }

    public function presentRecoilModifier()
    {
        return sprintf("%+d", $this->object->recoil_modifier);
    }

    public function presentRigid()
    {
        return $this->object->rigid ? "R" : "";
    }

    public function presentSeals()
    {
        return $this->object->seals ? "S" : "";
    }

    public function presentWatertight()
    {
        return $this->object->watertight ? "W" : "";
    }

    public function presentPreserves()
    {
        return $this->object->preserves ? "P" : "";
    }

    public function presentTechniques()
    {
        $techs = (array) $this->object->techniques;
        if (empty($techs)) {
            return "";
        }

        return implode(", ", $techs);
    }

    public function presentSourcePart()
    {
        if ($this->object->item === null) {
            return "(unknown)";
        }
        $sourcepart = $this->object->sourcepart;

        return '<a href="'.route("item.view", array("id" => $sourcepart->id)).'">'.$sourcepart->name.'</a>';
    }
}
