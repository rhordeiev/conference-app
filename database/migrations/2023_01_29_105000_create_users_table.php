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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname', 60)->nullable();
            $table->string('lastname', 60)->nullable();
            $table->date('birthdate')->nullable();
            $table->string('country_name', 60)->nullable();
            $table->foreign('country_name')->references('name')->on('countries')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->string('type', 60)->nullable();
            $table->foreign('type')->references('name')->on('roles')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->string('phone', 20)->nullable();
            $table->string('email', 60)->unique();
            $table->string('password', 60);
            $table->rememberToken();
            $table->timestamps();
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
};
