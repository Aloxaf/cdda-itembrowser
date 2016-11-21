<?php
namespace Repositories;

class LocalRepository extends Repository implements RepositoryInterface,
    RepositoryParserInterface, RepositoryWriterInterface
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
    // holds objects that could not find their copy-from template, they are reprocessed in a separate loop
    private $pending;

    private $events;

    public function __construct(
        \Illuminate\Events\Dispatcher $events,
        \Illuminate\Foundation\Application $app
    )
    {
        $this->events = $events;
        $this->app = $app;
    }

    public function setSource($source)
    {
        $this->source = $source;
    }

    private function newObject($object)
    {
        // skip snippets and talk topics for now
        if ($object->type == "snippet" || $object->type =="talk_topic") {
            return;
        }

        // generate a new repo ID if we are not currently processing pending objects
        if (!array_key_exists("repo_id", $object)) {
            $object->repo_id = $this->id++;
        }

        // move abstract field to id field so abstracts can be referenced in copy-from logic
        if (!array_key_exists("id", $object) && array_key_exists("abstract", $object)) {
            $object->id = $object->abstract;
        }

        // handle template copying in cataclysm JSON
        if (array_key_exists("copy-from", $object)) {
            $tempobj = $this->simpleget($object->{'copy-from'}, null);

            // if the referenced template is not available, store it in $pending for later, or wait for the next $pending review loop
            if ($tempobj === null) {
                if (isset($this->pending[$object->repo_id])) {
                    return;
                } else {
                    $this->pending[$object->repo_id] = $object;
                    return;
                }
            }

            // copy all template fields that are not already populated in the current item
            foreach($tempobj as $subkey => $subobject)
            {
                if(!array_key_exists($subkey,$object) && $subkey!="abstract")
                {
                    $object->{$subkey} = $subobject;
                }
            }

            // clear the pending list of the current item if template was found
            if (isset($this->pending[$object->repo_id])) {
                unset($this->pending[$object->repo_id]);
            }
        }

        // store basic ID into simple array for lookup later for resolving copy-from templates
        if (array_key_exists("id",$object)) {
            $this->simpleset($object->id,$object->repo_id);
        }

        // ask each indexer to process the new object
        $this->events->fire("cataclysm.newObject", array($this, $object));

        // store the updated object into the repo
        $this->database[$object->repo_id] = $object;
    }

    private function modDirectory($path, $id)
    {
        $mods = array_filter(glob("$path/data/mods/*"), "is_dir");
        foreach ($mods as $mod) {
            $modinfo = json_decode(file_get_contents("$mod/modinfo.json"));
            if ($modinfo->ident == $id) {
                return $mod;
            }
        }
    }

    // retrieve the directories where JSON files are contained
    private function dataPaths($path)
    {
        $default_mods_data = json_decode(file_get_contents("$path/data/mods/dev-default-mods.json"));

        // a new path (core) was added, contains basic json definitions
        $paths = array("$path/data/core", "$path/data/json");

        // add default-loaded mods to the path list
        foreach ($default_mods_data->dependencies as $mod) {
            $paths[] = $this->modDirectory($path, $mod);
        }

        return $paths;
    }

    // main function to load JSON entries
    public function read()
    {
        $path = $this->source;

        $this->database = array();
        $this->id = 0;
        $this->index = array();

        $this->simpleindex = array();
        $this->pending = array();

        $paths = $this->dataPaths($path);

        foreach ($paths as $currPath) {
            $it = new \RecursiveDirectoryIterator($currPath);
            foreach (new \RecursiveIteratorIterator($it) as $file) {
                $data = (array) json_decode(file_get_contents($file));

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

        // reprocess all pending array entries until array is empty
        while (count($this->pending) > 0) {
            $pendingCount = count($this->pending);
            array_walk($this->pending, array($this, 'newObject'));

            // in case no pending entries could be resolved in one cycle, loop is finished
            if (count($this->pending) >= $pendingCount) {
                break;
            }
        }

        // load special replacements for ingame features
        if (!$this->get("item.toolset")) {
            $this->newObject(json_decode('{
                "id":"toolset",
                "name":"integrated toolset",
                "type":"_SPECIAL",
                "description":"A fake item. It represents an feature in-game that does not normally exist as an item."
            }'));
        }
        $this->newObject(json_decode('{
            "id":"fire",
            "name":"nearby fire",
            "type":"_SPECIAL",
            "description":"A fake item. It represents an feature in-game that does not normally exist as an item."
            }'));
        $this->newObject(json_decode('{
            "id":"cvd_machine",
            "name":"cvd machine",
            "type":"_SPECIAL",
            "description":"A fake item. It represents an feature in-game that does not normally exist as an item."
        }'));
        $this->newObject(json_decode('{
            "id":"apparatus",
            "name":"a smoking device and a source of flame",
            "type":"_SPECIAL",
            "description":"A fake item. It represents an feature in-game that does not normally exist as an item."
        }'));

        $this->version = $this->getVersion($path);

        $this->events->fire("cataclysm.finishedLoading", array($this));

        return array($this->database, $this->index);
    }

    // save an index to an object, without category context
    private function simpleset($index, $value)
    {
        $this->simpleindex[$index] = $value;
    }

    private function simpleget($index, $default=null) {
        if (isset($this->simpleindex[$index])) {
            $temprepokey = $this->simpleindex[$index];
            return $this->database[$temprepokey];
        }
        return $default;
    }

    // save an index to an object
    public function set($index, $value)
    {
        $this->index[$index] = $value;
    }

    public function get($index, $default=null)
    {
        $repo_id = $this->raw($index, null);

        if($repo_id === null)
            return $default;

        return $this->database[$repo_id];
    }

    public function raw($index, $default=array())
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
        $data = file_get_contents($version_file);

        if ($data == false) {
            return "unknown_version";
        }

        return substr($data, 17, -2);
    }

    public function version()
    {
        return $this->version;
    }
}
