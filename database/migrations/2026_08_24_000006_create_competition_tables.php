<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->dateTime('registration_start_at')->nullable();
            $table->dateTime('registration_end_at')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->enum('status', ['draft', 'registration', 'ongoing', 'completed', 'cancelled'])->default('draft')->index();
            $table->decimal('registration_fee', 14, 2)->default(0);
            $table->text('rules')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('competition_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('category_type', ['men', 'women', 'mixed', 'open', 'junior', 'custom'])->default('open');
            $table->unsignedTinyInteger('min_players')->default(2);
            $table->unsignedTinyInteger('max_players')->default(2);
            $table->unsignedInteger('max_participants')->nullable();
            $table->decimal('registration_fee', 14, 2)->default(0);
            $table->enum('format', ['round_robin', 'knockout', 'round_robin_knockout'])->default('knockout');
            $table->timestamps();
        });

        Schema::create('competition_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('team_name')->nullable();
            $table->string('registration_code', 50)->unique();
            $table->enum('status', ['registered', 'confirmed', 'withdrawn', 'disqualified'])->default('registered');
            $table->unsignedSmallInteger('seed_number')->nullable();
            $table->unsignedInteger('points')->default(0);
            $table->unsignedInteger('wins')->default(0);
            $table->unsignedInteger('losses')->default(0);
            $table->integer('score_difference')->default(0);
            $table->timestamps();
        });

        Schema::create('competition_brackets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_category_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('round_number')->unique();
            $table->unsignedSmallInteger('match_number')->unique();
            $table->foreignId('participant_a_id')->nullable()->constrained('competition_participants')->nullOnDelete();
            $table->foreignId('participant_b_id')->nullable()->constrained('competition_participants')->nullOnDelete();
            $table->foreignId('winner_participant_id')->nullable()->constrained('competition_participants')->nullOnDelete();
            $table->enum('status', ['pending', 'ready', 'completed'])->default('pending');
            $table->timestamps();
        });

        Schema::create('competition_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competition_bracket_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('court_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('participant_a_id')->nullable()->constrained('competition_participants')->nullOnDelete();
            $table->foreignId('participant_b_id')->nullable()->constrained('competition_participants')->nullOnDelete();
            $table->foreignId('game_session_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('scheduled_at')->nullable()->index();
            $table->unsignedTinyInteger('round_number')->nullable();
            $table->enum('status', ['scheduled', 'ready', 'ongoing', 'completed', 'cancelled'])->default('scheduled')->index();
            $table->foreignId('winner_participant_id')->nullable()->constrained('competition_participants')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('competition_standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('competition_participants')->cascadeOnDelete()->unique();
            $table->unsignedInteger('played')->default(0);
            $table->unsignedInteger('won')->default(0);
            $table->unsignedInteger('lost')->default(0);
            $table->unsignedInteger('points')->default(0);
            $table->integer('score_for')->default(0);
            $table->integer('score_against')->default(0);
            $table->integer('score_difference')->default(0);
            $table->unsignedSmallInteger('rank')->nullable();
            $table->timestamps();
        });

        Schema::create('competition_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('winner_participant_id')->nullable()->constrained('competition_participants')->nullOnDelete();
            $table->json('score_summary')->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_results');
        Schema::dropIfExists('competition_standings');
        Schema::dropIfExists('competition_matches');
        Schema::dropIfExists('competition_brackets');
        Schema::dropIfExists('competition_participants');
        Schema::dropIfExists('competition_categories');
        Schema::dropIfExists('competitions');
    }
};
