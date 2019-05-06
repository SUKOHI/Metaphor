<?php

namespace Sukohi\Metaphor;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class MetaphorCollection extends Collection
{
    private $_parent;

    public function __construct($parent)
    {
        $this->_parent = $parent;
        $items = $parent->meta_relationship()->get();

        foreach($items as $item) {

            $type = $item->type;
            $value = $item->value;
            $this->put($item->key, $this->decodeValue($type, $value));

        }
    }

    // Setter & Getter

    public function __set($key, $value) {

        $this->put($key, $value);

    }

    public function __get($key) {

        return $this->get($key);

    }

    // Save & delete

    public function save() {

        foreach($this->all() as $key => $value) {

            $type = $this->getMetaType($value);
            $meta = $this->getMetaModel($key);

            if(is_null($meta)) {

                $meta = new MetaphorMeta();
                $meta->parent = $this->getModel();
                $meta->parent_id = $this->getModelId();
                $meta->type = $type;
                $meta->key = $key;
                $meta->value = $value;

            }

            $meta->value = $this->encodeValue($type, $value);
            $meta->save();

        }

    }

    public function delete($key) {

        $meta = $this->getMetaModel($key);

        if(is_null($meta)) {

            return true;

        }

        return $meta->delete();

    }

    public function deleteAll() {

        $model = $this->getModel();
        $model_id = $this->getModelId();
        return MetaphorMeta::where('parent', $model)
            ->where('parent_id', $model_id)
            ->delete();

    }

    // Others

    private function getMetaModel($key) {

        $model = $this->getModel();
        $model_id = $this->getModelId();
        return MetaphorMeta::where('parent', $model)
            ->where('parent_id', $model_id)
            ->where('key', $key)
            ->first();

    }

    private function getModel() {

        return get_class($this->_parent);

    }

    private function getModelId() {

        return $this->_parent->id;

    }

    private function getMetaType($value) {

        $type = gettype($value);

        if($type === 'double') {

            $type = 'float';

        } else if($type === 'array') {

            $type = 'json';

        } else if($type === 'object' && $value instanceof Carbon) {

            $type = 'datetime';

        } else if($type === 'NULL') {

            $type = 'null';

        }

        return $type;
    }

    private function encodeValue($type, $value) {

        if($type == 'json') {

            return json_encode($value);

        }

        return (string) $value;
    }

    private function decodeValue($type, $value) {

        if($type == 'datetime') {

            return new Carbon($value);

        } else if($type == 'json') {

            return json_decode($value, true);

        } else if($type == 'integer') {

            return intval($value);

        } else if($type == 'float') {

            return floatval($value);

        } else if($type == 'null') {

            return null;

        }

        return $value;
    }
}
