<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBuildStats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('build_stats', function (Blueprint $table) {
		    $table->renameColumn('classID', 'heroID');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::table('build_stats', function (Blueprint $table) {
		    $table->renameColumn('heroID', 'classID');
	    });
    }
}