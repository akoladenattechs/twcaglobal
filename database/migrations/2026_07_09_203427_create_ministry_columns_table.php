<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ministry_columns', function (Blueprint $table) {
            $table->id();
            $table->string('column_type')->default('ministry'); // 'ministry' or 'quote'
            $table->string('icon_class')->nullable();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('quote_author')->nullable();
            $table->integer('display_order')->default(0);
            $table->string('status')->default('published');
            $table->timestamps();
        });

        // Seed the 4 default columns
        $now = now();
        DB::table('ministry_columns')->insert([
            [
                'column_type' => 'ministry',
                'icon_class' => 'flaticon-church',
                'title' => 'Worship',
                'subtitle' => 'Our Fellowship',
                'description' => 'We hold firm with honour the true doctrine of the Bible by rightly dividing the word of God, in order that our worship may be right.',
                'quote_author' => null,
                'display_order' => 0,
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'column_type' => 'ministry',
                'icon_class' => 'flaticon-pray',
                'title' => 'Prayer',
                'subtitle' => 'Art Of Prayer',
                'description' => 'We believe so much in the art of prayer, we believe that prayer is a necessity for every believer, and we give ourselves to prayers.',
                'quote_author' => null,
                'display_order' => 1,
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'column_type' => 'ministry',
                'icon_class' => 'flaticon-love',
                'title' => "God's Love",
                'subtitle' => "Sharing God's Love",
                'description' => "God's love for every man is to be saved from eternal damnation by believing the gospel of Jesus Christ. And this, we're heralding.",
                'quote_author' => null,
                'display_order' => 2,
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'column_type' => 'quote',
                'icon_class' => null,
                'title' => 'The actual warfare is wordfare. A Christian soldier is a word-warrior; he wars with the WORD. He talks back against the wiles of darkness.',
                'subtitle' => null,
                'description' => null,
                'quote_author' => '~ TWCA\'s NUGGEST',
                'display_order' => 3,
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ministry_columns');
    }
};
