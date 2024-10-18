<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Foreign keys
            $table->foreignId('role_id')->after('id')->constrained()->onDelete('cascade');
            
            // company_id can be null only for superadmin (role_id = 1)
            $table->foreignId('company_id')->nullable()->after('role_id')->constrained()->onDelete('cascade');

            // New columns
            $table->string('phone')->after('email')->unique();
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->string('address')->nullable()->after('phone_verified_at');
            $table->string('city')->after('address');
            $table->string('pincode')->nullable()->after('city');
        });

        // Add a check constraint for company_id being null only for superadmin (role_id = 1)
        DB::statement('ALTER TABLE users ADD CONSTRAINT check_company_id_null_for_superadmin CHECK (role_id != 1 OR company_id IS NULL)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the constraint
            DB::statement('ALTER TABLE users DROP CONSTRAINT check_company_id_null_for_superadmin');

            // Drop the foreign keys and columns
            $table->dropForeign(['company_id']);
            $table->dropForeign(['role_id']);
            $table->dropColumn(['company_id', 'role_id', 'phone', 'phone_verified_at', 'address', 'city', 'pincode']);
        });
    }
};


