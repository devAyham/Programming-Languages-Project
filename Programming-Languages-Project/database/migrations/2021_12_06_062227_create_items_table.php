<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            //$table->integer("user_id")->unsigned();
            $table->integer('user_id')->unsigned()->index();
            $table->string('contact_information');
            $table->string('expiration_date');
            $table->integer('quantity');
            $table->integer('price');
            $table->integer('new_price')->nullable();
            $table->integer('views')->default(1);
            $table->timestamps();

            $table->foreign('user_id')
            ->references('id')->on('users')
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
        Schema::dropIfExists('items');
    }
}
