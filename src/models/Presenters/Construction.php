<?php

namespace Presenters;

class Construction extends \Robbo\Presenter\Presenter
{
    public function presentQualities()
    {
        $out = array();
        $simplearraygrouping = false;
        foreach ($this->object->qualities as $group) {
            if (!is_array($group)) {
                $simplearraygrouping = true;
                break;
            }
            $group = array_map(function ($q) {
                $link = '<a href="'.route("item.qualities", $q->id).'">'.$q->quality->name.'</a>';

                return "1 tool with $link quality of $q->level or more";
            }, $group);
            $out[] = "> ".join(" <strong>OR</strong> ", $group);
        }
        if ($simplearraygrouping == true) {
            $group = array_map(function ($q) {
                $link = '<a href="'.route("item.qualities", $q->id).'">'.$q->quality->name.'</a>';

                return "1 tool with $link quality of $q->level or more";
            }, $this->object->qualities);

            return implode("<br> ", $group);
        }

        return implode("<br> ", $out);
    }

    public function presentTools()
    {
        $out = array();
        foreach ($this->object->tools as $group) {
            $group = array_map(function ($t) {
                $link = '<a href="'.route("item.view", $t->item->id).'">'.$t->item->name.'</a>';

                return $link.($t->charges != 0 ? " ({$t->charges} charges)" : "");
            }, $group);
            $out[] = "> ".join(" <strong>OR</strong> ", $group);
        }

        return implode("<br>", $out);
    }

    public function presentComponents()
    {
        $out = array();
        foreach ($this->object->components as $group) {
            $group = array_map(function ($c) {
                $link = '<a href="'.route("item.view", $c->item->id).'">'.$c->item->name.'</a>';

                return "$c->amount $link";
            }, $group);
            $out[] = "> ".join(" <strong>OR</strong> ", $group);
        }

        return implode("<br> ", $out);
    }
}
