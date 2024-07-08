<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Create001DroiC0CountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('001_droi_c0_countries', function (Blueprint $table) {
        //     $table->bigIncrements('Country_Code')
        //         ->unsigned(false)
        //         ->nullable(false);

        //     $table->string('Name', 100)
        //         ->nullable(false)
        //         ->collation('utf8_unicode_ci');

        //     $table->string('CA2', 11)
        //         ->nullable(false)
        //         ->collation('utf8_unicode_ci')
        //         ->comment('Código alfa-2');

        //     $table->string('CA3', 11)
        //         ->nullable(false)
        //         ->collation('utf8_unicode_ci')
        //         ->comment('Código alfa-3');
                
        //     $table->integer('CNum')
        //         ->nullable(false)
        //         ->comment('Código numérico');
                
        //     $table->enum('State', ['Active', 'Inactive'])
        //         ->nullable(false)
        //         ->default('Active')
        //         ->collation('utf8_unicode_ci');

        //     // Configuración de motor de almacenamiento y conjunto de caracteres
        //     $table->engine = 'InnoDB';
        //     $table->charset = 'utf8';
        //     $table->collation = 'utf8_unicode_ci';
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('001_droi_c0_countries');
    }
}
