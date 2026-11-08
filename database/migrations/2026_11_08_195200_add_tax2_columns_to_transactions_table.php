<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('tax2_id')->unsigned()->nullable()->after('tax_amount');
            $table->foreign('tax2_id')->references('id')->on('tax_rates')->onDelete('cascade');
            $table->decimal('tax2_amount', 22, 4)->default(0)->after('tax2_id');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['tax2_id']);
            $table->dropColumn(['tax2_id', 'tax2_amount']);
        });
    }
};
