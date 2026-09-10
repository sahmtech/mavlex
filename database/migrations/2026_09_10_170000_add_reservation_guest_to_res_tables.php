<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('res_tables', 'reserved_guest_name')) {
            Schema::table('res_tables', function (Blueprint $table) {
                $table->string('reserved_guest_name')->nullable();
            });
        }
        if (! Schema::hasColumn('res_tables', 'reserved_guest_phone')) {
            Schema::table('res_tables', function (Blueprint $table) {
                $table->string('reserved_guest_phone', 50)->nullable();
            });
        }
        if (! Schema::hasColumn('res_tables', 'reserved_note')) {
            Schema::table('res_tables', function (Blueprint $table) {
                $table->string('reserved_note')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::table('res_tables', function (Blueprint $table) {
            foreach (['reserved_note', 'reserved_guest_phone', 'reserved_guest_name'] as $column) {
                if (Schema::hasColumn('res_tables', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
