<?php

namespace Sukohi\Metaphor;

trait MetaphorTrait {

    private $_metaphor_items = null;

	// Relationship
    public function meta_relationship() {

        return $this->hasMany(MetaphorMeta::class, 'parent_id', 'id');

    }

    // Accessor
    public function getMetaAttribute() {

        if(is_null($this->_metaphor_items)) {

            $this->_metaphor_items = new MetaphorCollection($this);

        }

        return $this->_metaphor_items;

    }

    // Scope
    public function scopeWhereMeta($query, $key, $operator = null, $value = null) {

        if (func_num_args() === 3) {

            $value = $operator;
            $operator = '=';

        }

        $query->whereHas('meta_relationship', function($q) use($key, $operator, $value) {

            $q->where('key', $key)
                ->where('value', $operator, $value);

        });

    }

    public function scopeOrWhereMeta($query, $key, $operator = null, $value = null) {

        if (func_num_args() === 3) {

            $value = $operator;
            $operator = '=';

        }

        $query->orWhereHas('meta_relationship', function($q) use($key, $operator, $value) {

            $q->where('key', $key)
                ->where('value', $operator, $value);

        });

    }

    public function scopeWhereMetaIn($query, $key, $values = []) {

        $query->whereHas('meta_relationship', function($q) use($key, $values) {

            $q->where('key', $key)
                ->whereIn('value', $values);

        });

    }

    public function scopeOrWhereMetaIn($query, $key, $values = []) {

        $query->orWhereHas('meta_relationship', function($q) use($key, $values) {

            $q->where('key', $key)
                ->whereIn('value', $values);

        });

    }

    public function scopeWhereMetaNotIn($query, $key, $values = []) {

        $query->whereHas('meta_relationship', function($q) use($key, $values) {

            $q->where('key', $key)
                ->whereNotIn('value', $values);

        });

    }

    public function scopeOrWhereMetaNotIn($query, $key, $values = []) {

        $query->orWhereHas('meta_relationship', function($q) use($key, $values) {

            $q->where('key', $key)
                ->whereNotIn('value', $values);

        });

    }

    public function scopeWhereMetaNull($query, $key) {

        $query->whereHas('meta_relationship', function($q) use($key) {

            $q->where('key', $key)
                ->where('type', 'null');

        });

    }

    public function scopeOrWhereMetaNull($query, $key) {

        $query->orWhereHas('meta_relationship', function($q) use($key) {

            $q->where('key', $key)
                ->where('type', 'null');

        });

    }

    public function scopeWhereMetaNotNull($query, $key) {

        $query->whereHas('meta_relationship', function($q) use($key) {

            $q->where('key', $key)
                ->where('type', '!=', 'null');

        });

    }

    public function scopeOrWhereMetaNotNull($query, $key) {

        $query->orWhereHas('meta_relationship', function($q) use($key) {

            $q->where('key', $key)
                ->where('type', '!=', 'null');

        });

    }

}