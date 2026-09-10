<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('res_tables', 'status')) {
            Schema::table('res_tables', function (Blueprint $table) {
                $table->tinyInteger('status')->default(0)->after('description');
            });
        }

        if (! Schema::hasColumn('res_tables', 'assigned_waiter_id')) {
            Schema::table('res_tables', function (Blueprint $table) {
                $table->unsignedInteger('assigned_waiter_id')->nullable()->after('status');
            });
        }
    }

    public function down()
    {
        Schema::table('res_tables', function (Blueprint $table) {
            if (Schema::hasColumn('res_tables', 'assigned_waiter_id')) {
                $table->dropColumn('assigned_waiter_id');
            }
            if (Schema::hasColumn('res_tables', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
