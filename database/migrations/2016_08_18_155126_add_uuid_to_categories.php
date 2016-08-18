<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUuidToCategories extends Migration
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
                $table->uuid('uuid')->default('00000000-0000–0000–0000-000000000000');
            } else {
                $table->uuid('uuid');
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
            $table->dropColumn('uuid');
        });
    }
}
