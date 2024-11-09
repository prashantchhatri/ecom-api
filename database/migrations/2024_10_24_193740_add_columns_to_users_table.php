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
            $table->foreignId('role_id')->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->after('role_id')->constrained()->onDelete('cascade');
            $table->string('phone')->after('email')->unique();
            $table->string('city')->after('phone');
            $table->string('address')->nullable()->after('city');
            $table->string('pincode')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['company_id']);
            $table->dropColumn(['role_id', 'company_id', 'phone', 'city', 'address', 'pincode']);
        });
    }
};
