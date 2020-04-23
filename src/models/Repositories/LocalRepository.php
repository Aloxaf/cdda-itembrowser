<?php

namespace Repositories;

putenv('LANG=zh_CN.utf8');   
setlocale(LC_ALL, 'zh_CN.utf8');  //指定要用的语系，如：en_US、zh_CN、zh_TW   
$domain = 'cataclysm-dda';                     //域名，可以任意取个有意义的名字，不过要跟相应的.mo文件的文件名相同（不包括扩展名）。
bindtextdomain($domain , "/cdda/locale/"); //设置某个域的mo文件路径    
bind_textdomain_codeset($domain, 'UTF-8');  //设置mo文件的编码为UTF-8    
textdomain($domain);                    //设置gettext()函数从哪个域去找mo文件 

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

    public function __construct(
        \Illuminate\Events\Dispatcher $events,
        \Illuminate\Foundation\Application $app
    ) {
        $this->events = $events;
        $this->app = $app;
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

    private function trans($string)
    {
        if (!is_string($string)) {
            echo "NOT STRING: ".var_dump($string)."\n";
            debug_print_backtrace(1, 2);
        }
        return gettext($string);
    }

    private function translate_skill($name)
    {
        if (gettext($name) != $name) {
            return gettext($name);
        } else {
            $trans = array(
                "firstaid" => "急救",
                "gun" => "枪法",
                "launcher" => "重武器",
                "tailor" => "裁缝",
                "traps" => "陷阱",
                "pistol" => "手枪",
                "rifle" => "步枪",
                "shotgun" => "霰弹枪",
                "stabbing" => "刺击武器",
                "bashing" => "钝击武器",
                "computer" => "计算机",
            );
            if (!isset($trans[$name])) {
                // debug_print_backtrace(1, 2);
                return $name;
            }
            return $trans[$name];
        }
    }

    private function newObject($object)
    {
        if (isset($object->name)) {
            if (is_array($object->name)) {
                if (is_string($object->name[0])) {
                    $object->name[0] = $this->trans($object->name[0]);
                }
            } else if (is_string($object->name)) {
                $object->name = $this->trans($object->name);
            } else if (is_object($object->name) && isset($object->name->ctxt)) {
                $object->name = trans("{$object->name->ctxt}\004{$object->name->str}");
            } else if (is_object($object->name) && isset($object->name->str)) {
                $object->name->str = $this->trans($object->name->str);
            }
        }
        if (isset($object->description) && is_string($object->description)) {
            $object->description = $this->trans($object->description);
        }
        if (isset($object->location)) {
            $object->location = $this->trans($object->location);
        }
        if (isset($object->skill_used)) {
            $object->skill_used = $this->translate_skill($object->skill_used);
        }
        if (isset($object->skills_required)) {
            foreach ($object->skills_required as $k => $skill) {
                if (is_string($skill)) {
                    $object->skills_required[$k] = $this->translate_skill($skill);
                } else if (is_array($skill)) {
                    $object->skills_required[$k][0] = $this->translate_skill($skill[0]);
                }
            }
        }
        if (isset($object->type) && $object->type == "GUNMOD") {
            if (isset($object->mod_targets)) {
                foreach ($object->mod_targets as $k => $target) {
                    // gun_type_type 为 msgctxt
                    $text = $this->trans("gun_type_type\004{$target}");
                    if ($text != "gun_type_type\004{$target}" && $target != "ar15") {
                        $object->mod_targets[$k] = $text;
                    }
                }
            }
        }
        // 武器可用模组
        if (isset($object->valid_mod_locations)) {
            foreach ($object->valid_mod_locations as $k => $v) {
                $object->valid_mod_locations[$k][0] = $this->trans($v[0]);
            }
        }

        // skip snippets and talk topics for now
        if (!isset($object->type)) {
            return;
        }
        if ($object->type == "snippet" || $object->type == "talk_topic" || $object->type == "overmap_terrain" || $object->type == "scenario" || $object->type == "ammunition_type" ||
        $object->type == "start_location" || $object->type == "effect_type" || $object->type == "MIGRATION" || $object->type == "item_action" || $object->type == "ITEM_CATEGORY") {
            return;
        }

        // manually skip other water definitions for now
        if (isset($object->id) && $object->id == "water" && isset($object->type) && $object->type != "COMESTIBLE") {
            return;
        }

        if (isset($object->type) && $object->type == "recipe" && isset($object->category) && $object->category == "CC_BUILDING") {
            return;
        }

        if (isset($object->type) && $object->type == "requirement") {
            $object->id = "requirement_".$object->id;
        }

        // generate a new repo ID if we are not currently processing pending objects
        if (!array_key_exists("repo_id", $object)) {
            $object->repo_id = $this->id++;
        }

        // move abstract field to id field so abstracts can be referenced in copy-from logic
        if (array_key_exists("abstract", $object)) {
            if (isset($object->type) && ($object->type == "recipe" || $object->type == "uncraft") && !isset($object->result)) {
                $object->result = $object->abstract;
            } elseif (!array_key_exists("id", $object)) {
                $object->id = $object->abstract;
            }
        }
        // handle vpart naming replacement here so abstracts are covered
        if (!isset($object->vpartadded) && isset($object->type) && $object->type == "vehicle_part") {
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
            }
            if (strtolower($object->type) == "monster") {
                $this->append("monster_multi.$object->id", $object->repo_id);
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
                    // TODO: 更多 name 格式
                    } else if (is_object($object->name) && isset($object->name->str)) {
                        $name = $object->name->str;
                    }
                    $this->appendUnique("item_multi.name.$object->id", $name);
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
            // \Log::info("copy-from ".$this->getidfield($tempobj).":$tempobj->type ($tempobj->modspace)\n");

            // copy all template fields that are not already populated in the current item
            foreach ($tempobj as $subkey => $subobject) {
                if (!array_key_exists($subkey, $object) && $subkey != "abstract") {
                    $object->{$subkey} = $subobject;
                }
            }

            // clear the pending list of the current item if template was found
            if (isset($this->pending[$object->repo_id])) {
                unset($this->pending[$object->repo_id]);
            }
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
        if (isset($object->type) && $object->type == "recipe") {
            $this->simplesetrecipe($object->result, $object->repo_id);
        } elseif (isset($object->type) && $object->type == "uncraft") {
            $this->simplesetuncraft($object->result, $object->repo_id);
        } else {
            if (array_key_exists("id", $object)) {
                $this->simpleset($object->id, $object->repo_id);
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
                if ($modinfo[0]->ident == $id) {
                    return $mod;
                }
            } else {
                if ($modinfo->ident == $id) {
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
        foreach ($default_mods_data->dependencies as $mod) {
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
                $ident = "dda";
                if (isset($modinfo[0]->ident)) {
                    $ident = strtolower($modinfo[0]->ident);
                }

                if ($ident != "dda") {
                    $this->append("modlist", $ident);
                }

                $this->set("modident.$isolatedname", $ident);
                if (isset($modinfo[0]->dependencies)) {
                    foreach ($modinfo[0]->dependencies as $dep) {
                        if ($dep != "dda") {
                            $this->append("moddep.$ident", strtolower($dep));
                        }
                    }
                }
                $modname = gettext($modinfo[0]->name);
                if (isset($modinfo[0]->obsolete) && $modinfo[0]->obsolete == true) {
                    $modname = "过时：".$modname;
                }
                $this->set("modname.$isolatedname", $modname);
            } else {
                $paths[] = $mod;
                $ident = "dda";
                if (isset($modinfo->ident)) {
                    $ident = strtolower($modinfo->ident);
                }
                $this->set("modident.$isolatedname", $ident);
                if (isset($modinfo->dependencies)) {
                    foreach ($modinfo->dependencies as $dep) {
                        if ($dep != "dda") {
                            $this->append("moddep.$ident", strtolower($dep));
                        }
                    }
                }
                $modname = gettext($modinfo->name);
                if (isset($modinfo->obsolete) && $modinfo->obsolete == true) {
                    $modname = "过时：".$modname;
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
                    var_dump($this->pending);
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
