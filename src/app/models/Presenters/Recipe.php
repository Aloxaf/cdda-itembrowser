<?php
namespace Presenters;

class Recipe extends \Robbo\Presenter\Presenter
{
    public function presentTime()
    {
        $time = $this->object->time;
        if ($time >= 1000) {
            return ($time/1000)." minutes";
        }

        return ($time/100)." turns";
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
                $inner[] =  link_to_route("item.view", $item->name, array("id" => $item->id))." ".($amount>0 ? "($amount&nbsp;charges)" : "");
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
                $inner[] = "{$amount}x ".link_to_route("item.view", $item->name, array("id" => $item->id));
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
            $byproducts[] = "{$amount}x ".link_to_route("item.view", $item->name, array("id" => $item->id));
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

        $suffix = $this->object->id_suffix;
        if (stripos($suffix, "npc") !== false) {
            $labelArray[] = '<span class="label label-warning">NPC Recipe</span><br>';
        } else {
            $labelArray[] = '<span class="label label-success">Player Recipe</span><br>';
        }
        $obsolete = $this->object->obsolete;
        if ($obsolete === true) {
            $labelArray[] = '<span class="label label-danger">Obsolete</span><br>';
        }

        return implode(" ", $labelArray);
    }
    
    public function presentNpcLabel()
    {
        $suffix = $this->object->id_suffix;
        if (stripos($suffix, "npc") !== false) {
            return '<span class="label label-warning">NPC Recipe</span>';
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
