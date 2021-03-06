<?php

namespace Presenters;

class ItemGroup extends \Robbo\Presenter\Presenter
{
    public function presentModinfo()
    {
        if ($this->modname != "_dda_" && $this->modname != "") {
            return '<span class="label label-warning">'.$this->modname.'</span>';
        }

        return "";
    }

    public function presentJson()
    {
        return '<pre><code class="language-json">'.$this->object->json.'</code></pre>';
    }

    private function parseEntries($entries, $is_distro)
    {
        $ret = "";
        $total = 1.0;
        if ($is_distro) {
            $total = array_sum(array_map(function($t) { return $t->prob ?? 100; }, $entries)) / 100 ?: 1;
        }
        foreach ($entries as $entry) {
            $pre = "";
            $prob = round(($entry->prob ?? 100) / $total, 3);
            $pre .= "<yellow>$prob</yellow>% 掉落 ";

            if (isset($entry->count)) {
                if (is_array($entry->count)) {
                    $pre .= " <yellow>{$entry->count[0]}</yellow>~<yellow>{$entry->count[1]}</yellow> 个 ";
                } else {
                    $pre .= " <yellow>{$entry->count}</yellow> 个 ";
                }
            }
            if (isset($entry->charges)) {
                if (is_array($entry->charges)) {
                    $pre .= " <yellow>{$entry->charges[0]}</yellow>~<yellow>{$entry->charges[1]}</yellow> 单位的 ";
                } else {
                    $pre .= " <yellow>{$entry->charges}</yellow> 单位 ";
                }
            }
            $keys = array(
                "contents-item" => array("item.view", "装着", "的"),
                "contents-group" => array("special.itemgroup", "装着", "的"),
                "container-item" => array("item.view", "用", "装着的"),
                "container-group" => array("special.itemgroup", "用", "装着的"),
                "ammo-item" => array("item.view", "装着", "的"),
                "ammo-group" => array("special.itemgroup", "装着", "的"),
            );
            foreach ($keys as $k => $v) {
                if (isset($entry->{$k})) {
                    $tmp = $entry->{$k};
                    if (!is_array($tmp)) {
                        $tmp = array($tmp);
                    }
                    foreach ($tmp as $tk) {
                        if ($v[0] == "item.view") {
                            $pre .= $v[1].' <a href="'.route($v[0], $tk->id).'">'.$tk->name.'</a> '.$v[2];
                        } else {
                            $pre .= $v[1].' <a href="'.route($v[0], $tk->id).'">'.$tk->id.'</a> '.$v[2];
                        }
                    }
                }
            }
            if (isset($entry->damage)) {
                if (is_array($entry->damage)) {
                    $pre .= " 损坏程度 <yellow>{$entry->damage[0]}</yellow>~<yellow>{$entry->damage[1]}</yellow> 的 ";
                } else {
                    $pre .= " 损坏程度 <yellow>{$entry->damage}</yellow> 的 ";
                }
            }

            if (isset($entry->group)) {
                $ret .= $pre.'<a href="'.route('special.itemgroup', $entry->group->id).'">'."{$entry->group->id}</a><br>";
            } else if (isset($entry->item)) {
                $ret .= $pre.'<a href="'.route('item.view', $entry->item->id).'">'."{$entry->item->name}</a><br>";
            } else if (isset($entry->distribution)) {
                $ret .= $pre.'以下物品之一：<br>';
                $ret .= "<ul>".$this->parseEntries($entry->distribution, true)."</ul>";
            } else {
                $ret .= $pre.'以下物品：<br>';
                $ret .= "<ul>".$this->parseEntries($entry->collection, false)."</ul>";
            }
        }
        return $ret;
    }

    public function presentItems()
    {
        if ($this->object->subtype == "collection") {
            $ret = "可能掉落以下物品：<br><ul>";
            return $ret.$this->parseEntries($this->object->entries, false)."</ul>";
        } else {
            $ret = "掉落以下物品之一：<br><ul>";
            return $ret.$this->parseEntries($this->object->entries, true)."</ul>";
        }
    }

    public function presentDropFrom()
    {
        $ret = implode(", ", array_map(
            function ($drop) {
                if ($drop->type == "MONSTER") {
                    return '<a href="'.route('monster.view', $drop->id).'">'.$drop->getPresenter()->nicename.'</a>';
                } else {
                    return '<a href="'.route('special.itemgroup', $drop->id).'">'.$drop->id.'</a>';
                }
            },
            $this->object->dropfrom
        ));
        if ($ret == "") {
            return;
        } else {
            return "掉落自：$ret<br>";
        }
    }

    public function presentHarvestFrom()
    {
        $ret = implode(", ", array_map(
            function ($drop) {
                if ($drop->type == "MONSTER") {
                    return '<a href="'.route('monster.view', $drop->id).'">'.$drop->getPresenter()->nicename.'</a>';
                } else {
                    return '<a href="'.route('special.itemgroup', $drop->id).'">'.$drop->id.'</a>';
                }
            },
            $this->object->harvestfrom
        ));
        if ($ret == "") {
            return;
        } else {
            return "收获自：$ret<br>";
        }
    }
}
