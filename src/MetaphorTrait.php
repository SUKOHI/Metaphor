<?php namespace Sukohi\Metaphor;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait MetaphorTrait {

    public $metaModel = null;
    private $_meta_checked = false;
    private $_meta_data = [];
    private $_table_columns = [];

    // Construct

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    // Eager Loading

    public function meta() {

        $meta_model = $this->getMetaModel();
        return $this->hasMany($meta_model, 'parent_id', 'id');

    }

    // Magic Methods

    public function __get($key) {

        if(array_key_exists($key, $this->attributes)
            || $this->hasGetMutator($key)
            || method_exists($this, $key)) {

            return parent::__get($key);

        }

        return $this->getMeta($key);

    }

    public function __set($key, $value) {

        if ($this->hasSetMutator($key)) {

            parent::__set($key, $value);

        } else {

            $this->setMeta($key, $value);

        }

    }

    // Public Methods

    public function hasMeta($key) {

        $this->checkMeta();
        return isset($this->_meta_data[$key]);

    }

    public function getMeta($key = '') {

        $this->checkMeta();

        if($key == '') {

            $values = [];

            foreach ($this->_meta_data as $meta_key => $meta_values) {

                $values[$meta_key] = $meta_values['value'];

            }

            return $values;

        }

        if(isset($this->_meta_data[$key])) {

            return $this->_meta_data[$key]['value'];

        }

        return null;

    }

    public function setMeta($key, $value) {

        $this->checkMeta();

        $id = ($this->hasMeta($key)) ? $this->_meta_data[$key]['id'] : -1;
        $this->_meta_data[$key] = [
            'id' => $id,
            'value' => $value
        ];

    }

    public function metaTableCreate($table) {

        $table->increments('id');
        $table->integer('parent_id')
            ->unsigned()
            ->index();
        $table->foreign('parent_id')
            ->references('id')
            ->on($this->getTable())
            ->onDelete('cascade');
        $table->string('type')->default('null');
        $table->string('meta_key')->index();
        $table->text('meta_value')->nullable();
        $table->timestamps();
        $table->unique([
            'parent_id',
            'meta_key'
        ]);

    }

    // Override Methods

    public function save(array $options = []) {

        $meta_model = $this->getMetaModel();
        \DB::beginTransaction();

        if(parent::save($options)) {

            if(!empty($this->_meta_data)) {

                foreach ($this->_meta_data as $meta_key => $meta_values) {

                    $meta_value = $meta_values['value'];
                    $type = $this->getMetaType($meta_value);
                    $meta_record = $meta_model::firstOrNew([
                        'id' => $meta_values['id']
                    ]);
                    $meta_record->parent_id = $this->id;
                    $meta_record->type = $type;
                    $meta_record->meta_key = $meta_key;
                    $meta_record->meta_value = $this->encodeValue($type, $meta_value);
                    $meta_record->save();

                }

            }

        }

        DB::commit();

    }

    // Private Methods

    private function checkMeta() {

        if(!$this->_meta_checked) {

            if(!$this->meta) {

                parent::__get('meta');

            }

            if($this->meta->count() > 0) {

                foreach ($this->meta as $meta) {

                    $type = $meta->type;
                    $meta_key = $meta->meta_key;
                    $this->_meta_data[$meta_key] = [
                        'id' => $meta->id,
                        'value' => $this->decodeValue($type, $meta->meta_value)
                    ];

                }

            }

            $this->_meta_checked = true;

        }

    }

    private function getMetaModel() {

        if($this->metaModel == null) {

            $this->metaModel = __CLASS__ .'Meta';

        }

        return $this->metaModel;

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

}