<?php

namespace Repositories\Indexers;

use Repositories\RepositoryWriterInterface;

class Requirement implements IndexerInterface
{
    protected $database;

    const DEFAULT_INDEX = "requirement";

    public function onNewObject(RepositoryWriterInterface $repo, $object)
    {
        if ($object->type == "requirement") {
            $repo->append(self::DEFAULT_INDEX, $object->id);
            $repo->set(self::DEFAULT_INDEX.".".$object->id, $object->repo_id);
        }
    }

    public function onFinishedLoading(RepositoryWriterInterface $repo)
    {
    }
}
