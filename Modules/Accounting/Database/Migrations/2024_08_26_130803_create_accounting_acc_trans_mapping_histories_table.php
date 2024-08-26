<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounting_acc_trans_mapping_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->integer('accounting_accounts_transactions_history_id')->nullable();
            $table->string('ref_no', 100);
            $table->string('type', 100);
            $table->integer('created_by');
            $table->dateTime('operation_date');
            $table->text('note')->nullable();
            
          
            $table->timestamps();
        });
      
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_acc_trans_mapping_histories');
    }
};