<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop orphaned legacy tables (no code references them anymore).
     * Data was backed up to _dev-tools/backups before removal.
     * Note: role_permissions, offerings, sermon_series are intentionally NOT dropped
     * (pivot used by Role/Permission, offerings in migration path, sermon_series FK used).
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        // Child first (FK to member_groups)
        Schema::dropIfExists('member_group_assignments');
        Schema::dropIfExists('member_groups');
        Schema::dropIfExists('news');
        Schema::dropIfExists('mail_templates');
        Schema::dropIfExists('services');
        Schema::dropIfExists('user_devotional_progress');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('content');
            $table->string('image_url', 255)->nullable();
            $table->enum('status', ['draft', 'published', 'archived']);
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->integer('display_order')->default(0);
        });

        Schema::create('mail_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('subject', 255);
            $table->text('body');
            $table->text('variables');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });

        Schema::create('member_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description');
            $table->unsignedInteger('leader_id')->nullable();
            $table->foreign('leader_id')->references('id')->on('church_members')->nullOnDelete();
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });

        Schema::create('member_group_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('member_id');
            $table->foreign('member_id')->references('id')->on('church_members')->nullOnDelete();
            $table->unsignedInteger('group_id');
            $table->foreign('group_id')->references('id')->on('member_groups')->nullOnDelete();
            $table->date('join_date');
            $table->enum('status', ['active', 'inactive']);
            $table->timestamp('created_at');
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('subtitle', 255)->nullable();
            $table->text('description');
            $table->string('icon_class', 50)->nullable();
            $table->boolean('status');
            $table->integer('display_order')->default(0);
            $table->timestamp('created_at');
        });

        Schema::create('user_devotional_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->unsignedInteger('devotional_id');
            $table->foreign('devotional_id')->references('id')->on('devotionals')->nullOnDelete();
            $table->timestamp('read_at');
            $table->text('notes');
            $table->unique(['user_id', 'devotional_id']);
        });
    }
};
