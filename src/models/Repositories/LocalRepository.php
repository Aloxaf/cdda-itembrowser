<?php

namespace Repositories;

use function DeepCopy\deep_copy;

class LocalRepository extends Repository implements RepositoryInterface, RepositoryParserInterface, RepositoryWriterInterface
{
    // provides a unique ID for each JSON entry added to cache, some JSON entries will overlap in text IDs
    private $id;
    // connection from repo IDs (generated from $id) to JSON objects
    private $database;
    // connection from JSON names to repo IDs (generated from $id)
    private $index;
    // references the git version if available
    private $version;
    private $source;

    // holds references to all object by repo_id, so they can be found during a copy-from lookup
    private $simpleindex;
    private $simplerecipeindex;
    private $simpleuncraftindex;
    // holds objects that could not find their copy-from template, they are reprocessed in a separate loop
    private $pending;

    private $events;

    private $sublist;

    // determine mod name when iterating through directories and attach
    private $currentmod;
    private $currentmodfoldername;

    private $blacklist;

    public function __construct(
        \Illuminate\Events\Dispatcher $events,
        \Illuminate\Foundation\Application $app
    ) {
        $this->events = $events;
        $this->app = $app;
        $this->blacklist = new \Ds\Set([
            "snippet", "talk_topic", "overmap_terrain", "scenario", "ammunition_type", "start_location",
            "MIGRATION", "item_action", "ITEM_CATEGORY", "mapgen", "speech", "keybinding", "region_overlay",
            "mod_tileset", "MONSTER_FACTION", "EXTERNAL_OPTION", "profession_item_substitutions", "dream",
            "rotatable_symbol", "ascii_art"
        ]);
        // TODO: ascii_art & dream
    }

    public function setSource($source)
    {
        $this->source = $source;
    }

    public function get_moddeps($object)
    {
        $moddeps = null;
        if (isset($object->modfoldername)) {
            // \Log::info("getting dependencies for $object->modfoldername\n");
            $resolvedmodident = $this->raw("modident.$object->modfoldername", null);

            if ($resolvedmodident !== null) {
                // \Log::info("getting dependencies for identified $resolvedmodident\n");
                $moddeps = $this->raw("moddep.$resolvedmodident", null);
            }
        }

        if ($moddeps !== null) {
            // \Log::info("Found ".count($moddeps)." dependencies.\n");
        }

        return $moddeps;
    }

    public function build_modid_prefix($modname)
    {
        $result = "";
        if ($modname !== null && $modname != "") {
            $result = "mod_".$modname."__";
        }

        return $result;
    }

    // determine which copyfromid to use in searching for item inheritance
    private function get_copyfrom_object($object, $modspacename)
    {
        $copyfromid = $modspacename.$object->{'copy-from'};
        if (isset($object->type) && $object->type == "vehicle_part") {
            $copyfromid = $modspacename."vpart_".$object->{'copy-from'};
        }
        // \Log::info("get_copyfrom_object checking $copyfromid\n");
        if (isset($object->type) && $object->type == "recipe") {
            $tempobj = $this->simplegetrecipe($copyfromid, null);
        } elseif (isset($object->type) && $object->type == "uncraft") {
            $tempobj = $this->simplegetuncraft($copyfromid, null);
        } else {
            $tempobj = $this->simpleget($copyfromid, null);
        }

        return $tempobj;
    }

    // TODO: Item.php 里也有一份，使用那里的而不是复制过来
    public function flattenVolume($val)
    {
        if (isset($val) && is_string($val)) {
            if (stripos($val, "ml") !== false) {
                $val = floatval($val) / 1000.0;
            } else {
                $val = floatval($val);
            }
        }

        return $val;
    }

    public function flattenWeight($val)
    {
        if (isset($val) && is_string($val)) {
            if (stripos($val, "kg") !== false) {
                $val = floatval($val) * 1000.0;
            } elseif (stripos($val, "mg") !== false) {
                $val = floatval($val) / 1000.0;
            } else {
                $val = floatval($val);
            }
        }

        return $val;
    }

