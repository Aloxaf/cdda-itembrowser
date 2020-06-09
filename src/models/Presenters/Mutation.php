<?php

namespace Presenters;

class Mutation extends \Robbo\Presenter\Presenter
{
    public function presentModLabel()
    {
        $badges = array();
        if ($this->modname != null) {
            $badges[] = '<span class="label label-warning">'.$this->modname.'</span>';
        }

        return implode(" ", $badges);
    }

    public function presentJson()
    {
        return '<pre><code class="language-json">'.$this->object->json.'</code></pre>';
    }
}
