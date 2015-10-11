<?php namespace Sukohi\Metaphor;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mockery\CountValidator\Exception;
use Illuminate\Database\Eloquent\Model;

class MetaphorModel extends Model
{
	private $_meta_table = null;
	private $_parent_id = 'parent_id';
	private $_meta_attributes = [];
	private $_table_columns = [];
	private $_retrieved_flag = false;

	// Magic Methods

	public function __get($key) {

		$columns = $this->getTableColumns();

		if(array_key_exists($key, $this->attributes)
			|| $this->hasGetMutator($key)
			|| $this->relationLoaded($key)
			|| method_exists($this, $key)
			|| in_array($key, $columns)) {

			return parent::__get($key);

		}

		return $this->getMeta($key);

	}

	public function __set($key, $value) {

		$columns = $this->getTableColumns();

		if($this->hasSetMutator($key)
			|| (in_array($key, $this->getDates()) && $value)
			|| ($this->isJsonCastable($key) && ! is_null($value))
			|| in_array($key, $columns)) {

			return parent::__set($key, $value);

		}

		$this->setMeta($key, $value);

	}

	// Public Methods

	public function getMeta($key = '') {

		$this->retrieveMeta();

		if($key == '') {

			return $this->_meta_attributes;

		} else if(isset($this->_meta_attributes[$key])) {

			return $this->_meta_attributes[$key];

		}

		return null;

	}

	public function setMeta($key, $value = '') {

		$this->retrieveMeta();

		if(is_array($key)) {

			$values = $key;

			foreach ($values as $key => $value) {

				$this->_meta_attributes[$key] = $value;

			}

		} else {

			$this->_meta_attributes[$key] = $value;

		}

	}

	public function unsetMeta($key) {

		if(isset($this->_meta_attributes[$key])) {

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

	public function getMetaTable() {

		if($this->_meta_table == null) {

			$class_parts = explode('\\', get_class($this));
			$class = array_pop($class_parts);
			$this->_meta_table = strtolower(str_plural($class)) .'_meta';

		}

		return $this->_meta_table;

	}

	// Override Methods

	public function save(array $options = []) {

		\DB::beginTransaction();

		try {

			$saved = parent::save($options);
			$id = $this->getParentId();

			if($id && !empty($this->_meta_attributes)) {

				$dt = Carbon::now();
				$meta_table = $this->getMetaTable();
				$meta_data = DB::table($meta_table)
					->where($this->_parent_id, $id)
					->get();
				$meta_attributes = $this->_meta_attributes;

				foreach ($meta_data as $meta) {

					$key = $meta->meta_key;

					if(!array_key_exists($key, $meta_attributes)) {

						DB::table($meta_table)
							->where($this->_parent_id, $id)
							->where('meta_key', $key)
							->delete();

					} else {

						$value = $meta_attributes[$key];
						$type = $this->getMetaType($value);

						DB::table($meta_table)
							->where($this->_parent_id, $id)
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
							$this->_parent_id => $id,
							'type' => $type,
							'meta_key' => $key,
							'meta_value' => $this->encodeValue($type, $value),
							'created_at' => $dt,
							'updated_at' => $dt
						]);

					}


				}

			}

			DB::commit();
			return $saved;

		} catch(Exception $e) {

			DB::rollback();

		}

		return false;

	}

	public function newEloquentBuilder($query) {

		$builder = new MetaphorEloquentBuilder($query);
		$builder->setMetaTable($this->getMetaTable());
		return $builder;

	}

	// Private Methods

	private function retrieveMeta() {

		$id = $this->getParentId();

		if($id && !$this->_retrieved_flag) {

			$meta_table = $this->getMetaTable();
			$meta_data = DB::table($meta_table)
				->where($this->_parent_id, $id)
				->orderBy('meta_key')
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

	private function getParentId() {

		return $this->getAttribute('id');

	}

	private function getTableColumns() {

		if(empty($this->_table_columns)) {

			$table = $this->getTable();
			$this->_table_columns = $this->getConnection()
				->getSchemaBuilder()
				->getColumnListing($table);

		}

		return $this->_table_columns;

	}
}