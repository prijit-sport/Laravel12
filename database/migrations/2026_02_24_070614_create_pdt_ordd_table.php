<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('pdt_ordd', function (Blueprint $table) {
        $table->id();
        $table->string('DOCNO');               // FK เชื่อมกับ PDT_ORDH
        $table->string('SN')->nullable();      // Serial Number
        $table->timestamps();

        $table->foreign('DOCNO')
              ->references('DOCNO')
              ->on('PDT_ORDH')
              ->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::dropIfExists('PDT_ORDD');
}
};
