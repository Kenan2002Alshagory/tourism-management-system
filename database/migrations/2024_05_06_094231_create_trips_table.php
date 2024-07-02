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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('trip_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration');
            $table->string('from');
            $table->string('destination');
            $table->string('guide_name');
            $table->integer('travelers_num');
            $table->string('trip_type');
            $table->integer('trip_price');
            $table->enum('trip_status', ['active', 'completed', 'cancelled', 'pending' , 'without_details'])->nullable();
            $table->string('status_time');
            $table->text('trip_description')->nullable();
            $table->string('trip_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
