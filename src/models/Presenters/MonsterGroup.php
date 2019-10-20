<?php

namespace Presenters;

class MonsterGroup extends \Robbo\Presenter\Presenter
{
    public function presentNiceName()
    {
        $name = $this->object->name;
        if (is_object($name)) {
            $name = $name->str;
        }
        return ucfirst(strtolower(substr($name, 6)));
    }

    public function presentUniqueMonsters()
    {
        $monsters = $this->object->uniqueMonsters;
        array_walk($monsters, function (&$monster) {
            $monster = new Monster($monster);
        });
        usort($monsters, function ($a, $b) {
            $a_name = $a->name;
            if (is_object($a_name)) {
                $a_name = $a_name->str;
            }
            $b_name = $b->name;
            if (is_object($b_name)) {
                $b_name = $b_name->str;
            }
            return strcmp(strtolower($a_name), strtolower($b_name));
        });

        return $monsters;
    }
}
