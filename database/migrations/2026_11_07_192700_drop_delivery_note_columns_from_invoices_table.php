<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'delivery_note_number')) {
                $table->dropColumn('delivery_note_number');
            }
            if (Schema::hasColumn('invoices', 'type')) {
                $table->dropColumn('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'type')) {
                $table->enum('type', ['invoice', 'delivery_note'])->default('invoice')->after('id');
            }
            if (!Schema::hasColumn('invoices', 'delivery_note_number')) {
                $table->string('delivery_note_number')->unique()->nullable()->after('invoice_number');
            }
        });
    }
};

