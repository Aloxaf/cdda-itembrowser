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

                return "1 个 $link 功能至少 $q->level 级的工具";
            }, $group);
            $out[] = "> ".join(" <strong>或</strong> ", $group);
        }
        if ($simplearraygrouping == true) {
            $group = array_map(function ($q) {
                $link = '<a href="'.route("item.qualities", $q->id).'">'.$q->quality->name.'</a>';

                return "1 个 $link 功能至少 $q->level 级的工具";
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

                return $link.($t->charges != 0 ? " ({$t->charges} 单位)" : "");
            }, $group);
            $out[] = "> ".join(" <strong>或</strong> ", $group);
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
            $out[] = "> ".join(" <strong>或</strong> ", $group);
        }

        return implode("<br> ", $out);
    }
}
