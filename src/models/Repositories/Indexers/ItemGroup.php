<?php

namespace Repositories\Indexers;

use Repositories\RepositoryWriterInterface;

class ItemGroup implements IndexerInterface
{
    protected $database;

    const DEFAULT_INDEX = "itemgroup";

    public function onFinishedLoading(RepositoryWriterInterface $repo)
    {
        $starttime = microtime(true);

        $repo->sort(self::DEFAULT_INDEX);

        $timediff = microtime(true) - $starttime;
        echo "ItemGroup post-processing ".number_format($timediff, 3)." s.\n";
    }

    public function onNewObject(RepositoryWriterInterface $repo, $object)
    {
        if ($object->type == "item_group" || $object->type == "harvest") {
            $repo->appendUnique(self::DEFAULT_INDEX, $object->id);
            $repo->set(self::DEFAULT_INDEX.".".$object->id, $object->repo_id);
            return;
        }
    }
}
