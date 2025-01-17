<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('userpanel_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usertype_id');
            $table->foreign('usertype_id')
                    ->references('id')
                    ->on('userpanel_usertypes')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            $table->string('password', 60);
            $table->string('remember_token', 100)->nullable();
            $table->boolean('has_custom_permissions');
            $table->tinyInteger('status');
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
        Schema::dropIfExists('userpanel_users');
    }
}
