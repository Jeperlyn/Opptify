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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->after('email');
            $table->timestamp('phone_number_verified_at')->nullable()->after('phone_number');
            $table->boolean('wants_job_fair_sms')->default(false)->after('remember_token');
            $table->boolean('wants_employer_sms')->default(false)->after('wants_job_fair_sms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_number',
                'phone_number_verified_at',
                'wants_job_fair_sms',
                'wants_employer_sms',
            ]);
        });
    }
};
