<?php

namespace Presenters;

class Recipe extends \Robbo\Presenter\Presenter
{
    public function presentTime()
    {
        $time = $this->object->time;

        if (stripos($time, "h")){
            return (floatval($time) * 60)." minutes";
        }
        if (stripos($time, "m")){
            return (floatval($time) * 1)." minutes";
        }
        if (stripos($time, "s") || stripos($time, "t")){
            return (floatval($time) * 1)." seconds";
        }
        if ($time >= 6000) {
            return (floatval($time) / 6000)." minutes";
        }

        return (floatval($time) / 100)." seconds";
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
                $inner[] = '<a href="'.route("item.view", array("id" => $item->id)).'">'.$item->name." ".($amount > 0 ? "($amount&nbsp;charges)" : "").'</a>';
            }
            $tools[] = implode(" OR ", $inner);
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
                $inner[] = "{$amount}x ".'<a href="'.route("item.view", array("id" => $item->id)).'">'.$item->name.'</a>';
            }
            $components[] = implode(" OR ", $inner);
        }
        $label = $this->object->category == "CC_NONCRAFT" ? "obtained" : "required";

        return "&gt; ".implode("<br>&gt; ", $components)."\n";
    }

    public function presentByproducts()
    {
        $byproducts = array();
        foreach ($this->object->byproducts as $group) {
            list($item, $amount) = $group;
            $byproducts[] = "{$amount}x ".'<a href="'.route("item.view", array("id" => $item->id)).'">'.$item->name.'</a>';
        }

        return "&gt; ".implode(", ", $byproducts)."\n";
    }

    public function presentLabels()
    {
        $labelArray = [];
        $neverlearn = $this->object->never_learn;
        if ($neverlearn) {
            $labelArray[] = '<span class="label label-warning">Cannot Be Memorized</span><br>';
        }
        if ($this->object->autolearn == true) {
            $labelArray[] = '<span class="label label-success">Autolearned</span><br>';
        }

        $suffix = $this->object->id_suffix;
        if (stripos($suffix, "npc") !== false) {
            $labelArray[] = '<span class="label label-warning">NPC Recipe</span><br>';
        } else {
            $labelArray[] = '<span class="label label-success">Player Recipe</span><br>';
        }
        if ($this->modname !== null) {
            $labelArray[] = '<span class="label label-warning">'.$this->modname.'</span>';
        }
        $obsolete = $this->object->obsolete;
        if ($obsolete === true) {
            $labelArray[] = '<span class="label label-danger">Obsolete</span><br>';
        }
        if ($this->object->override == true) {
            $labelArray[] = '<span class="label label-warning">overrides base recipe</span>';
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
        if ($this->modname !== null) {
            return '<span class="label label-warning">'.$this->modname.'</span>';
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
