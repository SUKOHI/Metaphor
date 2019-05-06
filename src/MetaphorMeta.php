<?php

namespace Sukohi\Metaphor;

use Illuminate\Database\Eloquent\Model;

class MetaphorMeta extends Model
{
    protected $table = 'metadata';
    protected $guarded = ['id'];
}
