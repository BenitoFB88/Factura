<?php

// database/migrations/2025_05_08_000001_create_configs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigsTable extends Migration
{
    public function up()
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->string('key');
            $table->string('value', 500);
        });
    }

    public function down()
    {
        Schema::dropIfExists('configs');
    }
}
