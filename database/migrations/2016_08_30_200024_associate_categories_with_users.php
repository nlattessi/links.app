<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AssociateCategoriesWithUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $dbConnection = env('DB_CONNECTION', 'mysql');

        Schema::table('categories', function (Blueprint $table) use ($dbConnection) {
            if ($dbConnection === 'sqlite') {
                $table->integer('user_id')->unsigned()->default(0);
            } else {
                $table->integer('user_id')->after('id')->unsigned();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
}
