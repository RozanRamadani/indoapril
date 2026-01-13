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
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->integer('idbarang')->unsigned();
            $table->integer('threshold')->default(10);
            $table->integer('current_stock')->default(0);
            $table->enum('status', ['active', 'dismissed', 'resolved'])->default('active');
            $table->timestamp('triggered_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->integer('dismissed_by')->unsigned()->nullable();
            $table->timestamps();

            $table->index(['status', 'triggered_at']);
            $table->index('idbarang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_alerts');
    }
};
