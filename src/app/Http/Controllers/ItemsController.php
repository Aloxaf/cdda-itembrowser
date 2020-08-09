<?php

namespace App\Http\Controllers;
use Repositories;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

class ItemsController extends Controller
{
    protected $item;
    protected $repo;

    private function getLayout()
    {
        return \View::make("layouts.bootstrap");
    }

    public function __construct(Repositories\RepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function view($id)
    {
        $itembunch = $this->repo->getMultiModelOrFail("Item", $id);
        // \Log::info("test", array($itembunch,compact('itembunch')));

        return $this->getLayout()->nest('content', 'items.view', compact('itembunch'));
    }

    public function craft($id)
    {
        $itembunch = $this->repo->getMultiModelOrFail("Item", $id);

        return $this->getLayout()->nest('content', 'items.craft', compact('itembunch'));
    }

    public function recipes($id, $category = null)
    {
        $itembunch = $this->repo->getMultiModelOrFail("Item", $id);
        $categories = $itembunch[0]->toolCategories;

        if ($category === null) {
            $category = key($categories);

            return redirect()->route(Route::currentRouteName(), array($id, $category));
        }

        $recipes = $itembunch[0]->getToolForCategory($category);

        return $this->getLayout()->nest('content', 'items.recipes', compact('itembunch', "category", "recipes", "categories"));
    }

    public function disassemble($id)
    {
        $itembunch = $this->repo->getMultiModelOrFail("Item", $id);

        return $this->getLayout()->nest('content', 'items.disassemble', compact('itembunch'));
    }

    public function construction($id)
    {
        $itembunch = $this->repo->getMultiModelOrFail("Item", $id);
        return $this->getLayout()->nest("content", "items.construction", compact('itembunch'));
    }

    public function armors($part = null)
    {
        $parts = $this->repo->raw("armorParts");

        if ($part === null) {
            return redirect()->route(Route::currentRouteName(), array(reset($parts)));
        }

        $items = $this->repo->allModels("Item", "armor.$part");

        return $this->getLayout()->nest('content', 'items.armor', compact('items', 'parts', 'part'));
    }

    public function guns($skill = null)
    {
        $skills = $this->repo->raw("gunSkills");

        if ($skill === null) {
            return redirect()->route(Route::currentRouteName(), array(reset($skills)));
        }

        $items = $this->repo->allModels("Item", "gun.$skill");

        return $this->getLayout()->nest('content', 'items.gun', compact('items', 'skills', 'skill'));
    }

    public function books($type = null)
    {
        $types = $this->repo->raw("bookSkills");

        if ($type === null) {
            return redirect()->route(Route::currentRouteName(), reset($types));
        }

        $items = $this->repo->allModels("Item", "book.$type");

        return $this->getLayout()->nest('content', 'items.books', compact('items', 'type', 'types'));
    }

    public function melee()
    {
        $items = $this->repo->allModels("Item", "melee");

        return $this->getLayout()->nest('content', "items.melee", compact('items'));
    }

    public function consumables($type = null)
    {
        $types = $this->repo->raw("consumableTypes");

        if ($type === null) {
            return redirect()->route(Route::currentRouteName(), array(reset($types)));
        }

        $items = $this->repo->allModels("Item", "consumables.$type");

        return $this->getLayout()->nest('content', 'items.consumables', compact('items', 'type', 'types'));
    }

    public function containers()
    {
        $items = $this->repo->allModels("Item", "container");

        return $this->getLayout()->nest('content', 'items.containers', compact('items'));
    }

    public function qualities($id = null)
    {
        $qualities = $this->repo->allModels("Quality", "qualities");

        if ($id === null) {
            return redirect()->route("item.qualities", array(reset($qualities)->id));
        }

        $items = $this->repo->allModels("Item", "quality.$id");

        return $this->getLayout()->nest('content', 'items.qualities', compact('items', 'qualities', 'id'));
    }

    public function materials($id = null)
    {
        $materials = $this->repo->allModels("Material", "materials");

        if ($id === null) {
            return redirect()->route(Route::currentRouteName(), array(reset($materials)->id));
        }
        $items = $this->repo->allModels("Item", "material.$id");

        return $this->getLayout()->nest('content', 'items.materials', compact('items', 'materials', 'id'));
    }

    public function flags($id = null)
    {
        $flags = $this->repo->raw("flags");

        if ($id === null) {
            return redirect()->route(Route::currentRouteName(), array(reset($flags)));
        }
        $items = $this->repo->allModels("Item", "flag.$id");

        return $this->getLayout()->nest('content', "items.flags", compact("items", "flags", "id"));
    }

    public function skills($id = null, $level = 1)
    {
        $skills = $this->repo->raw("skills");

        if ($id === null) {
            return redirect()->route(Route::currentRouteName(), array(reset($skills), 1));
        }
        $items = $this->repo->allModels("Item", "skill.$id.$level");
        $levels = range(1, 10);

        return $this->getLayout()->nest('content', "items.skills", compact("items", "skills", "id", "level", "levels"));
    }

    public function gunmods($skill = null, $part = null)
    {
        $skills = $this->repo->raw("gunmodSkills");
        $parts = $this->repo->raw("gunmodParts");
        $mods = $this->repo->allModels("Item", "gunmods.$skill.$part");

        return $this->getLayout()->nest('content', "items.gunmods", compact('skill', 'part', "skills", "parts", 'mods'));
    }

    public function wiki($id)
    {
        $item = $this->repo->getModelOrFail("Item", $id);

        return redirect()->to("http://cddawiki.chezzo.com/cdda_wiki/index.php?title=$item->slug");
    }
}
