<?php

namespace Presenters;

class Recipe extends \Robbo\Presenter\Presenter
{
    public function presentTime()
    {
        $time = $this->object->time;

        if (stripos($time, "h")){
            return ($time * 60)." 分钟";
        }
        if (stripos($time, "m")){
            return ($time * 1)." 分钟";
        }
        if (stripos($time, "s") || stripos($time, "t")){
            return ($time * 1)." 秒";
        }
        if ($time >= 6000) {
            return ($time / 6000)." 分钟";
        }

        return ($time / 100)." 秒";
    }

    public function presentSkillsRequired()
    {
        $skills = $this->object->skills_required;
        if (!$skills) {
            return "N/A";
        }

        return implode(", ", array_map(function ($i) use ($skills) {
            return "$i[0]($i[1])";
        }, $skills));
    }

    public function presentTools()
    {
        $tools = array();
        foreach ($this->object->tools as $group) {
            $inner = array();
            foreach ($group as $gi) {
                list($item, $amount) = $gi;
                $inner[] = link_to_route("item.view", $item->name, array("id" => $item->id))." ".($amount > 0 ? "($amount&nbsp;单位)" : "");
            }
            $tools[] = implode(" 或 ", $inner);
        }

        return "&gt; ".implode("<br>&gt; ", $tools)."\n";
    }

    public function presentComponents()
    {
        $components = array();
        foreach ($this->object->components as $group) {
            $inner = array();
            foreach ($group as $gi) {
                list($item, $amount) = $gi;
                $inner[] = "{$amount}x ".link_to_route("item.view", $item->name, array("id" => $item->id));
            }
            $components[] = implode(" 或 ", $inner);
        }
        $label = $this->object->category == "CC_NONCRAFT" ? "obtained" : "required";

        return "&gt; ".implode("<br>&gt; ", $components)."\n";
    }

    public function presentByproducts()
    {
        $byproducts = array();
        foreach ($this->object->byproducts as $group) {
            list($item, $amount) = $group;
            $byproducts[] = "{$amount}x ".link_to_route("item.view", $item->name, array("id" => $item->id));
        }

        return "&gt; ".implode(", ", $byproducts)."\n";
    }

    public function presentLabels()
    {
        $labelArray = [];
        $neverlearn = $this->object->never_learn;
        if ($neverlearn) {
            $labelArray[] = '<span class="label label-warning">无法被记住</span><br>';
        }
        if ($this->object->autolearn == true) {
            $labelArray[] = '<span class="label label-success">自动学会</span><br>';
        }

        $suffix = $this->object->id_suffix;
        if (stripos($suffix, "npc") !== false) {
            $labelArray[] = '<span class="label label-warning">NPC 配方</span><br>';
        } else {
            $labelArray[] = '<span class="label label-success">玩家配方</span><br>';
        }
        if ($this->object->modspace != "" && $this->object->modspace != "_dda_") {
            $labelArray[] = '<span class="label label-warning">'.$this->object->modfoldername.'</span>';
        }
        $obsolete = $this->object->obsolete;
        if ($obsolete === true) {
            $labelArray[] = '<span class="label label-danger">过时</span><br>';
        }
        if ($this->object->override == true) {
            $labelArray[] = '<span class="label label-warning">基础配方重载</span>';
        }

        return implode(" ", $labelArray);
    }

    public function presentNpcLabel()
    {
        $suffix = $this->object->id_suffix;
        if (stripos($suffix, "npc") !== false) {
            return '<span class="label label-warning">NPC</span>';
        }

        return "";
    }

    public function presentModLabel()
    {
        if ($this->object->modspace != "" && $this->object->modspace != "_dda_") {
            return '<span class="label label-warning">'.$this->object->modfoldername.'</span>';
        }

        return "";
    }

    public function presentObsoleteLabel()
    {
        $obsolete = $this->object->obsolete;
        if ($obsolete === true) {
            return '<span class="label label-danger">Obsolete</span>';
        }

        return "";
    }
}
