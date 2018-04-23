<?php
namespace Repositories\Indexers;

use Repositories\RepositoryWriterInterface;
use CustomUtility\ValueUtil;

class Recipe implements IndexerInterface
{
    const DEFAULT_INDEX = "recipe";

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
        try {
            $skills = [];
            foreach ($repo->raw(self::DEFAULT_INDEX) as $id) {
                $recipe = $repo->get(self::DEFAULT_INDEX.".".$id);

                // handle group substitution of qualities, tools, and components
                if (isset($recipe->using)) {
                    foreach ($recipe->using as $usinggroup) {
                        $req = $repo->get("requirement.".$usinggroup[0]);
                        if (isset($req)) {
                            // the second value multiplies the values contained in the requirement
                            $multiplier = $usinggroup[1];

                            // apply component (crafting ingredient) substitutions
                            if (isset($req->components)) {
                                if (!isset($recipe->components)) {
                                    $recipe->components = array();
                                }
                                foreach ($req->components as $group_of_similar_components) {
                                    $new_component_groups=[];
                                    foreach ($group_of_similar_components as $similar_component_unit) {
                                        list($id, $base_amount) = $similar_component_unit;
                                        $mult_amount = $base_amount * $multiplier;
                                        if ($mult_amount < 0) {
                                            $mult_amount = -1;
                                        }

                                        $this->linkIndexes($repo, "toolFor", $id, $recipe);

                                        if ($recipe->category == "uncraft"
                                        or (isset($recipe->reversible)
                                        and $recipe->reversible == "true")) {
                                            $repo->append("item.disassembledFrom.$id", $recipe->repo_id);
                                        }

                                        $new_component_groups[] = array($id, $mult_amount);
                                    }
                                    if (count($new_component_groups)>0) {
                                        $recipe->components[] = $new_component_groups;
                                    }
                                }
                            }

                            // apply tool quality substitutions
                            if (isset($req->qualities)) {
                                if (!isset($recipe->qualities)) {
                                    $recipe->qualities = array();
                                }

                                foreach ($req->qualities as $quality_unit) {
                                    ValueUtil::SetDefault($quality_unit, "amount", 1);
                                    $recipe->qualities[] = $quality_unit;
                                }
                            }

                            // apply tool usage (charge/quantity) substitutions
                            if (isset($req->tools)) {
                                if (!isset($recipe->tools)) {
                                    $recipe->tools = array();
                                }

                                foreach ($req->tools as $group_of_similar_tools) {
                                    $new_tool_group = array();
                                    foreach ($group_of_similar_tools as $similar_tool_unit) {
                                        $new_tool_unit = [];
                                        list($id, $base_amount) = $similar_tool_unit;
                                        $mult_amount = $base_amount * $multiplier;
                                        if ($mult_amount < 0) {
                                            $mult_amount = -1;
                                        }
                                        $this->linkIndexes($repo, "toolFor", $id, $recipe);
                                        $new_tool_group[] = array($id, $mult_amount);
                                    }
                                    if (count($new_tool_group)>0) {
                                        $recipe->tools[] = $new_tool_group;
                                    }
                                }
                            }
                        }
                    }
                } // end "using" substitution

                // handle component "LIST" substitution
                if (isset($recipe->components)) {
                    foreach ($recipe->components as $cgkey => $similar_unit_group) {
                        $splice_list = array();
                        $newgrouplist = [];
                        $insert_into_group=false;
                        foreach ($similar_unit_group as $groupkey => $similar_unit) {
                            unset($unit_param_3);
                            list($u_id, $multiplier) = $similar_unit;
                            if (count($similar_unit)>2) {
                                $unit_param_3 = $similar_unit[2];
                            }
                            if (isset($unit_param_3) && $unit_param_3 == "LIST") {
                                $req = $repo->get("requirement.".$u_id);
                                if (isset($req) && isset($req->components)) {
                                    foreach ($req->components as $req_similar_unit_group) {
                                        foreach ($req_similar_unit_group as $req_similar_unit) {
                                            list($ru_id, $base_amount) = $req_similar_unit;
                                            $mult_amount = $base_amount * $multiplier;
                                            if ($mult_amount < 0) {
                                                $mult_amount = -1;
                                            }
                                            $this->linkIndexes($repo, "toolFor", $ru_id, $recipe);

                                            if ($recipe->category == "uncraft"
                                            or (isset($recipe->reversible)
                                            and $recipe->reversible == "true")) {
                                                $repo->append("item.disassembledFrom.$ru_id", $recipe->repo_id);
                                            }
                                            $newgrouplist[] = array($ru_id, $mult_amount);
                                        }
                                    }

                                    $splice_list[] = $groupkey;
                                }
                            } else {
                                $insert_into_group=true;
                            }
                        }
                        if (count($newgrouplist)>0) {
                            if ($insert_into_group==true) {
                                foreach ($newgrouplist as $groupitem) {
                                    $recipe->components[$cgkey][] = $groupitem;
                                }
                            } else {
                                $recipe->components[] = $newgrouplist;
                            }
                        }

                        if (count($splice_list)>0) {
                            $splice_count = 0;
                            foreach ($splice_list as $splice_number) {
                                $splice_number -= $splice_count;
                                array_splice($recipe->components[$cgkey], $splice_number, 1);
                                $splice_count++;
                            }
                        }

                        $splice_list2 = array();
                        foreach ($recipe->components as $tool_groupkey => $tool_group) {
                            if (count($tool_group)<1) {
                                $splice_list2[]=$tool_groupkey;
                            }
                        }
                        if (count($splice_list2)>0) {
                            $splice_count = 0;
                            foreach ($splice_list2 as $splice_number) {
                                $splice_number -= $splice_count;
                                array_splice($recipe->components, $splice_number, 1);
                                $splice_count++;
                            }
                        }
                    }
                } // end component "LIST" substitution

                // handle tool "LIST" substitution
                if (isset($recipe->tools)) {
                    foreach ($recipe->tools as $cgkey => $similar_unit_group) {
                        $splice_list = array();
                                $newgrouplist = [];
                        $insert_into_group=false;
                        foreach ($similar_unit_group as $groupkey => $similar_unit) {
                            unset($unit_param_3);
                            list($u_id, $multiplier) = $similar_unit;
                            if (count($similar_unit)>2) {
                                $unit_param_3 = $similar_unit[2];
                            }
                            if (isset($unit_param_3) && $unit_param_3 == "LIST") {
                                $req = $repo->get("requirement.".$u_id);
                                if (isset($req) && isset($req->tools)) {
                                    foreach ($req->tools as $req_similar_unit_group) {
                                        foreach ($req_similar_unit_group as $req_similar_unit) {
                                            list($ru_id, $base_amount) = $req_similar_unit;
                                            $mult_amount = $base_amount * $multiplier;
                                            if ($mult_amount < 0) {
                                                $mult_amount = -1;
                                            }

                                            $this->linkIndexes($repo, "toolFor", $ru_id, $recipe);

                                            $newgrouplist[] = array($ru_id, $mult_amount);
                                        }
                                    }

                                    $splice_list[] = $groupkey;
                                }
                            } else {
                                $insert_into_group=true;
                            }
                        }
                        if (count($newgrouplist) > 0) {
                            if ($insert_into_group == true) {
                                foreach ($newgrouplist as $groupitem) {
                                    $recipe->tools[$cgkey][] = $groupitem;
                                }
                            } else {
                                $recipe->tools[] = $newgrouplist;
                            }
                        }
                        if (count($splice_list)>0) {
                            $splice_count = 0;
                            foreach ($splice_list as $splice_number) {
                                $splice_number -= $splice_count;
                                array_splice($recipe->tools[$cgkey], $splice_number, 1);
                                $splice_count++;
                            }
                        }
                        $splice_list2 = array();
                        foreach ($recipe->tools as $tool_groupkey => $tool_group) {
                            if (count($tool_group)<1) {
                                $splice_list2[]=$tool_groupkey;
                            }
                        }
                        if (count($splice_list2)>0) {
                            $splice_count = 0;
                            foreach ($splice_list2 as $splice_number) {
                                $splice_number -= $splice_count;
                                array_splice($recipe->tools, $splice_number, 1);
                                $splice_count++;
                            }
                        }
                    }
                } // end tool "LIST" substitution

                if (isset($recipe->tools)) {
                    foreach ($recipe->tools as $toolgroupkey => $toolgroup) {
                        $toolsub = array();
                        foreach ($toolgroup as $toolunit) {
                            $subitemlist = $repo->get_substitute($toolunit[0]);
                            if (count($subitemlist)>0) {
                                $toolsub[] = array($subitemlist, $toolunit[1]);
                            }
                        }
                        if (count($toolsub)>0) {
                            foreach ($toolsub as $toolsubgroup) {
                                list($subgroup,$amount) = $toolsubgroup;
                                foreach ($subgroup as $subitem) {
                                    $recipe->tools[$toolgroupkey][] = array($subitem, $amount);
                                }
                            }
                        }
                    }
                }

                // search for all the items with the apropiate qualities
                if (isset($recipe->qualities)) {
                    foreach ($recipe->qualities as $group) {
                        foreach ($repo->raw("quality.$group->id") as $id) {
                            $item = $repo->get("item.$id");
                            if ($this->itemQualityLevel($item, $group->id)<$group->level) {
                                continue;
                            }
                            $this->linkIndexes($repo, 'toolFor', $id, $recipe);
                        }
                    }
                }

                if (isset($recipe->skill_used)) {
                    $skill = $recipe->skill_used;
                    $level = $recipe->difficulty;

                    $item = $repo->get("item.$recipe->result");

                    if ($item === null) {
                        var_dump($recipe);
                        print "missing recipe result $recipe->result\n";
                    }

                    $repo->append("skill.$skill.$level", $item->id);
                    $skills[$skill] = $skill;
                }
            }

            sort($skills);
            $repo->set("skills", $skills);
        } catch (Exception $e) {
            print "Exception encountered while linking recipe information.\n";
            throw $e;
        }
        $endtime = microtime(true);
        $timediff = $endtime-$starttime;
        print "Recipe post-processing $timediff s.\n";
    }

    private function linkIndexes($repo, $key, $id, $recipe)
    {
        // NONCRAFT recipes go directly to the disassembly index,
        // they are not needed anywhere else.
        if ($key == "recipes"
            and $recipe->type == "uncraft") {
            $repo->append("item.disassembly.$id", $recipe->repo_id);

            return;
        }

        // reversible recipes go to the disassembly index,
        // but they're used to craft, so process further indexes.
        if ($key == "recipes"
        and isset($recipe->reversible)
        and $recipe->reversible == "true") {
            $repo->append("item.disassembly.$id", $recipe->repo_id);
        }

        if ($key == "toolFor") {
            // create a list of recipe categories, excluding NONCRAFT.
            if ($recipe->type != "uncraft") {
                $category = $recipe->category;
                $repo->addUnique("item.categories.$id", $category);
            }

            // create a list of tools per category for this object.
            $repo->append(
                "item.toolForCategory.$id.$recipe->category",
                $recipe->repo_id
            );
        }

        $repo->append("item.$key.$id", $recipe->repo_id);
    }

    public function onNewObject(RepositoryWriterInterface $repo, $object)
    {
        try {
            if ($object->type == "recipe" || $object->type == "uncraft") {
                $recipe = $object;

                if ($recipe->type == "uncraft") {
                    ValueUtil::SetDefault($recipe, "category", "uncraft");
                }

                ValueUtil::SetDefault($recipe, "difficulty", 0);

                $repo->append(self::DEFAULT_INDEX, $recipe->repo_id);
                $repo->set(self::DEFAULT_INDEX.".".$recipe->repo_id, $recipe->repo_id);

                if (isset($recipe->result)) {
                    $this->linkIndexes($repo, "recipes", $recipe->result, $recipe);
                    if (isset($recipe->book_learn)) {
                        foreach ($recipe->book_learn as $learn) {
                            $this->linkIndexes($repo, "learn", $learn[0], $recipe);
                        }
                    }
                }

                if (isset($recipe->qualities)) {
                    foreach ($recipe->qualities as $group) {
                        ValueUtil::SetDefault($group, "amount", 1);
                    }
                }

                if (isset($recipe->byproducts)) {
                    for ($a = 0; $a < count($recipe->byproducts); $a++) {
                        if (count($recipe->byproducts[$a]) == 1) {
                            array_push($recipe->byproducts[$a], 1);
                        }
                    }
                }

                if (isset($recipe->tools)) {
                    foreach ($recipe->tools as $group) {
                        foreach ($group as $tool) {
                            list($id, $amount) = $tool;
                            $this->linkIndexes($repo, "toolFor", $id, $recipe);
                        }
                    }
                }

                if (isset($recipe->components)) {
                    foreach ($recipe->components as $group) {
                        foreach ($group as $component) {
                            unset($listval);
                            list($id, $amount) = $component;
                            if (count($component)>2) {
                                $listval = $component[2];
                            }
                            if (isset($listval) && $listval == "LIST") {
                                continue;
                            }
                            $this->linkIndexes($repo, "toolFor", $id, $recipe);

                            if ($recipe->category == "uncraft"
                            or (isset($recipe->reversible)
                            and $recipe->reversible == "true")) {
                                $repo->append("item.disassembledFrom.$id", $recipe->repo_id);
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            var_dump($object);
            if (isset($object)&&isset($object->result)) {
                print "Recipe for ".$object->result." has an error.\n";
            }
            throw $e;
        }
    }
}
