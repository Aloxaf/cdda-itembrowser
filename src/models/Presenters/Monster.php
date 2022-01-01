<?php

namespace Presenters;

class Monster extends \Robbo\Presenter\Presenter
{
    public function presentSymbol()
    {
        list($fg, $bg) = HelperCss::colorPairToCSS($this->object->color);

        return sprintf(
            "<span style=\"color: %s; background: %s\">%s</span>",
            $fg,
            $bg,
            $this->object->symbol
        );
    }

    public function presentNiceName()
    {
        return ucfirst($this->object->name);
    }

    public function presentFlags()
    {
        // 参见 mtypre.h
        $expl = array(
            "ABSORBS" => "移动时会<info>吞噬物品</info>并<bad>恢复血量</bad>",
            "ABSORBS_SPLITS" => "移动时会<info>吞噬物品</info>并<bad>恢复血量</bad>，吞噬量足够时会<bad>分裂</bad>",
            "ACIDPROOF" => "免疫<bad>酸性伤害</bad>",
            "ACIDTRAIL" => "留下<bad>带有酸液的足迹</bad>",
            "ACID_BLOOD" => "拥有<info>酸性血液</info>",
            "ANIMAL" => "是<info>动物</info>",
            "AQUATIC" => "是<info>水生生物</info>",
            "ARTHROPOD_BLOOD" => "拥有<info>淋巴血液</info>",
            "ATTACKMON" => "会<info>攻击其他怪物</info>",
            "ATTACK_LOWER" => "<good>无法击中上肢</good>",
            "ATTACK_UPPER" => "<bad>可以击中上肢</bad>",
            "BADVENOM" => "的攻击可能使你<bad>严重中毒</bad>",
            "BASHES" => "会<bad>破坏门</bad>",
            "BILE_BLOOD" => "拥有<info>胆汁血液</info>",
            "BIRDFOOD" => "可以用鸟食<good>驯服</good>",
            "BONES" => "屠宰时可能得到<info>骨头和肌腱</info>",
            "BORES" => "挖掘时能<info>破坏任何东西</info>",
            "CANPLAY" => "在成为宠物后可以和它<good>一起玩</good>",
            "CAN_DIG" => "可以<info>掘进</info>",
            "CAN_OPEN_DOORS" => "会<bad>开门</bad>",
            "CATFOOD" => "可以用猫粮<good>驯服</good>",
            "CATTLEFODDER" => "可以用家畜饲料<good>驯服</good>",
            "CBM_CIV" => "屠宰时可能得到<info>常见 CBM</info>或<info>储能 CBM</info>",
            "CBM_OP" => "屠宰时可能得到<info>bionics_op 内的 CBM</info>",
            "CBM_POWER" => "屠宰时可能得到<info>储能 CBM</info>",
            "CBM_SCI" => "屠宰时可能得到<info>bionics_sci 内的 CBM</info>",
            "CBM_SUBS" => "屠宰时可能得到<info>bionics_subs 内的 CBM</info>",
            "CBM_TECH" => "屠宰时可能得到<info>bionics_tech 内的 CBM</info>",
            "CHITIN" => "屠宰时可能得到<info>几丁质</info>",
            "CLIMBS" => "会<bad>攀爬</bad>",
            "COLDPROOF" => "<bad>免疫低温伤害</bad>",
            "CONSOLE_DESPAWN" => "在附近的控制台被正确入侵时<good>会消失</good>",
            "DESTROYS" => "会<bad>破坏墙壁</bad>及其他地形",
            "DIGS" => "在地下挖掘",
            "DOGFOOD" => "可以用狗粮<good>驯服</good>",
            "DRIPS_GASOLINE" => "移动时偶尔会<info>滴下汽油</info>",
            "DRIPS_NAPALM" => "移动时偶尔会<info>滴下凝固汽油</info>",
            "DROPS_AMMO" => "<good>掉落子弹</good>",
            "ELECTRIC" => "<bad>浑身带电</bad>",
            "ELECTRIC_FIELD" => "会向周围区域<info>放电</info>",
            "ELECTRONIC" => "是<info>电子产品</info>",
            "FAT" => "屠宰时可能得到<info>脂肪</info>",
            "FILTHY" => "掉落的衣物永远是<bad>肮脏的</bad>",
            "FIREPROOF" => "<bad>免疫火焰</bad>",
            "FIREY" => "在燃烧并且<bad>免疫火焰</bad>",
            "FISHABLE" => "可以被<info>钓上来</info>",
            "FLAMMABLE" => "可以被<good>点燃</good>",
            "FLIES" => "<info>会飞</info>",
            "FUR" => "屠宰时可能得到<info>毛皮</info>",
            "GOODHEARING" => "拥有<bad>敏锐的听觉</bad>",
            "GRABS" => "攻击时可能<bad>抓住你</bad>",
            "GROUP_BASH" => "在破坏门时会得到周围怪物的<bad>协助</bad>",
            "GROUP_MORALE" => "当周围有同伴时会<bad>更勇敢</bad>",
            "GUILT" => "被杀死后会让你<bad>感到内疚</bad>",
            "HARDTOSHOOT" => "<bad>不易</bad>被远程攻击命中",
            "HEARS" => "<bad>拥有听觉</bad>",
            "HIT_AND_RUN" => "完成一次攻击后会<info>迅速逃开</info>",
            "HUMAN" => "是<info>人类</info>",
            "ID_CARD_DESPAWN" => "在附近的控制台被插入科学家 ID 卡以后<good>会消失</good>",
            "IMMOBILE" => "<good>不会移动</good>",
            "INSECTICIDEPROOF" => "<bad>对杀虫剂免疫</bad>",
            "INTERIOR_AMMO" => "不会掉落子弹",
            "KEENNOSE" => "拥有<bad>敏锐的嗅觉</bad>",
            "KEEP_DISTANCE" => "会与当前目标<info>保持距离</info>",
            "LARVA" => "是幼虫",
            "LEATHER" => "屠宰时可能得到<info>皮革</info>",
            "LOUDMOVES" => "移动时会发出<info>巨大的噪音</info>",
            "MECH_DEFENSIVE" => "可以在驾驶时彻底保护你",
            "MECH_RECON_VISION" => "驾驶时能提供<good>夜视</good>和增强的<good>大地图视野</good>",
            "MILITARY_MECH" => "是<info>军用机甲</info>",
            "MILKABLE" => "<good>会产奶</good>",
            "NEMESIS" => "是一个复仇怪物",
            "NIGHT_INVISIBILITY" => "在黑暗中<bad>隐形</bad>",
            "NOGIB" => "被超量伤害杀死时<info>不会爆成碎块</info>",
            "NOHEAD" => "<bad>没有脑袋</bad>",
            "NOT_HALLUCINATION" => "<info>不会成为幻觉</info>",
            "NO_BREATHE" => "<bad>不需要呼吸</bad>",
            "NO_BREED" => "<info>不会繁殖</info>",
            "NO_FUNG_DMG" => "<info>免疫真菌</info>",
            "NO_NECRO" => "<good>无法被死灵魔法复活</good>",
            "PACIFIST" => "<good>不会进行近战攻击</good>",
            "PARALYZEVENOM" => "攻击时可能<bad>使你麻痹</bad>",
            "PATH_AVOID_DANGER_1" => "行动时会<bad>规避危险</bad>",
            "PATH_AVOID_DANGER_2" => "行动时会<bad>规避危险</bad>",
            "PATH_AVOID_FALL" => "行动时会<info>绕开悬崖</info>",
            "PATH_AVOID_FIRE" => "行动时会<info>绕开火焰</info>",
            "PAY_BOT" => "可以通过支付金钱来短暂地<good>成为伙伴</good>",
            "PET_HARNESSABLE" => "可以<good>装备挽具</good>",
            "PET_MOUNTABLE" => "可以<good>骑乘</good>或<good>装备挽具</good>",
            "PET_WONT_FOLLOW" => "被驯服之后<info>不会跟着你</info>",
            "PLASTIC" => "拥有<bad>物理伤害减免</bad>",
            "POISON" => "吃起来<bad>有毒</bad>",
            "PRIORITIZE_TARGETS" => "会依据威胁程度处理目标",
            "PUSH_MON" => "会<info>推开道路上的其他怪物</info>",
            "PUSH_VEH" => "会<info>推开道路上的载具</info>",
            "QUEEN" => "的死亡会<good>导致整个种群死亡</good>",
            "RANGED_ATTACKER" => "拥有<bad>远程攻击</bad>手段",
            "REVIVES" => "<bad>会复活</bad>",
            "REVIVES_HEALTHY" => "复活时会<bad>恢复状态</bad>",
            "RIDEABLE_MECH" => "是一件<good>可以驾驶的机甲</good>",
            "SEES" => "<bad>拥有视觉</bad>",
            "SHEARABLE" => "可以被剪羊毛",
            "SHORTACIDTRAIL" => "移动时会留下<bad>酸液痕迹</bad>",
            "SLUDGEPROOF" => "<bad>不受污泥痕影响</bad>",
            "SLUDGETRAIL" => "移动时会<bad>留下污泥痕</bad>",
            "SMELLS" => "<bad>拥有嗅觉</bad>",
            "STUMBLES" => "行动时<good>会跌到</good>",
            "STUN_IMMUNE" => "<bad>免疫眩晕</bad>",
            "SUNDEATH" => "会在<good>阳光下死亡</good>",
            "SWARMS" => "会与其他同伴<info>聚集在一起</info>",
            "SWIMS" => "<bad>会游泳</bad>",
            "VENOM" => "的攻击可能使你<bad>中毒</bad>",
            "WARM" => "是<info>温血生物</info>",
            "WATER_CAMOUFLAGE" => "在水下<bad>难以被发现</bad>，尤其是你不在水下时",
            "WEBWALK" => "可以在<info>蛛网上行走</info>",
            "WOOL" => "屠宰时可能得到<info>木头</info>",
        );
        $invert = $this->object->flags;

        if (empty($invert)) {
            return [];
        }

        $ret = [];
        foreach ($invert as $flag) {
            $ret[] = array($flag, $expl[$flag] ?? "待补充");
        }
        return $ret;
    }


