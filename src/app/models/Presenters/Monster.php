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
        $trans = array(
            "aberration" => "畸变体",
            "amphibian" => "两栖动物",
            "bird" => "鸟",
            "blob" => "变形怪",
            "demon_spider" => "恶魔蜘蛛",
            "dinosaur" => "恐龙",
            "dragon" => "龙",
            "fish" => "鱼",
            "fungus" => "真菌",
            "hallucination" => "幻象",
            "horror" => "恐怖",
            "human" => "人类",
            "insect" => "昆虫",
            "magical_beast" => "魔法怪兽",
            "mammal" => "哺乳动物",
            "mollusk" => "软体动物",
            "mutant" => "变种人",
            "nether" => "神话生物",
            "plant" => "植物",
            "reptile" => "爬虫",
            "robot" => "机器人",
            "spider" => "蜘蛛",
            "unknown" => "未知",
            "worm" => "蠕虫",
            "zombie" => "丧尸",
            "none" => "无",
        );
        $links = array_map(function ($species) use ($trans) {
            return link_to_route('monster.species', $trans[strtolower($species)], array($species));
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

    public function presentDifficulty()
    {
        $diff = $this->object->difficulty;
        if ($diff < 3) {
            $strvalue = '<font color="lightgray">极低威胁</font>';
        } else if ($diff < 10) {
            $strvalue = '<font color="lightgray">低威胁</font>';
        } else if ($diff < 20) {
            $strvalue = '<font color="LightCoral">中威胁</font>';
        } else if ($diff < 30) {
            $strvalue = '<font color="LightCoral">高威胁</font>';
        } else if ($diff < 50) {
            $strvalue = '<font color="red">极高威胁</font>';
        } else {
            $strvalue = '<font color="red">致命威胁</font>';
        }
        $diff = round($diff);
        return "$diff ($strvalue)";
    }
}
