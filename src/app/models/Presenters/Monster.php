<?php

namespace Presenters;

class Monster extends \Robbo\Presenter\Presenter
{
    public function presentSymbol()
    {
        list($fg, $bg) = colorPairToCSS($this->object->color);

        return sprintf("<span style=\"color: %s; background: %s\">%s</span>",
            $fg, $bg,
            $this->object->symbol);
    }

    public function presentNiceName()
    {
        return ucfirst($this->object->name);
    }

    public function presentFlags()
    {
        return implode(", ", $this->object->flags);
    }

    public function presentDeathFunction()
    {
        $death = (array) $this->object->death_function;
        if (empty($death)) {
            return "";
        }

        return implode(", ", $death);
    }

    public function presentSpecialAttacks()
    {
        $attacks = (array) $this->object->special_attacks;
        if (empty($attacks)) {
            return "";
        }

        array_walk($attacks, function (&$attack) {
            if (isset($attack->type)) {
                $attack = "$attack->type: $attack->cooldown";
            } elseif (isset($attack->id)) {
                if (isset($attack->damage_max_instance)) {
                    $counter = 0;
                    $attackstr = "$attack->id: ";
                    $attackarray = [];
                    foreach ($attack->damage_max_instance as $inst) {
                        $attackarray[] = "($inst->damage_type for $inst->amount damage)";
                    }
                    $attackstr = $attackstr.implode(" ", $attackarray);
                    $attack = $attackstr;
                } else {
                    $attack = "$attack->id";
                }
            } else {
                $attack = "$attack[0]: $attack[1]";
            }
        });

        return implode(",<br>", $attacks);
    }

    public function presentModinfo()
    {
        if ($this->object->modspace != "_dda_" && $this->object->modspace != "") {
            return '<span class="label label-warning">'.$this->object->modfoldername.'</span>';
        }

        return "";
    }

    public function presentSpecies()
    {
        $links = array_map(function ($species) {
            return link_to_route('monster.species', $species, array($species));
        }, $this->object->species);

        return implode(", ", $links);
    }

    public function presentDamage()
    {
        return "{$this->melee_dice}d{$this->melee_dice_sides}+{$this->melee_cut}";
    }

    public function presentDescription()
    {
        return preg_replace("/\\n/", "<br>", htmlspecialchars($this->object->description));
    }

    public function presentAvgDamage()
    {
        return number_format($this->object->avgDamage, 2);
    }

    public function presentSpecialWhenHit()
    {
        if (!($this->object->special_when_hit)) {
            return "";
        }

        return $this->object->special_when_hit[0]." (".$this->object->special_when_hit[1].")";
    }

    public function presentSize()
    {
        if(stripos($this->object->volume, "ml"))
        {
            $value = $this->object->volume * 1.0;
            $strvalue = "";
            if($value <= 7500)
            {
                $strvalue = "很小";
            }else if ($value <= 46250)
            {
                $strvalue = "小";
            }else if ($value <= 77500)
            {
                $strvalue = "中等";
            }else if ($value <= 483750)
            {
                $strvalue = "大";
            }else
            {
                $strvalue = "巨大";
            }
            return $strvalue." (".$this->object->volume.")";
        }
        else
        {
            return $this->object->size;
        }
    }
}
