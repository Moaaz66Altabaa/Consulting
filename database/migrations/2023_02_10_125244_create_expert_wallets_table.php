<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expert_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expert_id');
            $table->float('total')->default(10000);
            $table->foreign('expert_id')->references('id')->on('experts')->onDelete('cascade');        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expert_wallets');
    }
};
