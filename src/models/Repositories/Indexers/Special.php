<?php

namespace Repositories\Indexers;

use Repositories\RepositoryWriterInterface;

class Special implements IndexerInterface
{
    protected $database;

    const DEFAULT_INDEX = "special";

    public function onFinishedLoading(RepositoryWriterInterface $repo)
    {
        $starttime = microtime(true);

        $repo->sort(self::DEFAULT_INDEX);

        $timediff = microtime(true) - $starttime;
        echo "Special post-processing ".number_format($timediff, 3)." s.\n";
    }

    public function onNewObject(RepositoryWriterInterface $repo, $object)
    {
        if ($object->type != "effect_type" && $object->type != "vitamin") {
            return;
        }
        $repo->appendUnique(self::DEFAULT_INDEX, $object->id);
        $repo->set(self::DEFAULT_INDEX.".".$object->id, $object->repo_id);
    }
}
