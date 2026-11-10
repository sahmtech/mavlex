<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE transactions MODIFY tax_amount DECIMAL(22,10) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE transactions MODIFY tax2_amount DECIMAL(22,10) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE transaction_sell_lines MODIFY item_tax DECIMAL(22,10) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE transaction_sell_lines MODIFY unit_price_inc_tax DECIMAL(22,10) NULL');
        DB::statement('ALTER TABLE transaction_sell_lines MODIFY tobacco_tax DECIMAL(22,10) NULL DEFAULT 0');

        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'tax_1_id')) {
                $table->unsignedInteger('tax_1_id')->nullable()->after('tax2_amount');
                $table->foreign('tax_1_id')->references('id')->on('tax_rates')->nullOnDelete();
            }
            if (! Schema::hasColumn('transactions', 'tax_1')) {
                $table->decimal('tax_1', 22, 10)->default(0)->after('tax_1_id');
            }
            if (! Schema::hasColumn('transactions', 'tax_2_id')) {
                $table->unsignedInteger('tax_2_id')->nullable()->after('tax_1');
                $table->foreign('tax_2_id')->references('id')->on('tax_rates')->nullOnDelete();
            }
            if (! Schema::hasColumn('transactions', 'tax_2')) {
                $table->decimal('tax_2', 22, 10)->default(0)->after('tax_2_id');
            }
            if (! Schema::hasColumn('transactions', 'tax_total')) {
                $table->decimal('tax_total', 22, 10)->default(0)->after('tax_2');
            }
        });

        Schema::table('transaction_sell_lines', function (Blueprint $table) {
            if (! Schema::hasColumn('transaction_sell_lines', 'tax_1_id')) {
                $table->unsignedInteger('tax_1_id')->nullable()->after('tax_id');
                $table->foreign('tax_1_id')->references('id')->on('tax_rates')->nullOnDelete();
            }
            if (! Schema::hasColumn('transaction_sell_lines', 'tax_1')) {
                $table->decimal('tax_1', 22, 10)->default(0)->after('tax_1_id');
            }
            if (! Schema::hasColumn('transaction_sell_lines', 'tax_2_id')) {
                $table->unsignedInteger('tax_2_id')->nullable()->after('tax_1');
                $table->foreign('tax_2_id')->references('id')->on('tax_rates')->nullOnDelete();
            }
            if (! Schema::hasColumn('transaction_sell_lines', 'tax_2')) {
                $table->decimal('tax_2', 22, 10)->default(0)->after('tax_2_id');
            }
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'tax_1_id')) {
                $table->dropForeign(['tax_1_id']);
            }
            if (Schema::hasColumn('transactions', 'tax_2_id')) {
                $table->dropForeign(['tax_2_id']);
            }
            $table->dropColumn(['tax_1_id', 'tax_1', 'tax_2_id', 'tax_2', 'tax_total']);
        });

        Schema::table('transaction_sell_lines', function (Blueprint $table) {
            if (Schema::hasColumn('transaction_sell_lines', 'tax_1_id')) {
                $table->dropForeign(['tax_1_id']);
            }
            if (Schema::hasColumn('transaction_sell_lines', 'tax_2_id')) {
                $table->dropForeign(['tax_2_id']);
            }
            $table->dropColumn(['tax_1_id', 'tax_1', 'tax_2_id', 'tax_2']);
        });

        DB::statement('ALTER TABLE transactions MODIFY tax_amount DECIMAL(22,4) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE transactions MODIFY tax2_amount DECIMAL(22,4) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE transaction_sell_lines MODIFY item_tax DECIMAL(22,4) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE transaction_sell_lines MODIFY unit_price_inc_tax DECIMAL(22,4) NULL');
        DB::statement('ALTER TABLE transaction_sell_lines MODIFY tobacco_tax DECIMAL(22,4) NULL');
    }
};
