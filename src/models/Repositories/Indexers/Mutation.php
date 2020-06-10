<?php

namespace Repositories\Indexers;

use Repositories\RepositoryWriterInterface;

class Mutation implements IndexerInterface
{
    protected $database;

    const DEFAULT_INDEX = "mutation";

    public function onFinishedLoading(RepositoryWriterInterface $repo)
    {
        $starttime = microtime(true);

        $repo->sort(self::DEFAULT_INDEX);
        $repo->appendUnique("mutation_category", "None");

        $timediff = microtime(true) - $starttime;
        echo "Mutation post-processing ".number_format($timediff, 3)." s.\n";
    }

    public function onNewObject(RepositoryWriterInterface $repo, $object)
    {
        if ($object->type != "mutation" && $object->type != "mutation_category") {
            return;
        }
        $repo->appendUnique(self::DEFAULT_INDEX, $object->id);
        $repo->set(self::DEFAULT_INDEX.".".$object->id, $object->repo_id);

        if ($object->type == "mutation_category") {
            $repo->appendUnique("mutation_category", $object->id);
        }
        if (isset($object->category)) {
            foreach ($object->category as $category) {
                $repo->append("mutation_category.$category", $object->id);
            }
        } else {
            $repo->append("mutation_category.None", $object->id);
        }
        return;
    }
}