    public function presentDeathFunction()
    {
        $death = $this->object->death_function;
        if (empty($death)) {
            return "";
        }

        if (is_object($death)) {
            if (isset($death->effect)) {
                $death_type = $death->effect->id;
            } else {
                $death_type = $death->corpse_type;
            }
            $message = $death->message ?? "";
            return "<a title=\"{$message}\">{$death_type}</a>";
        } else {
            return implode(", ", $death);
        }
    }

    public function presentSpecialAttacks()
    {
        $attacks = (array) $this->object->special_attacks;
        if (empty($attacks)) {
            return "";
        }

        array_walk($attacks, function (&$attack) {
            if (isset($attack->type)) {
                $attackstr = "<a href=\"https://cdda-wiki.aloxaf.com/怪物：攻击#{$attack->type}\">{$attack->type}</a>";
                if (isset($attack->cooldown)) {
                    $attackstr = $attackstr." / 冷却：$attack->cooldown";
                }
                if (isset($attack->max_range)) {
                    $attackstr = $attackstr." / 范围：$attack->max_range";
                }
                $attack = $attackstr;
            } elseif (isset($attack->id)) {
                if (isset($attack->damage_max_instance)) {
                    $counter = 0;
                    $attackstr = "<a href=\"https://cdda-wiki.aloxaf.com/wiki/怪物：攻击#{$attack->id}\">{$attack->id}</a>: ";
                    $attackarray = [];
                    foreach ($attack->damage_max_instance as $inst) {
                        $attackarray[] = "($inst->amount 点".$inst->damage_type."伤害)";
                    }
                    $attackstr = $attackstr.implode(" ", $attackarray);
                    $attack = $attackstr;
                } else {
                    $attack = "<a href=\"https://cdda-wiki.aloxaf.com/wiki/怪物：攻击#{$attack->id}\">{$attack->id}</a>";
                }
            } else {
                $attack = "<a href=\"https://cdda-wiki.aloxaf.com/wiki/怪物：攻击#{$attack[0]}\">{$attack[0]}</a> / 冷却：$attack[1]";
            }
        });

        return implode(",<br>", $attacks);
    }

