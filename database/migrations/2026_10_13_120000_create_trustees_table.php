<?php

use App\Models\WebsiteSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trustees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('designation')->nullable();
            $table->longText('content')->nullable();
            $table->foreignId('image_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('other_profile_url')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at', 'sort_order']);
        });

        $setting = WebsiteSetting::query()->where('key', 'about_trustees')->value('value');
        $items = json_decode($setting ?: '[]', true);
        if (! is_array($items)) {
            return;
        }

        foreach (array_values($items) as $index => $item) {
            if (! is_array($item) || blank($item['name'] ?? null)) {
                continue;
            }

            $baseSlug = Str::slug($item['name']) ?: 'trustee';
            $slug = $baseSlug;
            $suffix = 2;
            while (DB::table('trustees')->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$suffix++;
            }

            DB::table('trustees')->insert([
                'name' => $item['name'],
                'slug' => $slug,
                'designation' => $item['designation'] ?? null,
                'image_media_id' => $item['image_media_id'] ?? null,
                'facebook_url' => $item['facebook_url'] ?? null,
                'twitter_url' => $item['twitter_url'] ?? null,
                'linkedin_url' => $item['linkedin_url'] ?? null,
                'instagram_url' => $item['instagram_url'] ?? null,
                'other_profile_url' => $item['other_profile_url'] ?? null,
                'status' => filter_var($item['is_visible'] ?? true, FILTER_VALIDATE_BOOLEAN) ? 'published' : 'draft',
                'sort_order' => is_numeric($item['sort_order'] ?? null) ? (int) $item['sort_order'] : $index,
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('trustees');
    }
};
