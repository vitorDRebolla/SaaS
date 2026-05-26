<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('status_id')->constrained('issue_statuses')->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('none'); // none, low, medium, high, urgent
            $table->unsignedInteger('sequence_number');
            $table->float('position')->default(0);
            $table->date('due_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'sequence_number']);
            $table->index(['project_id', 'status_id', 'position']);
            $table->index(['team_id', 'assignee_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('issues'); }
};
