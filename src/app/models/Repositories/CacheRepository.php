<?php
namespace Repositories;

class CacheRepository extends Repository implements RepositoryInterface,
    RepositoryParserInterface
{
    private $repo;
    private $source;
    private $expiration;

    public function __construct(
        \Illuminate\Cache\Repository $repo,
        \Illuminate\Foundation\Application $app
    )
    {
        $this->repo = $repo;
        $this->app = $app;
    }

    public function setSource($source)
    {
        $this->source = $source;
    }

    public function read()
    {
        list($database, $index) = $this->source->read();

        foreach ($database as $repo_id => $object) {
            $this->repo->forever("cdda:db:$repo_id", $object);
        }

        foreach ($index as $id => $data) {
            $this->repo->forever("cdda:index:$id", $data);
        }
        $this->repo->forever("cdda:version", $this->source->version());
        return [$database, $index];
    }

    public function get($index, $id)
    {
        $indexDb = $this->all($index);
        if (!isset($indexDb[$id])) {
            return;
        }

        $repo_id = $indexDb[$id];

        if (!isset($this->database[$repo_id])) {
            $this->database[$repo_id] = $this->repo->get("cdda:db:$repo_id");
        }

        return $this->database[$repo_id];
    }

    public function all($index)
    {
        if (!isset($this->index[$index])) {
            $this->index[$index] = $this->repo->get("cdda:index:$index")?: array();
        }

        return $this->index[$index];
    }

    public function searchModels($repo, $search)
    {
        $key = "search:$repo:".str_replace(" ", "!", $search);

        $repoInstance = $this->app->make("Repositories\\Indexers\\$repo");
        $idField = $repoInstance::ID_FIELD;

        $objects = $this->repo->rememberForever($key,
            function () use ($repo, $search, $idField) {
                $objects = parent::searchModels($repo, $search);
                array_walk($objects, function (&$object) use ($idField) {
                    $object = $object->$idField;
                });

            return $objects;
        });

        array_walk($objects, 
            function (&$object) use ($repo) {
                $object = $this->getModel($repo, $object);
            });

        return $objects;
    }
    public function version()
    {
        return $this->repo->get("cdda:version");
    }
}
