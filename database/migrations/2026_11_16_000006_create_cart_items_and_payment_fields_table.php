<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('duration_days')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('checkout_group', 40)->nullable()->after('order_code')->index();
            $table->unsignedInteger('quantity')->default(1)->after('product_id');
            $table->enum('payment_method', ['wa_langsung', 'transfer'])->default('wa_langsung')->after('total_price');
            $table->enum('payment_status', ['belum_bayar', 'menunggu_verifikasi', 'lunas', 'ditolak'])->default('belum_bayar')->after('payment_method');
            $table->string('bank_name', 80)->nullable()->after('payment_status');
            $table->string('payment_note', 150)->nullable()->after('bank_name');
            $table->string('payment_proof', 255)->nullable()->after('payment_note');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'checkout_group',
                'quantity',
                'payment_method',
                'payment_status',
                'bank_name',
                'payment_note',
                'payment_proof',
            ]);
        });

        Schema::dropIfExists('cart_items');
    }
};
