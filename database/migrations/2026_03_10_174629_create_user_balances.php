<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->decimal('balance', 24, 8)->default(0);
            $table->timestamps();
        });

        Schema::create('balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['deposit','withdraw','fee']);
            $table->decimal('amount', 24, 8);
            $table->enum('status', ['pending','confirmed','failed'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('balance_transactions');
        Schema::dropIfExists('user_balances');
    }
};