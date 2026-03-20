<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('address')->nullable();
            $table->decimal('monthly_rent', 10, 2)->default(0);
            $table->decimal('default_iva_percentage', 5, 2)->default(0);
            $table->decimal('default_retention_percentage', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('current_tenant_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
