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

Set `MetaphorTrait` in your model like this.

    use Sukohi\Metaphor\MetaphorTrait;
    
    class Item extends Model
    {
        use MetaphorTrait;
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

You can get values as you retrieve original value like this.

    echo $item->price;
    echo $item->size;
    echo $item->weight;
    echo print_r($item->years);
    echo $item->memo;
    echo $item->purchased_at;
    
`$item->getMeta('META_KEY');` is also available.
    
or 

    print_r($item->getMeta());  // All data

###Save value

You can save values as you set original value like this.

Insert
    
    $item = new \App\Item;
    $item->price = 500;
    $item->size = 3.5;
    $item->weight = '50kg';
    $item->years = [2013, 2014, 2015];
    $item->memo = null;
    $item->purchased_at = new Carbon();
    $item->save();
    
Update
    
    $item = \App\Item::find(1);
    $item->price = 500;
    $item->size = 3.5;
    $item->weight = '50kg';
    $item->years = [2013, 2014, 2015];
    $item->memo = null;
    $item->purchased_at = new Carbon();
    $item->save();
    
`$item->setMeta('META_KEY', 'META_VALUE');` is also available.
    
or You also can save meta values like this.

    $item->setMeta([
        'price' => 600,
        'size' => 4.5,
        'weight' => '65kg',
        'years' => [2013, 2014, 2015],
        'memo' => null,
        'purchased_at' => new Carbon()
    ]);

###Remove value

    $item->unset('meta_key');
    $item->save();

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