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

    private function parseEntries($entries)
    {
        $ret = array();
        foreach ($entries as $entry) {
            $pre = "";
            $prob = $entry->prob ?? 100;
            $pre .= "<yellow>$prob</yellow>% 掉落 ";

            if (isset($entry->count)) {
                if (is_array($entry->count)) {
                    $pre .= " {$entry->count[0]}~{$entry->count[1]} 个 ";
                } else {
                    $pre .= " {$entry->count} 个 ";
                }
            }
            if (isset($entry->charges)) {
                if (is_array($entry->charges)) {
                    $pre .= " {$entry->charges[0]}~{$entry->charges[1]} 单位的 ";
                } else {
                    $pre .= " {$entry->charges} 单位 ";
                }
            }
            $keys = array(
                "contents-item" => array("item.view", "装着", "的"),
                "contents-group" => array("item.itemgroup", "装着", "的"),
                "container-item" => array("item.view", "用", "装着的"),
                "container-group" => array("item.itemgroup", "用", "装着的"),
                "ammo-item" => array("item.view", "装着", "的"),
                "ammo-group" => array("item.itemgroup", "装着", "的"),
            );
            foreach ($keys as $k => $v) {
                if (isset($entry->{$k})) {
                    if ($v[0] == "item.view") {
                        $pre .= $v[1].' <a href="'.route($v[0], $entry->{$k}->id).'">'.$entry->{$k}->name.'</a> '.$v[2];
                    } else {
                        $pre .= $v[1].' <a href="'.route($v[0], $entry->{$k}->id).'">'.$entry->{$k}->id.'</a> '.$v[2];
                    }
                }
            }
            if (isset($entry->damage)) {
                if (is_array($entry->damage)) {
                    $pre .= " 损坏程度 {$entry->damage[0]}~{$entry->damage[1]} 的 ";
                } else {
                    $pre .= " 损坏程度 {$entry->damage} 的 ";
                }
            }

            if (isset($entry->group)) {
                $ret[] = $pre.'<a href="'.route('item.itemgroup', $entry->group->id).'">'."{$entry->group->id}</a>";
            } else if (isset($entry->item)) {
                $ret[] = $pre.'<a href="'.route('item.view', $entry->item->id).'">'."{$entry->item->name}</a>";
            } else if (isset($entry->distribution)) {
                $ret[] = '必定掉落以下物品之一：';
                $ret = array_merge($ret, array("<ul>".$this->parseEntries($entry->distribution)."</ul>"));
            } else {
                $ret[] = '可能掉落以下物品：';
                $ret = array_merge($ret, array("<ul>".$this->parseEntries($entry->collection)."</ul>"));
            }
        }
        return implode("<br>", $ret);
    }

    public function presentItems()
    {
        if ($this->object->subtype == "collection") {
            $ret = "可能掉落以下物品：<br><ul>";
        } else {
            $ret = "必定掉落以下物品之一：<br><ul>";
        }
        return $ret.$this->parseEntries($this->object->entries)."</ul>";
    }
}
