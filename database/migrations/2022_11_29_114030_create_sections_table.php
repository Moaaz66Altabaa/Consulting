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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('sectionName');
            $table->string('imagePath')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });

        \App\Models\Category::find(1)->sections()->create(['sectionName' => 'C++']);
        \App\Models\Category::find(1)->sections()->create(['sectionName' => 'Java']);
        \App\Models\Category::find(1)->sections()->create(['sectionName' => 'JavaScript']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sections');
    }
};
