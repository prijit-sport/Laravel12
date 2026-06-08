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
    Schema::create('pdt_ordh', function (Blueprint $table) {
        $table->string('DOCNO')->primary();   // เลขที่เอกสาร
        $table->string('PDTCD')->nullable();   // รหัสสินค้า
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('PDT_ORDH');
}
};
