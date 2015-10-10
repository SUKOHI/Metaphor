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

and save new model for a meta table.

    use Illuminate\Database\Eloquent\Model;
    
    class ItemMeta extends Model
    {
        public $table = 'items_meta';
        public $guarded = ['id', 'created_at', 'updated_at'];
    }

###2. Meta Table

Execute the following command to generate a migration file.

    php artisan make:migration crate_items_meta_table

   
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
            
        // You also can manually write your own custom schema here instead.
        
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

    $item = \App\Item::with('meta')->find(1);
    
    echo $item->getMeta('meta_key_1');
    echo $item->getMeta('meta_key_2');
    echo $item->getMeta('meta_key_3');
    print_r($item->getMeta('meta_key_4'));
    print_r($item->getMeta('meta_key_5'));
    echo $item->getMeta('meta_key_6');
    
    print_r($item->getMeta());  // Get All Values
    
    // or
    
    echo $item->meta_key_1;
    echo $item->meta_key_2;
    echo $item->meta_key_3;
    echo $item->meta_key_4;
    echo $item->meta_key_5;
    echo $item->meta_key_6;
    
    print_r($item->meta);

###Save value
    
    $item = \App\Item::find(1);
    
    $item->setMeta('meta_key_1', 'String');
    $item->setMeta('meta_key_2', 1);
    $item->setMeta('meta_key_3', 1.5);
    $item->setMeta('meta_key_4', ['value - 1', 'value -2']);
    $item->setMeta('meta_key_5', ['key' => 'key', 'value' => 'value']);
    $item->setMeta('meta_key_6', null);
    $item->save();
    
    // or
    
    $item->meta_key_1 = 'String';
    $item->meta_key_2 = 1;
    $item->meta_key_3 = 1.5;
    $item->meta_key_4 = ['value - 1', 'value -2'];
    $item->meta_key_5 = ['key' => 'key', 'value' => 'value']);
    $item->meta_key_6 = null;
    $item->save();

About value type
====

Meta value's type will be automatically convert.  
So you don't need to take care about it.

License
====

This package is licensed under the MIT License.

Copyright 2015 Sukohi Kuhoh