    public function presentModinfo()
    {
        if ($this->modname != "_dda_" && $this->modname != "") {
            return '<span class="label label-warning">'.$this->modname.'</span>';
        }

        return "";
    }

    public function presentSpecies()
    {
        // TODO: 和 species.blade.php 页面的翻译整合到一起
        $trans = array(
            "aberration" => "畸变体",
            "amphibian" => "两栖动物",
            "alien" => "外星人",
            "biocrystal" => "晶体生物",
            "bird" => "鸟",
            "blob" => "变形怪",
            "cracker" => "饼干",
            "chewgum" => "口香糖",
            "cookie" => "曲奇饼",
            "cyborg" => "生化人",
            "demon_spider" => "恶魔蜘蛛",
            "dinosaur" => "恐龙",
            "dragon" => "龙",
            "fish" => "鱼",
            "fungus" => "真菌",
            "goblin" => "哥布林",
            "gummy" => "软糖",
            "hallucination" => "幻象",
            "horror" => "恐怖",
            "human" => "人类",
            "insect" => "昆虫",
            "insect_flying" => "飞虫",
            "leech_plant" => "水蛭花",
            "licorice" => "甘草",
            "lizardfolk" => "蜥蜴人",
            "magical_beast" => "魔法巨兽",
            "mammal" => "哺乳动物",
            "marshmallow" => "棉花糖",
            "mollusk" => "软体动物",
            "mutant" => "变种人",
            "nether" => "神话生物",
            "plant" => "植物",
            "reptile" => "爬虫",
            "robot" => "机器人",
            "slime" => "变形怪",
            "spider" => "蜘蛛",
            "uplift" => "擢升者",
            "worm" => "蠕虫",
            "wildalien" => "外星野生生物",
            "zombie" => "丧尸",
            "none" => "无",
            "unknown" => "未知",
        );
        $links = array_map(function ($species) use ($trans) {
            $sp = strtolower($species);
            if (isset($trans[$sp])) {
                $sp = $trans[$sp];
            }
            return '<a href="'.route('monster.species', array($species)).'">'.$sp.'</a>';
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

        return $this->object->special_when_hit[1]."% 触发 ".$this->object->special_when_hit[0];
    }

    public function presentSize()
    {
        if (stripos($this->object->volume, "ml") || stripos($this->object->volume, "L")) {
            $value = floatval($this->object->volume) * 1.0;
            if (stripos($this->object->volume, "ml")) {
                $value /= 1000.0;
            }
            $strvalue = "";
            if ($value <= 7.5) {
                $strvalue = "很小";
            } elseif ($value <= 46.25) {
                $strvalue = "小";
            } elseif ($value <= 77.5) {
                $strvalue = "中等";
            } elseif ($value <= 483.75) {
                $strvalue = "大";
            } else {
                $strvalue = "巨大";
            }
            return "$strvalue (<y>$value</y> L)";
        } else {
            return $this->object->size;
        }
    }

    public function presentDifficulty()
    {
        $diff = $this->object->difficulty;
        if ($diff < 3) {
            $strvalue = '<font color="lightgray">极低</font>';
        } elseif ($diff < 10) {
            $strvalue = '<font color="lightgray">低</font>';
        } elseif ($diff < 20) {
            $strvalue = '<font color="LightCoral">中</font>';
        } elseif ($diff < 30) {
            $strvalue = '<font color="LightCoral">高</font>';
        } elseif ($diff < 50) {
            $strvalue = '<font color="red">极高</font>';
        } else {
            $strvalue = '<font color="red">致命</font>';
        }
        $diff = floor($diff);
        return "$diff ($strvalue)";
    }

    public function presentJson()
    {
        return '<pre><code class="language-json">'.$this->object->json.'</code></pre>';
    }

    public function presentMaterial()
    {
        return implode(",", array_map(function($m) {
            return $m->name;
        }, $this->object->material));
    }

    public function presentUpgradesTo()
    {
        $monsters = $this->object->upgrades_to;
        if (empty($monsters)) {
            return "";
        }

        $total_weight = array_sum(array_map(function ($mon) {
            return $mon->freq ?? ($mon->weight ?? 1);
        }, $monsters));

        return implode("<br>", array_map(
            function ($mon) use ($total_weight) {
                $ret = "";
                $name = $mon->monster->name;
                if (is_object($name)) {
                    $name = $name->str;
                }
                $freq = ($mon->freq ?? ($mon->weight ?? 1)) / $total_weight * 100;
                $ret .= '<a href="'.route("monster.view", $mon->monster->id).'">'.$name."</a> （{$freq}%）";
                if (isset($mon->cost_multiplier)) {
                    $ret .= "(占位：{$mon->cost_multiplier})";
                }
                return $ret;
            },
            $monsters
        ));
    }

    private function parseEntries($entries, $is_distro)
    {
        // TODO: container 等玩意儿？
        $ret = array();
        $total = 1.0;
        if ($is_distro) {
            $total = array_sum(array_map(function($t) { return $t->prob ?? 100; }, $entries)) / 100 ?: 1;
        }
        foreach ($entries as $entry) {
            $prob = round(($entry->prob ?? 100) / $total, 3);
            if (isset($entry->group)) {
                $ret[] = '<a href="'.route('special.itemgroup', $entry->group->id).'">'."{$entry->group->id}</a>（{$prob}%）";
            } else if (isset($entry->item)) {
                $ret[] = '<a href="'.route('item.view', $entry->item->id).'">'."{$entry->item->name}</a>（{$prob}%）";
            } else if (isset($entry->distribution)) {
                $ret[] = '掉落以下物品之一：';
                $ret = array_merge($ret, array($this->parseEntries($entry->distribution, true)));
            } else {
                $ret[] = '可能掉落以下物品：';
                $ret = array_merge($ret, array($this->parseEntries($entry->collection, false)));
            }
        }
        return implode("<br>", $ret);
    }

    public function presentDeathDrops()
    {
        if ($this->object->death_drops == NULL) {
            return;
        }
        $death_drops = $this->object->death_drops;
        if (is_object($death_drops) && $death_drops->id != NULL) {
            return '<a href="'.route('special.itemgroup', $death_drops->id)."\">{$death_drops->id}";
        } else if (is_array($death_drops)) {
            return "掉落以下物品之一：<br><ul>".$this->parseEntries($death_drops, true)."</ul>";
        } else {
            $entries = $death_drops->entries;
            if ($entries == NULL) {
                return;
            }
            if (isset($death_drops->subtype) && $death_drops->subtype == "collection") {
                $ret = "可能掉落以下物品：<br><ul>";
                return $ret.$this->parseEntries($entries, false)."</ul>";
            } else {
                $ret = "掉落以下物品之一：<br><ul>";
                return $ret.$this->parseEntries($entries, true)."</ul>";
            }
        }
    }
}
