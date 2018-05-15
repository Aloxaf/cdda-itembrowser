<?php
namespace Repositories;

class LocalRepository extends Repository implements
    RepositoryInterface,
    RepositoryParserInterface,
    RepositoryWriterInterface
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

    private function newObject($object)
    {
        // skip snippets and talk topics for now
        if ($object->type == "snippet" || $object->type =="talk_topic") {
            return;
        }

        // manually skip other water definitions for now
        if (isset($object->id) && $object->id == "water" && isset($object->type) && $object->type != "COMESTIBLE") {
            return;
        }

        // //temporary exclusion of uncrafting recipes
        // if (isset($object->type)&&$object->type=="uncraft") {
        //     return;
        // }

        // if (isset($object->id) && $object->id=="radio_car") {
        //     var_dump($object);
        // }

        // generate a new repo ID if we are not currently processing pending objects
        if (!array_key_exists("repo_id", $object)) {
            $object->repo_id = $this->id++;
            // print $object->repo_id."\n";
        }
        // if (isset($object->id)) {
        //     print $object->id."\n";
        // } elseif (isset($object->result)) {
        //     print $object->result."\n";
        // } elseif (isset($object->abstract)) {
        //     print $object->abstract."\n";
        // }


        // move abstract field to id field so abstracts can be referenced in copy-from logic
        if (array_key_exists("abstract", $object)) {
            if (isset($object->type)&&($object->type=="recipe"||$object->type=="uncraft")&&!isset($object->result)) {
                $object->result = $object->abstract;
            } elseif (!array_key_exists("id", $object)) {
                $object->id = $object->abstract;
            }
        }
        // if (!array_key_exists("id", $object) && array_key_exists("abstract", $object)) {
        //     $object->id = $object->abstract;
        // }

        // handle template copying in cataclysm JSON
        if (array_key_exists("copy-from", $object)) {
            if (isset($object->type)&&$object->type=="recipe") {
                $tempobj = $this->simplegetrecipe($object->{'copy-from'}, null);
            } elseif (isset($object->type)&&$object->type=="uncraft") {
                $tempobj = $this->simplegetuncraft($object->{'copy-from'}, null);
            } else {
                $tempobj = $this->simpleget($object->{'copy-from'}, null);
            }
            // $tempobj = $this->simpleget($object->type."#".$object->{'copy-from'}, null);

            // if the referenced template is not available, store it in $pending for later, or wait for the next $pending review loop
            if ($tempobj === null) {
                if (isset($this->pending[$object->repo_id])) {
                    return;
                } else {
                    $this->pending[$object->repo_id] = $object;
                    return;
                }
            } else {
                // print "Loaded ".$object->{'copy-from'}." for repo ID $object->repo_id\n";
            }

            // copy all template fields that are not already populated in the current item
            foreach ($tempobj as $subkey => $subobject) {
                if (!array_key_exists($subkey, $object) && $subkey!="abstract") {
                    $object->{$subkey} = $subobject;
                }
            }

            // clear the pending list of the current item if template was found
            if (isset($this->pending[$object->repo_id])) {
                unset($this->pending[$object->repo_id]);
            }
        }

        // // store basic ID into simple array for lookup later for resolving copy-from templates
        // if (array_key_exists("type", $object) && ($object->type=="recipe"||$object->type=="uncraft")) {
        // } elseif (array_key_exists("id", $object)) {
        //     // $str = "";
        //     // if (isset($object->type)) {
        //     //     $str=$object->type;
        //     // }
        //     // $this->simpleset($str."#".$object->id, $object->repo_id);
        //     if ($this->simpleget($object->id) === null) {
        //         $this->simpleset($object->id, $object->repo_id);
        //     } else {
        //         print "omitting ".$object->id." from simpleget list, appears as duplicate\n";
        //         return;
        //     }
        // }

        // store basic ID into simple array for lookup later for resolving copy-from templates
        if (isset($object->type)&&$object->type=="recipe") {
            $this->simplesetrecipe($object->result, $object->repo_id);
        } elseif (isset($object->type)&&$object->type=="uncraft") {
            // print $object->result."\n";
            $this->simplesetuncraft($object->result, $object->repo_id);
        } else {
            if (array_key_exists("id", $object)) {
                $this->simpleset($object->id, $object->repo_id);
            }
        }

        // if (isset($object->id)) {
        //     if ($object->id=="radio_car") {
        //         // var_dump($object);
        //     }
        //     //print $object->type."#".$object->id."\n";
        // } else {
        //     if (isset($object->result)) {
        //         if ($object->result=="radio_car") {
        //             // var_dump($object);
        //         }
        //         //print $object->type."#".$object->result."\n";
        //     } else {
        //         // var_dump($object);
        //     }
        // }

        try {
            // ask each indexer to process the new object
            $this->events->fire("cataclysm.newObject", array($this, $object));
        } catch (Exception $e) {
            $str = "";
            if (isset($object->id)) {
                $str=$object->id;
            } elseif (isset($object->result)) {
                $str=$object->result;
            }
            print $str." had an error.\n";
            throw $e;
        }

        // store the updated object into the repo
        $this->database[$object->repo_id] = $object;
    }

    private function modDirectory($path, $id)
    {
        $mods = array_filter(glob("$path/data/mods/*"), "is_dir");
        foreach ($mods as $mod) {
            $modinfo = json_decode(file_get_contents("$mod/modinfo.json"));
            // JSON structure is different than earlier mod versions
            if ($modinfo[0]->ident == $id) {
                return $mod;
            }
        }
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

        return $paths;
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

        print "[Main] Processing objects...\n";

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

        print "[Main] Processing objects with copy-from dependencies...\n";

        // reprocess all pending array entries until array is empty
        $pendRetry = 0;
        $pendingIterations = 0;
        while (count($this->pending) > 0) {
            $pendingIterations++;
            print "[Pending] Round $pendingIterations\n";
            $pendingCount = count($this->pending);
            array_walk($this->pending, array($this, 'newObject'));

            // in case no pending entries could be resolved in one cycle, loop is finished
            if (count($this->pending) >= $pendingCount) {
                $pendRetry++;
                if ($pendRetry>5) {
                    print $pendingCount." pending items left out.\n";
                    // var_dump($this->pending);
                    break;
                }
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

        print "[Main] Post-processing for loaded objects...\n";
        $this->events->fire("cataclysm.finishedLoading", array($this));
        print "[Main] Post-processing for loaded objects finished.\n";

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
}
