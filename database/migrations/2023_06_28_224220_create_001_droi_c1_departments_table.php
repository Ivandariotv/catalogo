<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Create001DroiC1DepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('001_droi_c1_departments', function (Blueprint $table) {
        //     $table->bigIncrements('Department_Code')
        //         ->unsigned(false)
        //         ->nullable(false);

        //     $table->bigInteger('Country_Code', 15)
        //         ->nullable(false);

        //     $table->string('Name', 100)
        //         ->nullable()
        //         ->default(null)
        //         ->collation('utf8_unicode_ci')
        //         ->nullable(false);
                
        //     $table->string('CNum', 10)
        //         ->collation('utf8_unicode_ci')
        //         ->nullable();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('001_droi_c1_departments');
    }
}
