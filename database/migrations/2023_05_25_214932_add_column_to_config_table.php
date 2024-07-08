<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('001_droi_p0_t1_config_business', function (Blueprint $table) {
            $table->tinyInteger('useOnlineStore')
                ->default(0)
                ->after('Language');

            $table->tinyInteger('show_image_subgroups')
                ->default(0)
                ->after('show_image_groups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('001_droi_p0_t1_config_business', function (Blueprint $table) {
            $table->dropColumn('useOnlineStore');
            $table->dropColumn('show_image_subgroups');
        });
    }
}
