Metaphor
=====

A Laravel package to manage meta values of a specific DB table.  
(This is for Laravel 5+.)

Installation
====

Execute composer command.

    composer require sukohi/metaphor:2.*

Preparation
====

In this case, you're generating a meta table for a table called `items`. 

###1. Model

Set `MetaphorModel` in your model like this.

    use Sukohi\Metaphor\MetaphorModel;
    
    class Item extends MetaphorModel
    {
        //
    }

###2. Meta Table

Execute the following command to generate a migration file.

    php artisan make:migration create_items_meta_table

   
Set DB table schema there

    /**
    * Run the migrations.
    *
    * @return void
    */
    public function up()
    {
        \Schema::create('items_meta', function (Blueprint $table) {

            with(new \App\Item)->metaTableCreate($table);

        });
    }
    
    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::drop('items_meta');
    }
    

Execute migration.

    php artisan migrate


Usage
====

###Retrieve value

You can get meta values as you retrieve original value like this.

    $item = \App\Item::find(1);
    echo $item->price;
    
or
    
    $item->getMeta('META_KEY');
    
When retrieving all meta values as array

    $item->getMeta();

###Save value

You can save values as you set original value like this.

Insert
    
    $item = new \App\Item;
    $item->price = 500;
    $item->save();
    
Update
    
    $item = \App\Item::find(1);
    $item->price = 500;
    $item->save();

or 

    $item->setMeta('META_KEY', 'META_VALUE');
    $item->save();
    
When saving meta values using array.

    $item->setMeta([
        'price' => 600,
        'years' => [2013, 2014, 2015],
        'purchased_at' => new Carbon()
    ]);

###Remove value

    $item->unset('meta_key');
    $item->save();

###with Where clause

You can use the following `where` methods for meta table when retrieving data.  

* whereMeta
* orWhereMeta
* whereBetweenMeta
* whereNotBetweenMeta
* whereInMeta
* whereNotInMeta
* whereNullMeta
* whereNotNullMeta

Usage is the same with original where methods like this.

    $items = \App\Item::where('id', '>', 0)
                ->whereMeta('size', '1')
                ->whereBetweenMeta('size', [1, 2])
                ->whereNotBetweenMeta('size', [3, 5])
                ->whereInMeta('size', [1, 5])
                ->whereNotInMeta('size', [2, 3, 4])
                ->whereNullMeta('memo')
                ->whereNotNullMeta('memo')
                ->orWhereMeta('size', 'LIKE', '%5%')
                ->get();

*The first argument means meta key you want to get.

About original value
====

Of course you also can set original value at the same time like the next.

    $item = new \App\Item;
    $item->title = 500;
    $item->size = 3.5;
    $item->save();

About value type
====

Meta value's type will be automatically casted.  
So you don't need to take care about it.

License
====

This package is licensed under the MIT License.

Copyright 2015 Sukohi Kuhoh