<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Offers extends Migration
{
    




    public function up()
    {
            Schema::create('offers', function (Blueprint $table) 
            {
            $table->increments('id');
            $table->integer('item_id')->unsigned()->index();
            $table->integer('First_offer');
            $table->integer('value_of_discount_first_offer');
            $table->integer('Second_offer');
            $table->integer('value_of_discount_Second_offer');
            $table->integer('Third_offer');
            $table->integer('value_of_discount_Third_offer');

            
            $table->timestamps();

            $table->foreign('item_id')
            ->references('id')->on('items')
            ->onDelete('cascade');
            
            });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
