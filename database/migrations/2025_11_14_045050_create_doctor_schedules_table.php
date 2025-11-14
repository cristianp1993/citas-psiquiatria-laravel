<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('patient_name');
            $table->string('patient_email');
            $table->string('patient_phone')->nullable();
            $table->dateTimeTz('start_at');
            $table->dateTimeTz('end_at');
            $table->enum('status',['pending','confirmed','completed','rejected'])->default('pending');
            $table->string('slug')->unique();
            $table->timestamps();
            $table->index(['doctor_id','start_at','end_at','status']);
        });

        DB::statement("CREATE UNIQUE INDEX appointments_no_overlap ON appointments(doctor_id, start_at) WHERE status IN ('pending','confirmed')");
    }
    public function down(): void {
        Schema::dropIfExists('appointments');
        DB::statement("DROP INDEX IF EXISTS appointments_no_overlap");
    }
};

