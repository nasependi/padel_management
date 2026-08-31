<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_number', 50)->unique();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('court_id')->constrained()->restrictOnDelete();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->enum('status', ['scheduled', 'active', 'paused', 'completed', 'cancelled'])->default('scheduled')->index();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('session_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('guest_phone', 30)->nullable();
            $table->unsignedTinyInteger('position')->nullable();
            $table->string('side', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('session_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('team_number');
            $table->timestamps();
            $table->unique(['game_session_id', 'team_number']);
        });

        Schema::create('session_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->enum('status', ['pending', 'checked_in', 'checked_out', 'no_show'])->default('pending');
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('session_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 50);
            $table->json('payload')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('scoreboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('board_code', 50)->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('match_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('set_number')->default(1);
            $table->foreignId('team_a_id')->nullable()->constrained('session_teams')->nullOnDelete();
            $table->foreignId('team_b_id')->nullable()->constrained('session_teams')->nullOnDelete();
            $table->unsignedTinyInteger('team_a_score')->default(0);
            $table->unsignedTinyInteger('team_b_score')->default(0);
            $table->boolean('is_finished')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->index(['game_session_id', 'set_number']);
        });

        Schema::create('score_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_score_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('old_team_a_score')->default(0);
            $table->unsignedTinyInteger('old_team_b_score')->default(0);
            $table->unsignedTinyInteger('new_team_a_score')->default(0);
            $table->unsignedTinyInteger('new_team_b_score')->default(0);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_logs');
        Schema::dropIfExists('match_scores');
        Schema::dropIfExists('scoreboards');
        Schema::dropIfExists('session_events');
        Schema::dropIfExists('session_checkins');
        Schema::dropIfExists('session_teams');
        Schema::dropIfExists('session_players');
        Schema::dropIfExists('game_sessions');
    }
};