    private function determine_copyfrom_origin($object)
    {
        $tempobj = null;

        if (!isset($object->copyfrom_id) || $object->copyfrom_id != $object->{'copy-from'}) {
            $tempobj = $this->get_copyfrom_object($object, $object->modspace);
        }

        // if the referenced template is not available, store it in $pending for later, or wait for the next $pending review loop
        if ($tempobj === null) {
            $moddeps = $this->get_moddeps($object);
            if ($moddeps !== null) {
                foreach ($moddeps as $modident) {
                    // \Log::info("checking copy-from mod dependency $modident\n");
                    $tempobj = $this->get_copyfrom_object($object, $this->build_modid_prefix($modident));
                    if ($tempobj !== null) {
                        break;
                    }
                }
            }

            if ($tempobj === null) {
                $tempobj = $this->get_copyfrom_object($object, "");
            }
        }

        return $tempobj;
    }

    private function getidfield($object)
    {
        $result = "";
        if (isset($object->result)) {
            $result = $object->result;
        }
        if (isset($object->id)) {
            $result = $object->id;
        }
        if (isset($object->abstract)) {
            $result = $object->abstract;
        }

        return $result;
    }

    private function newObject($object)
    {
        // skip snippets and talk topics for now
        // 因为此处已经判断是否存在 $object->type 了，所以后面不需要再判断了
        if (!isset($object->type)) {
            return;
        }

        if ($this->blacklist->contains($object->type)) {
            return;
        }

        // manually skip other water definitions for now
        if (isset($object->id) && $object->id == "water" && $object->type != "COMESTIBLE" && $object->type != "material") {
            return;
        }

        if ($object->type == "recipe" && isset($object->category) && $object->category == "CC_BUILDING") {
            return;
        }

        if ($object->type == "requirement") {
            $object->id = "requirement_".$object->id;
        }

        // generate a new repo ID if we are not currently processing pending objects
        if (!array_key_exists("repo_id", $object)) {
            $object->repo_id = $this->id++;
        }

        // move abstract field to id field so abstracts can be referenced in copy-from logic
        if (array_key_exists("abstract", $object)) {
            if (($object->type == "recipe" || $object->type == "uncraft") && !isset($object->result)) {
                $object->result = $object->abstract;
            } elseif (!array_key_exists("id", $object)) {
                $object->id = $object->abstract;
            }
        }
        // handle vpart naming replacement here so abstracts are covered
        if (!isset($object->vpartadded) && $object->type == "vehicle_part") {
            $object->id = "vpart_".$object->id;
            $object->vpartadded = true;
        }

        if (!isset($object->modadded)) {
            if (!isset($object->modspace)) {
                if (isset($this->currentmodfoldername)) {
                    $object->modspace = $this->build_modid_prefix($this->currentmodfoldername);
                } else {
                    $object->modspace = "_dda_";
                }
            }

            if (isset($object->id)) {
                $object->original_id = $object->id;
            }
            $object->modfoldername = $this->currentmodfoldername;
            if ($object->modspace == "_dda_") {
                $object->modspace = "";
            } else {
                $object->modspace = $this->build_modid_prefix($this->currentmodfoldername);
            }
            if ($object->type == "monstergroup") {
                $this->append("monstergroup_multi.$object->name", $object->repo_id);
            } else if ($object->type == "item_group" || $object->type == "harvest") {
                $this->append("itemgroup_multi.{$object->id}", $object->repo_id);
            } else if (strtolower($object->type) == "monster") {
                $this->append("monster_multi.$object->id", $object->repo_id);
            } else if ($object->type == "mutation") {
                $this->append("mutation_multi.$object->id", $object->repo_id);
            }
            if (isset($object->id) && $object->modspace != "_dda_") {
                $object->copyfrom_id = $object->modspace.$object->id;

                // 重复 ID 的话, 每次都将先前的名称加到现在名称的前面
                if (isset($object->name)) {
                    if (is_array($object->name)) {
                        if (is_string($object->name[0])) {
                            $name = $object->name[0];
                        }
                    } else if (is_string($object->name)) {
                        $name = $object->name;
                    } else if (is_object($object->name) && isset($object->name->str)) {
                        $name = $object->name->str;
                    } else if (isset($object->name->str_sp)) {
                        $name = $object->name->str_sp;
                    } else {
                        echo "There is no name for {$object->id}\n";
                    }
                    if (isset($name)) {
                        $this->appendUnique("item_multi.name.$object->id", $name);
                    }
                }

                $this->append("item_multi.$object->id", $object->repo_id);
                // \Log::info("item_multi.$object->id\n", array($this->raw("item_multi.$object->id")));
            }
            $object->modadded = true;
        }

        $display_object_id = "";
        if (isset($object->id)) {
            $display_object_id = $display_object_id.$object->id;
        }
        if (isset($object->result)) {
            $display_object_id = $display_object_id.$object->result;
        }
        if (isset($object->abstract)) {
            $display_object_id = $display_object_id."[$object->abstract]";
        }
        $display_object_id = $display_object_id.":$object->type";
        if (isset($object->modspace)) {
            $display_object_id = $display_object_id." ($object->modspace)";
        }
        // \Log::info($display_object_id);

        // handle template copying in cataclysm JSON
        if (array_key_exists("copy-from", $object)) {
            // \Log::info("checking copy-from value ".$object->{'copy-from'}."\n");
            $tempobj = $this->determine_copyfrom_origin($object);
            if ($tempobj === null) {
                // \Log::info("copy-from: didn't find object\n");
                if (isset($this->pending[$object->repo_id])) {
                    // \Log::info("copy-from: object already in pending\n");
                    return;
                } else {
                    $this->pending[$object->repo_id] = $object;
                    // \Log::info("copy-from: object added to pending\n");
                    return;
                }
            }
            // 避免处理 relative tag 时牵一发而动全身
            $tempobj = deep_copy($tempobj);
            // \Log::info("copy-from ".$this->getidfield($tempobj).":$tempobj->type ($tempobj->modspace)\n");

            // copy all template fields that are not already populated in the current item
            foreach ($tempobj as $subkey => $subobject) {
                if ((!array_key_exists($subkey, $object)
                        || (
                            (is_object($object->{$subkey}) || is_array($object->{$subkey}))
                            && !(array) $object->{$subkey}))
                    && $subkey != "abstract"
                ) {
                    $object->{$subkey} = $subobject;
                }
            }

            if (array_key_exists("extend", $object)) {
                foreach ($object->extend as $k => $v) {
                    if (is_array($v)) {
                        $object->$k = array_merge($object->$k ?? array(), $v);
                    } else if (!is_object($v)) {
                        $object->$k = $v;
                    } else {
                        echo "Unsupport type of extend\n";
                    }
                }
            }

            // clear the pending list of the current item if template was found
            if (isset($this->pending[$object->repo_id])) {
                unset($this->pending[$object->repo_id]);
            }
        }

        // handle properties that are modified by addition/multiplication
        // the property is removed after application, since each template reference can have its own modifiers
        // 提前处理，因为需要 copy-from 时前一个物品的 relative 已经被处理了
        if (array_key_exists("relative", $object)) {
            foreach ($object->relative as $relkey => $relvalue) {
                if (isset($object->{$relkey})) {
                    // echo $relkey."\n";
                    if ($relkey == "//") {
                        continue;
                    }

                    // handle values containing unit measurements
                    if ($relkey == "volume" || $relkey == "barrel_length") {
                        $tempval = $this->flattenVolume($relvalue);
                        $object->{$relkey} = $this->flattenVolume($object->{$relkey});
                        $object->{$relkey} += $tempval;
                    } elseif ($relkey == "weight") {
                        $tempval = $this->flattenWeight($relvalue);
                        $object->{$relkey} = $this->flattenWeight($relvalue);
                        $object->{$relkey} += $tempval;
                    } elseif ($relkey == "vitamins" && is_array($relvalue)) {
                        // special processing for vitamins (array with 2 indices, vitamin and count)
                        foreach ($relvalue as $vitamin_unit_key => $vitamin_unit) {
                            $found_vitamin = false;
                            foreach ($object->{$relkey} as $dest_vitamin_unit_key => $dest_vitamin_unit) {
                                if ($dest_vitamin_unit[0] == $vitamin_unit[0]) {
                                    $found_vitamin = true;
                                    $object->{$relkey}[$dest_vitamin_unit_key][1] += $vitamin_unit[1];
                                    break;
                                }
                            }
                            if (!$found_vitamin) {
                                array_push($object->{$relkey}, $vitamin_unit);
                            }
                        }
                    } elseif (($relkey == "damage" || $relkey == "ranged_damage") && is_object($relvalue)) {
                        foreach ($relvalue as $k => $v) {
                            if (isset($object->{$relkey}->{$k}) && is_numeric($object->{$relkey}->{$k})) {
                                $object->{$relkey}->{$k} += $v;
                            } elseif (isset($object->{$relkey}->{$k})) {
                                $object->{$relkey}->{$k} = $v;
                            } elseif ($relkey == "ranged_damage") {
                                if ($k == "amount") {
                                    $object->ranged_damage += $v;
                                } else if ($k != "damage_type") {
                                    echo "$object->id has a relative key that did not process correctly: $relkey =>".var_dump($relvalue)."\n";
                                }
                            } elseif ($relkey == "damage") {
                                if ($k != "damage_type" && isset($object->damage->{$k})) {
                                    $object->damage->{$k} += $v;
                                }
                            } else {
                                echo "$object->id has a relative key that did not process correctly: $relkey =>".var_dump($relvalue)."\n";
                            }
                        }
                    } else {
                        try {
                            $object->{$relkey} += $relvalue;
                        } catch (\Exception $e) {
                            echo "$object->id has a relative key that did not process correctly: $relkey"."\n";
                            // throw $e;
                        }
                    }
                }
            }
            unset($object->relative);
        }

        // handle delete tag
        if (array_key_exists("delete", $object)) {
            //iterate through each object defined in delete tag
            foreach ($object->delete as $delkey => $delvalue) {
                if (isset($object->{$delkey})) {
                    $delvaluearray = $delvalue;
                    if (!is_array($delvalue)) {
                        $delvaluearray = array();
                        $delvaluearray[] = $delvalue;
                    }

                    //iterate through each item in the array for a delete item
                    foreach ($delvaluearray as $inspectobjvalue) {
                        // if delete item is an array, compare arrays before deleting
                        if (is_array($inspectobjvalue)) {
                            if (is_array($object->{$delkey})) {
                                for ($i = 0; $i < count($object->{$delkey}); $i++) {
                                    if (is_array($object->{$delkey}[$i])) {
                                        // a matching array will be spliced out
                                        if (count(array_diff($object->{$delkey}[$i], $inspectobjvalue)) == 0) {
                                            array_splice($object->{$delkey}, $i, 1);
                                            $i--;
                                        }
                                    }
                                }
                            }
                        } else {
                            // if delete item is a single item, compare array of single items to find matching items to delete
                            if (is_array($object->{$delkey})) {
                                for ($i = 0; $i < count($object->{$delkey}); $i++) {
                                    if ($object->{$delkey}[$i] == $inspectobjvalue) {
                                        array_splice($object->{$delkey}, $i, 1);
                                        $i--;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // store basic ID into simple array for lookup later for resolving copy-from templates
        if (!($object->obsolete ?? false)) {
            if ($object->type == "recipe") {
                $this->simplesetrecipe($object->result, $object->repo_id);
            } elseif ($object->type == "uncraft") {
                $this->simplesetuncraft($object->result, $object->repo_id);
            } elseif (array_key_exists("id", $object)) {
                $this->simpleset($object->id, $object->repo_id);
            } elseif ($object->type != "monstergroup") {
                echo "There is no id for an object type {$object->type}\n"; //.var_dump($object);
            }
        }

        try {
            // ask each indexer to process the new object
            $this->events->dispatch("cataclysm.newObject", array($this, $object));
        } catch (\Exception $e) {
            $str = "";
            if (isset($object->id)) {
                $str = $object->id;
            } elseif (isset($object->result)) {
                $str = $object->result;
            }
            echo $str." had an error.\n".$e;
            // throw $e;
        }

        // store the updated object into the repo
        $this->database[$object->repo_id] = $object;
    }

    private function modDirectory($path, $id)
    {
        $mods = array_filter(glob("$path/data/mods/*"), "is_dir");
        foreach ($mods as $mod) {
            $modinfo = $this->find_modinfo_json($mod);
            if ($modinfo === null) {
                \Log::info("[modDirectory] Could not find modinfo.json in $mod");
                continue;
            }
            // JSON structure is different than earlier mod versions
            if (is_array($modinfo)) {
                if ($modinfo[0]->id == $id) {
                    return $mod;
                }
            } else {
                if ($modinfo->id == $id) {
                    return $mod;
                }
            }
        }
    }

    private function find_modinfo_json($path)
    {
        $filepath = "$path/modinfo.json";
        if (!file_exists($filepath)) {
            $otherpathlist = array_filter(glob("$path/data/mods/*"), "is_dir");
            foreach ($otherpathlist as $otherpath) {
                $filepath = "$otherpath/modinfo.json";
                if (file_exists($filepath)) {
                    break;
                }
            }
        }
        if (file_exists($filepath)) {
            return json_decode(file_get_contents($filepath));
        }

        return null;
    }

    // retrieve the directories where JSON files are contained
    private function dataPaths($path)
    {
        $default_mods_data = json_decode(file_get_contents("$path/data/mods/default.json"));

        // a new path (core) was added, contains basic json definitions
        $paths = array("$path/data/core", "$path/data/json");

        // add default-loaded mods to the path list
        foreach ($default_mods_data[0]->dependencies as $mod) {
            $paths[] = $this->modDirectory($path, $mod);
        }

        $modlist = array_filter(glob("$path/data/mods/*"), "is_dir");
        $this->set("modlist", array());

        foreach ($modlist as $mod) {
            $modinfo = $this->find_modinfo_json($mod);
            if ($modinfo === null) {
                \Log::info("[dataPaths] Could not find modinfo.json in $mod");
                continue;
            }
            $isolatedname = "dda";
            if (stripos($mod, "data/mods") !== false) {
                $aftermodstring = substr($mod, stripos($mod, "data/mods") + 10);
                if (stripos($aftermodstring, "/") !== false) {
                    $aftermodstringcutoff = stripos($aftermodstring, "/");
                    $isolatedname = substr($aftermodstring, 0, $aftermodstringcutoff);
                } else {
                    $isolatedname = $aftermodstring;
                }
                $isolatedname = strtolower($isolatedname);
            }

            // JSON structure is different than earlier mod versions
            if (is_array($modinfo)) {
                $paths[] = $mod;
                $id = "dda";
                if (isset($modinfo[0]->id)) {
                    $id = strtolower($modinfo[0]->id);
                }

                if ($id != "dda") {
                    $this->append("modlist", $id);
                }

                $this->set("modident.$isolatedname", $id);
                if (isset($modinfo[0]->dependencies)) {
                    foreach ($modinfo[0]->dependencies as $dep) {
                        if ($dep != "dda") {
                            $this->append("moddep.$id", strtolower($dep));
                        }
                    }
                }
                $modname = $modinfo[0]->name;
                if (isset($modinfo[0]->obsolete) && $modinfo[0]->obsolete == true) {
                    $modname = "过时：".$modinfo[0]->name;
                }
                $this->set("modname.$isolatedname", $modname);
            } else {
                $paths[] = $mod;
                $id = "dda";
                if (isset($modinfo->id)) {
                    $id = strtolower($modinfo->id);
                }
                $this->set("modident.$isolatedname", $id);
                if (isset($modinfo->dependencies)) {
                    foreach ($modinfo->dependencies as $dep) {
                        if ($dep != "dda") {
                            $this->append("moddep.$id", strtolower($dep));
                        }
                    }
                }
                $modname = $modinfo->name;
                if (isset($modinfo->obsolete) && $modinfo->obsolete == true) {
                    $modname = "过时：".$modinfo->name;
                }
                $this->set("modname.$isolatedname", $modname);
            }
        }

        return $paths;
    }

    private function cleanupModname($object)
    {
        if (isset($object->modspace)) {
            if ($object->modspace=="_dda_" || $object->modspace=="") {
                $object->modname="_";
            } else {
                $object->modname=$object->modfoldername;
            }
        }
        if (isset($object->modname) && ($object->modname == "_" || $object->modname == "")) {
            // $object->modname="_";
            unset($object->modname);
        }
        unset($object->modspace);
        unset($object->modfoldername);
        unset($object->modadded);
    }

    private function cleanupFields($object)
    {
        unset($object->copyfrom_id);
    }

    // main function to load JSON entries
    public function read()
    {
        $path = $this->source;

        $this->database = array();

        //starting from zero causes issues
        $this->id = 1;
        $this->index = array();

        $this->simpleindex = array();
        $this->simplerecipeindex = array();
        $this->simpleuncraftindex = array();

        $this->pending = array();
        $this->sublist = array();

        $paths = $this->dataPaths($path);

        echo "[Main] Processing objects...\n";

        foreach ($paths as $currPath) {
            $it = new \RecursiveDirectoryIterator($currPath);
            foreach (new \RecursiveIteratorIterator($it) as $file) {
                $data = (array) json_decode(file_get_contents($file));
                if (stripos($currPath, "data/json") !== false || stripos($currPath, "data/core") !== false) {
                    $this->currentmod = "_dda_";
                    $this->currentmodfoldername = "";
                }
                if (stripos($currPath, "data/mods") !== false) {
                    $aftermodstring = substr($currPath, stripos($currPath, "data/mods") + 10);
                    if (stripos($aftermodstring, "/") !== false) {
                        $aftermodstringcutoff = stripos($aftermodstring, "/");
                        $isolatedname = substr($aftermodstring, 0, $aftermodstringcutoff);
                    } else {
                        $isolatedname = $aftermodstring;
                    }
                    $isolatedname = strtolower($isolatedname);
                    $this->currentmod = "mod_".$isolatedname."__";
                    $this->currentmodfoldername = $isolatedname;
                }

                if (count($data) > 0) {
                    // check if a data type is available, otherwise the JSON file isn't compatible
                    if (!array_key_exists("type", $data)) {
                        if (substr($file, -12) != "modinfo.json") {
                            array_walk($data, array($this, 'newObject'));
                        }
                    }
                }
            }
        }

        echo "[Main] Processing objects with copy-from dependencies...\n";

        // reprocess all pending array entries until array is empty
        $pendRetry = 0;
        $pendingIterations = 0;
        while (count($this->pending) > 0) {
            $pendingIterations++;
            $pcount = count($this->pending);
            echo "[Pending] Round $pendingIterations ($pcount)\n";
            $pendingCount = count($this->pending);
            array_walk($this->pending, array($this, 'newObject'));

            // in case no pending entries could be resolved in one cycle, loop is finished
            if (count($this->pending) >= $pendingCount) {
                $pendRetry++;
                if ($pendRetry > 2) {
                    echo $pendingCount." pending items left out.\n";
                    // var_dump($this->pending);
                    break;
                }
            }
        }

        // simplify mod reference variables to reduce cache size
        array_walk($this->database, array($this, 'cleanupModname'));
        array_walk($this->database, array($this, 'cleanupFields'));

        // load special replacements for ingame features
        if (!$this->get("item.toolset")) {
            $this->newObject(json_decode('{
                "id":"toolset",
                "name":"integrated toolset",
                "type":"_SPECIAL",
                "description":"A fake item. It represents a feature in-game that does not normally exist as an item."
            }'));
        }
        $this->newObject(json_decode('{
            "id":"fire",
            "name":"nearby fire",
            "type":"_SPECIAL",
            "description":"A fake item. It represents a feature in-game that does not normally exist as an item."
            }'));
        $this->newObject(json_decode('{
            "id":"cvd_machine",
            "name":"cvd machine",
            "type":"_SPECIAL",
            "description":"A fake item. It represents a feature in-game that does not normally exist as an item."
        }'));
        $this->newObject(json_decode('{
            "id":"apparatus",
            "name":"a smoking device and a source of flame",
            "type":"_SPECIAL",
            "description":"A fake item. It represents a feature in-game that does not normally exist as an item."
        }'));

        $this->version = $this->getVersion($path);

        echo "[Main] Post-processing for loaded objects...\n";
        $this->events->dispatch("cataclysm.finishedLoading", array($this));
        echo "[Main] Post-processing for loaded objects finished.\n";

        return array($this->database, $this->index);
    }

    public function add_substitute($id, $sub)
    {
        if (!isset($this->sublist[$sub])) {
            $this->sublist[$sub] = array();
        }
        array_push($this->sublist[$sub], $id);
    }

    public function get_substitute($id)
    {
        $result = array();
        if (isset($this->sublist[$id])) {
            $result = $this->sublist[$id];
        }

        return $result;
    }

    // save an index to an object, without category context
    private function simpleset($index, $value)
    {
        $this->simpleindex[$index] = $value;
    }

    private function simpleget($index, $default = null)
    {
        if (isset($this->simpleindex[$index])) {
            $temprepokey = $this->simpleindex[$index];

            return $this->database[$temprepokey];
        }

        return $default;
    }

    // save an index to an object, with recipe context
    private function simplesetrecipe($index, $value)
    {
        $this->simplerecipeindex[$index] = $value;
    }

    private function simplegetrecipe($index, $default = null)
    {
        if (isset($this->simplerecipeindex[$index])) {
            $temprepokey = $this->simplerecipeindex[$index];

            return $this->database[$temprepokey];
        }

        return $default;
    }

    // save an index to an object, with recipe context
    private function simplesetuncraft($index, $value)
    {
        $this->simpleuncraftindex[$index] = $value;
    }

    private function simplegetuncraft($index, $default = null)
    {
        if (isset($this->simpleuncraftindex[$index])) {
            $temprepokey = $this->simpleuncraftindex[$index];

            return $this->database[$temprepokey];
        }

        return $default;
    }

    // save an index to an object
    public function set($index, $value)
    {
        $this->index[$index] = $value;
    }

    public function get($index, $default = null)
    {
        $repo_id = $this->raw($index, null);

        if ($repo_id === null) {
            return $default;
        }

        return $this->database[$repo_id];
    }

    public function raw($index, $default = array())
    {
        if (!isset($this->index[$index])) {
            return $default;
        }

        return $this->index[$index];
    }

    public function append($index, $value)
    {
        $this->index[$index][] = $value;
    }

    public function appendUnique($index, $value)
    {
        $addnew = false;
        if (isset($this->index[$index])) {
            if (!in_array($value, $this->index[$index])) {
                $addnew = true;
            }
        } else {
            $addnew = true;
        }
        if ($addnew) {
            $this->index[$index][] = $value;
        }
    }

    public function appendcreate($index, $value)
    {
        if ($this->get($index) === null) {
            $this->index[$index] = array();
        }
        $this->index[$index][] = $value;
    }

    public function addUnique($index, $value)
    {
        $this->index[$index][$value] = $value;
    }

    public function sort($index)
    {
        $data = $this->raw($index);
        sort($data);
        $this->set($index, $data);
    }

    private function getVersion($path)
    {
        $version_file = "$path/src/version.h";

        if (file_exists($version_file)) {
            $data = @file_get_contents($version_file);
        } else {
            return "unknown_version";
        }

        return substr($data, 17, -2);
    }

    public function version()
    {
        return $this->version;
    }

    public function getrepo($repo_id, $default = null)
    {
        return null;
    }
}
