<?php

namespace Presenters;

class Special extends \Robbo\Presenter\Presenter
{
    public function presentJson()
    {
        return '<pre><code class="language-json">'.$this->object->json.'</code></pre>';
    }


    public function presentDeficiency()
    {
        if ($this->object->deficiency !== NULL) {
            return '<a href="'.route("special.effect", $this->object->deficiency->id).'">'.$this->object->deficiency->effect_name.'</a>';
        }
    }

    public function presentExcess()
    {
        if ($this->object->excess !== NULL) {
            return '<a href="'.route("special.effect", $this->object->excess->id).'">'.$this->object->excess->effect_name.'</a>';
        }
    }

    public function presentRemovesEffects()
    {
        return implode("，", array_map(
            function ($effect) {
                return '<a href="'.route("special.effect", $effect->id).'">'.$effect->effect_name.'</a>';
            },
            $this->object->removes_effects
        ));
    }
}
