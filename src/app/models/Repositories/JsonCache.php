<?php
namespace Repositories;

class JsonCache extends Json implements RepositoryInterface
{
  const CACHE_KEY="json";
  private $dataChunks;
  private $indexChunks;

  public function __construct()
  {
    $this->dataChunks = array();
    $this->indexChunks = array();
    parent::__construct();
  }

  protected function read()
  {
    $key = self::CACHE_KEY;

    $lock_fp = fopen(storage_path()."/cache/.json.lock", "w+");
    flock($lock_fp, LOCK_EX);

    if(\Cache::has("$key:loaded")) {
      flock($lock_fp, LOCK_UN);
      fclose($lock_fp);

      return;
    }

    parent::read();

    $database = $this->chopDatabase($this->database);
    foreach($database as $chunk=>$data) {
      \Log::info("saving database chunk $chunk: ". count($data).":". strlen(serialize($data)));
      \Cache::put("$key:db:$chunk", $data, 60);
    }

    $index = $this->chopIndex($this->index);
    foreach($index as $chunk=>$data)
    {
      \Log::info("saving index chunk $chunk: ". count($data).":". strlen(serialize($data)));
      \Cache::put("$key:index:$chunk", $data, 60);
    }

    \Cache::put("$key:loaded", true, 60);

    flock($lock_fp, LOCK_UN);
    fclose($lock_fp);
  }

  private function makeDatabaseHash($repo_id)
  {
    return intval($repo_id/100);
  }

  private function chopDatabase($database)
  {
    $chunks = array();
    $newDatabase = array();
    foreach($database as $repo_id=>$object) {
      $chunk = $this->makeDatabaseHash($repo_id);
      $chunks[$repo_id] = $chunk;
      $newDatabase[$chunk][$repo_id] = $object;
    }
    return $newDatabase;
  }

  protected function loadObject($repo_id)
  {
    if(isset($this->database[$repo_id]))
      return;

    $chunk = $this->makeDatabaseHash($repo_id);

    if(!isset($this->dataChunks[$chunk])) {
      $key = self::CACHE_KEY;
      $this->dataChunks[$chunk] = \Cache::get("$key:db:$chunk");
      \Log::debug("loading data chunk $chunk for data id: $repo_id");
    }
    $this->database[$repo_id] = $this->dataChunks[$chunk][$repo_id];
  }

  private function makeIndexHash($index)
  {
    $max = 10;
    // the item index is quite big by itself, use a chunk just for it.
    if ($index=="item") 
      return $max+1;

    // the recipe index is common enough to be in a chunk by itself.
    else if ($index=="recipe")
      return $max+2;

    return abs(intval(base_convert($index, 16, 10)/$max)%$max);
  }

  private function chopIndex($database)
  {
    $chunks = array();
    $newDatabase = array();
    foreach($database as $index=>$data) {
      $chunk = $this->makeIndexHash($index);
      $chunks[$index] = $chunk;
      $newDatabase[$chunk][$index] = $data;
    }
    return $newDatabase;
  }

  protected function loadIndex($index)
  {
    if(isset($this->index[$index])) 
      return;

    $chunk = $this->makeIndexHash($index);
    if(!isset($this->indexChunks[$chunk])) {
      $key = self::CACHE_KEY;
      $this->indexChunks[$chunk] = \Cache::get("$key:index:$chunk");
      \Log::debug("loading index chunk $chunk for index $index");
    }

    if(!isset($this->indexChunks[$chunk][$index]))
      return;

    $this->index[$index] = $this->indexChunks[$chunk][$index];
    
  }
}
