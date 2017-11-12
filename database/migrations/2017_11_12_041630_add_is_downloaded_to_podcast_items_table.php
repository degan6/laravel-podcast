<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsDownloadedToPodcastItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('podcast_items', function (Blueprint $table) {
            $table->boolean('downloaded')->after('podcast_id')->default('0');
            $table->text('file_name')->after('downloaded')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('podcast_items', function (Blueprint $table) {
            $table->drop(['downloaded', 'file_name']);
        });
    }
}
