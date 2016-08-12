<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AssociateLinksWithCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $dbConnection = env('DB_CONNECTION', 'mysql');
        if ($dbConnection === 'sqlite') {
            Schema::table('links', function (Blueprint $table) {
                // Create the category_id column as an unsigned integer
                $table->integer('category_id')->unsigned()->default(0);
            });
        } else {
            Schema::table('links', function (Blueprint $table) {
                // Create the category_id column as an unsigned integer
                $table->integer('category_id')->after('id')->unsigned();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('links', function (Blueprint $table) {
            // Lastly, now it's safe to drop the column
            $table->dropColumn('category_id');
        });
    }
}
