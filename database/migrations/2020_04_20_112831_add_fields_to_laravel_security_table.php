<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToLaravelSecurityTable extends Migration
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
        Schema::table(config('laravel-security.database.password_history_table', 'password_history'), function (Blueprint $table) {
            $table->string('user_class')->after('user_id')->nullable();
        });
        Schema::table(config('laravel-security.database.user_security_table', 'user_extend_security'), function (Blueprint $table) {
            $table->string('user_class')->after('user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('laravel-security.database.user_security_table', 'user_extend_security'), function (Blueprint $table) {
            $table->dropColumn('user_class');
        });
        Schema::table(config('laravel-security.database.password_history_table', 'password_history'), function (Blueprint $table) {
            $table->dropColumn('user_class');
        });
    }
}
