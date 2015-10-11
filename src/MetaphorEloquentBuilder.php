<?php namespace Sukohi\Metaphor;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MetaphorEloquentBuilder extends Builder
{
	private $_meta_table = '';
	private $_where_meta_methods = [
		'whereMeta',
		'orWhereMeta',
		'whereBetweenMeta',
		'whereNotBetweenMeta',
		'whereInMeta',
		'whereNotInMeta',
		'whereNullMeta',
		'whereNotNullMeta'
	];

	public function __call($method, $parameters) {

		if(in_array($method, $this->_where_meta_methods)) {

			$db = DB::table($this->_meta_table)->where('meta_key', $parameters[0]);

			if(in_array($method, ['whereMeta', 'orWhereMeta'])) {

				$db->where('meta_value', $parameters[1], $parameters[2]);

			} else if($method == 'whereBetweenMeta') {

				$db->whereBetween('meta_value', $parameters[1]);

			} else if($method == 'whereNotBetweenMeta') {

				$db->whereNotBetween('meta_value', $parameters[1]);

			} else if($method == 'whereInMeta') {

				$db->whereIn('meta_value', $parameters[1]);

			} else if($method == 'whereNotInMeta') {

				$db->whereNotIn('meta_value', $parameters[1]);

			} else if($method == 'whereNullMeta') {

				$db->where('type', 'null');

			} else if($method == 'whereNotNullMeta') {

				$db->where('type', '<>', 'null');

			}

			$parent_ids = $db->lists('parent_id');

			if($method == 'orWhereMeta') {

				return $this->orWhere(function($query) use($parent_ids){

					return $query->whereIn('id', $parent_ids);

				});

			}

			return $this->whereIn('id', $parent_ids);

		}

		return parent::__call($method, $parameters);

	}

	public function setMetaTable($table) {

		$this->_meta_table = $table;

	}

}