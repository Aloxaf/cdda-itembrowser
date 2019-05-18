<?php

namespace Repositories\Indexers;

use Repositories\RepositoryWriterInterface;

class Construction implements IndexerInterface
{
    const DEFAULT_INDEX = "construction";

    public function onNewObject(RepositoryWriterInterface $repo, $object)
    {
        if ($object->type != "construction") {
            return;
        }

        try {
            $repo->append(self::DEFAULT_INDEX, $object->repo_id);
            $repo->set(self::DEFAULT_INDEX.".$object->repo_id", $object->repo_id);

            $repo->append("construction.category.$object->category", $object->repo_id);
            $repo->addUnique("construction.categories", $object->category);

            if (isset($object->components)) {
                foreach ($object->components as $group) {
                    foreach ($group as $component) {
                        $item = $component[0];
                        $repo->addUnique("construction.$item", $object->repo_id);
                    }
                }
            }

            if (isset($object->tools)) {
                foreach ($object->tools as $group) {
                    foreach ($group as $item) {
                        if (is_array($item)) {
                            list($item, $amount) = $item;
                        }
                        $repo->addUnique("construction.$item", $object->repo_id);
                    }
                }
            }
        } catch (\Exception $e) {
            var_dump($object);
            echo "Construction object failed to parse: ".$e->getMessage()."\n";
        }
    }

    private function itemQualityLevel($item, $quality)
    {
        foreach ($item->qualities as $q) {
            if ($q[0] == $quality) {
                return $q[1];
            }
        }
    }

    public function onFinishedLoading(RepositoryWriterInterface $repo)
    {
        $starttime = microtime(true);
        foreach ($repo->raw(self::DEFAULT_INDEX) as $id) {
            $object = $repo->get(self::DEFAULT_INDEX.".$id");

            if (isset($object->qualities)) {
                foreach ($object->qualities as $group) {
                    $isqualitygroup = true;
                    foreach ($group as $quality) {
                        // some qualities are a simple array vs two-dimensional array
                        if (!is_object($quality)) {
                            $isqualitygroup = false;
                            break;
                        }

                        foreach ($repo->raw("quality.$quality->id") as $item_id) {
                            $item = $repo->get("item.$item_id");
                            if (isset($item->type) && strtoupper($item->type) == "VEHICLE_PART") {
                                continue;
                            }
                            if ($this->itemQualityLevel($item, $quality->id) < $quality->level) {
                                continue;
                            }
                            $repo->addUnique("construction.$item_id", $id);
                        }
                    }
                    if (!$isqualitygroup) {
                        foreach ($repo->raw("quality.$group->id") as $item_id) {
                            $item = $repo->get("item.$item_id");
                            if (isset($item->type) && strtoupper($item->type) == "VEHICLE_PART") {
                                continue;
                            }
                            if ($this->itemQualityLevel($item, $group->id) < $group->level) {
                                continue;
                            }
                            $repo->addUnique("construction.$item_id", $id);
                        }
                    }
                }
            }
        }

        $timediff = microtime(true) - $starttime;
        echo "Construction post-processing ".number_format($timediff, 3)." s.\n";
    }
}
