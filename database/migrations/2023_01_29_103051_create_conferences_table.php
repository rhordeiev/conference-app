<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conferences', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->date('date');
            $table->decimal('lat', 15, 10)->nullable();
            $table->decimal('lng', 15, 10)->nullable();
            $table->string('country_name', 60)->nullable();
            $table->foreign('country_name')->references('name')->on('countries')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });

//        Schema::table('conferences', function (Blueprint $table) {
//            $table->foreign('country_name')->references('name')->on('countries')
//                ->nullOnDelete()->cascadeOnUpdate();
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conferences');
    }
};
