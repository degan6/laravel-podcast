<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDownloadErrorPodcastItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('podcast_items', function (Blueprint $table) {
            $table->text('download_error')->after('podcast_id')->default(null);
            $table->text('download_error_desc')->after('download_error')->default(null);
            $table->dateTime('download_date')->after('download_error_desc')->nullable();
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
            $table->drop(['download_error', 'download_date', 'download_error_desc']);
        });
    }
}
