<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLaravelSecurityTables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return config('database.default');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!config('laravel-security')) {
            throw new \Exception('Cannot read config [laravel-security]. Have you done vendor:publish?');
        }
        Schema::create(config('laravel-security.database.password_history_table', 'password_history'), function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->index();
            $table->string('password', 60);
            $table->timestamps();
        });
        Schema::create(config('laravel-security.database.user_security_table', 'user_extend_security'), function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->index();
            $table->date('last_loggein_at')->nullable();
            $table->date('last_password_updated_at')->nullable();
            $table->integer('status')->default(10);
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
        Schema::dropIfExists(config('laravel-security.database.password_history_table'));
        Schema::dropIfExists(config('laravel-security.database.user_security_table'));
    }
}
