<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('slug', 200)->nullable()->after('title');
            $table->index('slug');
        });

        // Backfill slugs for existing events so the frontend URLs work
        // immediately. Titles may collide, so append an id suffix when needed.
        $rows = DB::table('events')->orderBy('id')->get(['id', 'title', 'slug']);
        $used = [];
        foreach ($rows as $row) {
            if (! empty($row->slug)) {
                $used[$row->slug] = true;

                continue;
            }
            $base = Str::slug($row->title);
            $slug = $base;
            $i = 1;
            while (isset($used[$slug])) {
                $slug = $base.'-'.$i++;
            }
            $used[$slug] = true;
            DB::table('events')->where('id', $row->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropColumn('slug');
        });
    }
};