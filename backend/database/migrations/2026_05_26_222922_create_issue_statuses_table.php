<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('issue_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->default('#94a3b8');
            $table->string('type')->default('backlog'); // backlog, started, completed, cancelled
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index(['project_id', 'position']);
        });
    }
    public function down(): void { Schema::dropIfExists('issue_statuses'); }
};
