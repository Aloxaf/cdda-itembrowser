<?php

namespace App\Http\Controllers;
use Repositories;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

class WorldController extends Controller
{
    private $repo;

    private function getLayout()
    {
        return \View::make("layouts.bootstrap");
    }

    public function __construct(Repositories\RepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function construction($id)
    {
        $data = $this->repo->getModel("Construction", $id);
        return $this->getLayout()->nest("content", "world.construction", compact('data'));
    }

    public function constructionCategories($id = null)
    {
        $categories = $this->repo->raw("construction.categories");
        if ($id === null) {
            return redirect()->route(Route::currentRouteName(), array(reset($categories)));
        }

        $data = $this->repo->allModels("Construction", "construction.category.$id");
        return $this->getLayout()->nest("content", "world.constructionCategories",
            compact('categories', 'data', 'id'));
    }
}
