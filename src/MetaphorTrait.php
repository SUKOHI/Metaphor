<?php namespace Sukohi\Metaphor;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait MetaphorTrait {

    private $_meta_table = null;
    private $_parent_id = 'parent_id';
    private $_meta_attributes = [];
    private $_retrieved_flag = false;

    // Magic Methods

    public function __get($key) {

        if($this->hasMetaKey($key)) {

            return $this->getMeta($key);

        }

        return parent::__get($key);

    }

    public function __set($key, $value) {

        if($this->hasMetaKey($key)) {

            $this->setMeta($key, $value);
            return;

        }

        parent::__set($key, $value);

    }

    // Public Methods

    public function getMeta($key = '') {

        $this->retrieveMeta();

        $values = [];

        foreach ($this->metaKeys as $meta_column) {

            if(isset($this->_meta_attributes[$meta_column])) {

                $values[$meta_column] = $this->_meta_attributes[$meta_column];

            }

        }

        if($key == '') {

            return $values;

        } else if(isset($values[$key])) {

            return $this->_meta_attributes[$key];

        }

        return null;

    }

    public function setMeta($key, $value = '') {

        $this->retrieveMeta();

        if(is_array($key) && $value == '') {

            $meta_values = $key;

            foreach ($meta_values as $key => $value) {

                if($this->hasMetaKey($key)) {

                    $this->_meta_attributes[$key] = $value;

                }

            }

        } else {

            if($this->hasMetaKey($key)) {

                $this->_meta_attributes[$key] = $value;

            }

        }

    }

    public function unsetMeta($key) {

        if($this->hasMetaKey($key) && isset($this->_meta_attributes[$key])) {

            unset($this->_meta_attributes[$key]);

        }

    }

    public function metaTableCreate($table) {

        $table->increments('id');
        $table->integer($this->_parent_id)
            ->unsigned()
            ->index();
        $table->foreign($this->_parent_id)
            ->references('id')
            ->on($this->getTable())
            ->onDelete('cascade');
        $table->string('type')->default('null');
        $table->string('meta_key')->index();
        $table->text('meta_value')->nullable();
        $table->timestamps();
        $table->unique([
            $this->_parent_id,
            'meta_key'
        ]);

    }

    // Override Methods

    public function save(array $options = []) {

        $meta_table = $this->getMetaTable();
        \DB::beginTransaction();

        if(parent::save($options)) {

            if(!empty($this->_meta_attributes)) {

                $dt = Carbon::now();
                $meta_data = DB::table($meta_table)
                                    ->where($this->_parent_id, $this->id)
                                    ->get();
                $meta_attributes = $this->_meta_attributes;

                foreach ($meta_data as $meta) {

                    $key = $meta->meta_key;

                    if(!isset($meta_attributes[$key])) {

                        DB::table($meta_table)
                            ->where($this->_parent_id, $this->id)
                            ->where('meta_key', $key)
                            ->delete();

                    } else {

                        $value = $meta_attributes[$key];
                        $type = $this->getMetaType($value);

                        DB::table($meta_table)
                            ->where($this->_parent_id, $this->id)
                            ->where('meta_key', $key)
                            ->update([
                                'type' => $type,
                                'meta_value' => $this->encodeValue($type, $value),
                                'updated_at' => $dt
                            ]);

                    }

                    unset($meta_attributes[$key]);

                }

                if(count($meta_attributes) > 0) {

                    foreach ($meta_attributes as $key => $value) {

                        $type = $this->getMetaType($value);

                        DB::table($meta_table)->insert([
                            $this->_parent_id => $this->id,
                            'type' => $type,
                            'meta_key' => $key,
                            'meta_value' => $this->encodeValue($type, $value),
                            'created_at' => $dt,
                            'updated_at' => $dt
                        ]);

                    }


                }

            }

        }

        DB::commit();

    }

    // Private Methods

    private function retrieveMeta() {

        if(!$this->_retrieved_flag) {

            $meta_table = $this->getMetaTable();
            $meta_data = DB::table($meta_table)
                            ->where($this->_parent_id, $this->id)
                            ->get();

            foreach ($meta_data as $meta) {

                $type = $meta->type;
                $meta_key = $meta->meta_key;
                $meta_value = $this->decodeValue($type, $meta->meta_value);
                $this->_meta_attributes[$meta_key] = $meta_value;

            }

            $this->_retrieved_flag = true;

        }

    }

    private function getMetaTable() {

        if($this->_meta_table == null) {

            $class_parts = explode('\\', __CLASS__);
            $class = array_pop($class_parts);
            $this->_meta_table = strtolower(str_plural($class)) .'_meta';

        }

        return $this->_meta_table;

    }

    private function getMetaType($value) {

        $type = gettype($value);

        if($type == 'double') {

            return 'float';

        } else if($type == 'array') {

            return 'json';

        } else if($type == 'object' && get_class($value) == 'Carbon\Carbon') {

            return 'datetime';

        } else if($type == 'NULL') {

            return 'null';

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

    private function hasMetaKey($key) {

        return in_array($key, $this->metaKeys);

    }

}