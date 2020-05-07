<?php

namespace App\Http\Controllers;
use Repositories;
use App\Http\Controllers\Controller;

class SpecialController extends Controller
{
    protected $repo;

    private function getLayout()
    {
        return \View::make("layouts.bootstrap");
    }

    public function __construct(Repositories\RepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function latestchanges()
    {
        $diff = json_decode(file_get_contents("diff.json"));
        return $this->getLayout()->nest('content', 'special.latestchanges', compact('diff'));
    }

    public function vitamin($id)
    {
        $items = array($this->repo->getModel("Special", $id));
        return $this->getLayout()->nest('content', 'special.vitamin', compact('items'));
    }

    public function effect($id)
    {
        $items = array($this->repo->getModel("Special", $id));
        return $this->getLayout()->nest('content', 'special.effect', compact('items'));
    }

    public function itemgroup($id)
    {
        $groups = $this->repo->getMultiModelOrFail("ItemGroup", $id);
        return $this->getLayout()->nest('content', 'special.itemgroup', compact('groups'));
    }
}
