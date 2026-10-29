<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->string('dept_id')->primary();
            $table->string('name');
            $table->string('manager_employee')->nullable();
            $table->string('company')->default('Arunika');
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('department_id');
            $table->string('job_title');
            $table->string('location');
            $table->string('status')->default('Aktif'); // 'Aktif' | 'Nonaktif'
            $table->unsignedBigInteger('mapped_user_id')->nullable();
            $table->foreign('mapped_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('department_id')->references('dept_id')->on('departments');
            $table->timestamps();
        });

        Schema::create('employee_private_records', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('personal_email');
            $table->string('emergency_contact');
            $table->string('private_phone');
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_private_records');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('departments');
    }
};
