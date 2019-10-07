<?php
namespace Repositories;

use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class Repository implements RepositoryInterface 
{
    protected $app;
    public function getModelOrFail($model, $id)
    {
        $indexer = $this->app->make("Repositories\\Indexers\\$model");

        $data = $this->get($indexer::DEFAULT_INDEX.".".$id);
        if (!$data) {
            throw new ModelNotFoundException();
        }

        $model = $this->app->make($model);
        $model->load($data);

        return $model;
    }
    
    public function getMultiModelOrFail($model, $id)
    {
        $indexer = $this->app->make("Repositories\\Indexers\\$model");

        $model1 = $this->app->make($model);
        $data = $this->raw(strtolower($model)."_multi.".$id,null);

        if (!$data) {
            throw new ModelNotFoundException();
        }

        array_walk($data, 
            function (&$value) use ($model) {
                $data2 = $this->getrepo($value,null);

                if(!$data2){
                    throw new ModelNotFoundException;
                }else{
                    $model2 = $this->app->make($model);
                    $model2->load($data2);
                    $value = $model2;
                }
            }
        );
        
        return $data;
    }

    public function getModel($model, $id)
    {
        $indexer = $this->app->make("Repositories\\Indexers\\$model");

        $data = $this->get($indexer::DEFAULT_INDEX.".".$id);

        $model = $this->app->make($model);

        if (!$data) {
            if(method_exists($model, "loadDefault"))
                $model->loadDefault($id);
            else
                throw new ModelNotFoundException;
        } else {
            $model->load($data);
        }

        return $model;
    }

    public function getModelAuto($id)
    {
        $data = $this->get("all.$id");

//        \Log::info("auto", array($id, $data));
        $model = ucfirst($data->type);
        $model = $this->app->make($model);

        if (!$data) {
            if(method_exists($model, "loadDefault"))
                $model->loadDefault($id);
            else
                throw new ModelNotFoundException;
        } else {
            $model->load($data);
        }

        return $model;

    }

    public function allModels($model, $index = null)
    {
        if (!$index) {
            $class = "Repositories\\Indexers\\$model";
            $index = $class::DEFAULT_INDEX;
        }

        $data = array_unique($this->raw($index));

        array_walk($data, 
            function (&$value) use ($model) {
                $value = $this->getModel($model, $value);
            }
        );

        return $data;
    }

    public function searchModels($model, $search)
    {
        \Log::info("searching for $search...");

        $results = array();
        if (!$search) {
            return $results;
        }

        foreach ($this->allModels($model) as $obj) {
            if ($obj->matches($search)) {
                $results[] = $obj;
            }
        }

        return $results;
    }
}
