<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('001_droi_p2_t1_clients', function (Blueprint $table) {
        //     //
        // });}

        Schema::table('001_droi_p2_t1_clients', function (Blueprint $table) {
            $table->string('password',255)
                ->charset('utf8mb4')
                ->collation('utf8mb4_unicode_ci')
                ->after('Phone');

            $table->string('photograph',200)
                ->charset('utf8')
                ->collation('utf8_unicode_ci')
                ->nullable()
                ->after('password');

            $table->tinyInteger('useApp')
                ->nullable()
                ->default(0)
                ->after('photograph');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('001_droi_p2_t1_clients', function (Blueprint $table) {
            $table->dropColumn('password');
            $table->dropColumn('photograph');
            $table->dropColumn('useApp');
        });
    }
}
