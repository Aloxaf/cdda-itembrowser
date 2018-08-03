<?php
namespace Repositories\Indexers;

use Repositories\RepositoryWriterInterface;
use CustomUtility\ValueUtil;

class Monster implements IndexerInterface
{
    protected $database;

    const DEFAULT_INDEX = "monsters";

    public function onNewObject(RepositoryWriterInterface $repo, $object)
    {
        if ($object->type == "MONSTER") {
            $repo->append(self::DEFAULT_INDEX, $object->id);
            $repo->set(self::DEFAULT_INDEX.".".$object->id, $object->repo_id);
            $objspecies = (array) $object->species;
            foreach ($objspecies as $species) {
                $repo->append("monster.species.$species", $object->id);
                $repo->addUnique("monster.species", $species);
            }

            ValueUtil::SetDefault($object, "melee_skill", 0);
            ValueUtil::SetDefault($object, "melee_dice_sides", 0);
            ValueUtil::SetDefault($object, "melee_cut", 0);
            ValueUtil::SetDefault($object, "melee_dice", 0);
            ValueUtil::SetDefault($object, "aggression", 0);
            ValueUtil::SetDefault($object, "morale", 0);

            return;
        }
    }

    public function onFinishedLoading(RepositoryWriterInterface $repo)
    {
        $starttime = microtime(true);

        $repo->sort("monster.species");

        $timediff = microtime(true) - $starttime;
        print "Monster post-processing ".number_format($timediff,3)." s.\n";
    }
}
