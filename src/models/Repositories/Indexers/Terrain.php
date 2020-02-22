<?php

namespace Repositories\Indexers;

use Repositories\RepositoryWriterInterface;

class Terrain implements IndexerInterface
{
    const DEFAULT_INDEX = "terrains";

    public function onNewObject(RepositoryWriterInterface $repo, $object)
    {
        if ($object->type != "terrain") {
            return;
        }

        $repo->append(self::DEFAULT_INDEX, $object->id);
        $repo->set(self::DEFAULT_INDEX.".$object->id", $object->repo_id);
        $repo->set("all.$object->id", $object->repo_id);

        if (isset($object->bash) && isset($object->id) && isset($object->bash->items)) {
            if (is_array($object->bash->items)) {
                foreach ($object->bash->items as $item) {
                    if (isset($item->item)) {
                        $repo->append("item.bashFromTerrain.$item->item", $object->id);
                    }
                }
            }
        }
    }

    public function onFinishedLoading(RepositoryWriterInterface $repo)
    {
    }
}
