# Metaphor
A Laravel package that allows you to manage metadata.
This package is maintained under Laravel 5.8.

# Installation
Run the following command.

    composer require sukohi/metaphor:3.*

# Preparation

## 1. Trait

Set `MetaphorTrait` in your model as follows.

    <?php
    
    namespace App;
    
    use Illuminate\Database\Eloquent\Model;
    use Sukohi\Metaphor\MetaphorTrait;
    
    class Item extends Model
    {
        use MetaphorTrait;
    }
    
## 2. Migration

Just run the migration command.  

**Note:** You do NOT need to make any migrations by yourself because this package already has it.

    php artisan migrate
    
That's it!

# Usage

## Save

    $item = \App\Item::find(1);
    $item->meta->key_1 = 300;
    $item->meta->key_2 = 'yyy';
    $item->meta->key_3 = ['item_1x', 'item_2', 'item_3'];
    $item->meta->key_4 = null;
    $item->meta->save();
    
**Note:** `$item->meta` is an extended Collection of Laravel.  
So you can use all of the methods as usual.
    
## Delete

    $item->meta->delete($key);
    
    // or
    
    $item->meta->deleteAll();
    
## Check if a meta value exists

    if($item->meta->has($key)) {

        // has it!

    }

# About appending

If you'd like metadata to include in model data, set `meta` to `$appends`.

    <?php
    
    namespace App;
    
    use Illuminate\Database\Eloquent\Model;
    use Sukohi\Metaphor\MetaphorTrait;
    
    class Item extends Model
    {
        use MetaphorTrait;
        protected $appends = ['meta'];  // <- here
    }

# Where clause

## 1. whereMeta

    \App\Item::whereMeta('price', '500')->get();
    \App\Item::whereMeta('price', 'LIKE', '%50%')->get();
    \App\Item::orWhereMeta('price', '500')->get();
    \App\Item::orWhereMeta('price', 'LIKE', '%50%')->get();

## 2. whereMetaIn

    \App\Item::whereMetaIn('price', [300, 500])->get();
    \App\Item::orWhereMetaIn('price', [300, 500])->get();

## 3. whereMetaNotIn

    \App\Item::whereMetaNotIn('price', [300, 500])->get();
    \App\Item::orWhereMetaNotIn('price', [300, 500])->get();

## 4. whereMetaNull

    \App\Item::whereMetaNull('price')->get();
    \App\Item::orWhereMetaNull('price')->get();

## 5. whereMetaNotNull

    \App\Item::whereMetaNotNull('price')->get();
    \App\Item::orWhereMetaNotNull('price')->get();

# OrderByMeta

    \App\Item::orderByMeta($key', 'asc')->get();
    \App\Item::orderByMeta($key', 'desc')->get();

**Note:** This method uses `FIELD(value, val1, val2, val3, ...)` function in SQL.  
It means if your DB system does not have the function, this feature is **not available**. MySQL has it, though.

# License
This package is licensed under the MIT License.  
Copyright 2019 Sukohi Kuhoh