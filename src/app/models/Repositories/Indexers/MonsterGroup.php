<?php
namespace Repositories\Indexers;

use Repositories\RepositoryWriterInterface;

class MonsterGroup implements IndexerInterface
{
    protected $database;

    const DEFAULT_INDEX = "monstergroups";

    public function onFinishedLoading(RepositoryWriterInterface $repo)
    {
        $starttime = microtime(true);

        $repo->sort(self::DEFAULT_INDEX);

        $timediff = microtime(true) - $starttime;
        print "MonsterGroup post-processing $timediff s.\n";
    }

    public function onNewObject(RepositoryWriterInterface $repo, $object)
    {
        if ($object->type == "monstergroup") {
            $repo->append(self::DEFAULT_INDEX, $object->name);
            $repo->set(self::DEFAULT_INDEX.".".$object->name, $object->repo_id);

            return;
        }
    }
}
