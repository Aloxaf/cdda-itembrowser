<?php

namespace Repositories\Indexers;

use Repositories\RepositoryWriterInterface;

class Furniture implements IndexerInterface
{
    const DEFAULT_INDEX = "furnitures";

    public function onNewObject(RepositoryWriterInterface $repo, $object)
    {
        if ($object->type != "furniture") {
            return;
        }

        $repo->append(self::DEFAULT_INDEX, $object->id);
        $repo->set(self::DEFAULT_INDEX.".$object->id", $object->repo_id);
        $repo->set("all.$object->id", $object->repo_id);

        if (isset($object->deconstruct) && isset($object->id) && isset($object->deconstruct->items)) {
            foreach ($object->deconstruct->items as $item) {
                if (isset($item->item)) {
                    $repo->append("item.deconstructFrom.$item->item", $object->id);
                }
            }
        }
    }

    public function onFinishedLoading(RepositoryWriterInterface $repo)
    {
    }
}
