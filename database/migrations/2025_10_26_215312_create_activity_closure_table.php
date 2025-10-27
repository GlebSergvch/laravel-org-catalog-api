<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('activity_closure', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ancestor_id')->constrained('activities')->cascadeOnDelete();
            $table->foreignId('descendant_id')->constrained('activities')->cascadeOnDelete();
            $table->unsignedInteger('depth')->default(0);
            $table->timestamps();

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->unique(['ancestor_id', 'descendant_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_closure');
    }
};
