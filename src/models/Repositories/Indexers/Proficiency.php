<?php

namespace Repositories\Indexers;

use Repositories\RepositoryWriterInterface;

class Proficiency implements IndexerInterface
{
    protected $database;

    const DEFAULT_INDEX = "proficiencies";

    public function onNewObject(RepositoryWriterInterface $repo, $object)
    {
        if ($object->type == "proficiency") {
            $repo->append(self::DEFAULT_INDEX, $object->id);
            $repo->set(self::DEFAULT_INDEX.".".$object->id, $object->repo_id);
        }
    }

    public function onFinishedLoading(RepositoryWriterInterface $repo)
    {
    }
}
