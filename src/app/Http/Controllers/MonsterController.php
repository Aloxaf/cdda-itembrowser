<?php

namespace App\Http\Controllers;
use Repositories;
use App\Http\Controllers\Controller;

class MonsterController extends Controller
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

    public function groups($id = null)
    {
        $groups = $this->repo->allModels('MonsterGroup', 'monstergroups');
        if ($id === null) {
            $id = reset($groups)->name;

            return redirect()->route("monster.groups", array($id));
        }
//         $group = $this->repo->getModel('MonsterGroup', $id);
        $groupbunch = $this->repo->getMultiModelOrFail("MonsterGroup", $id);

        return $this->getLayout()->nest('content', 'monsters.groups', compact('groups', 'groupbunch', 'id'));
    }

    public function species($id = null)
    {
        $species = $this->repo->raw("monster.species");
        if ($id === null) {
            $id = reset($species);

            return redirect()->route("monster.species", array($id));
        }
        $data = $this->repo->allModels("Monster", "monster.species.$id");

        return $this->getLayout()->nest('content', 'monsters.species', compact('species', 'id', 'data'));
    }

    public function view($id)
    {
//         $monster = $this->repo->getModel('Monster', $id);
        $monsterbunch = $this->repo->getMultiModelOrFail('Monster', $id);

        return $this->getLayout()->nest('content', 'monsters.view', compact('id', 'monsterbunch'));
    }
}
