<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            ['Breast Cancer Project', 'breast-cancer-project', 'Promoting early detection and awareness of breast cancer among women in underserved communities.'],
            ['Women Empowerment', 'women-empowerment', 'Equipping women with education, skills, and leadership tools to achieve independence and equality.'],
            ['Climate Resilience', 'climate-resilience', 'Supporting community-led initiatives that empower women to combat climate change and its effects.'],
        ];

        foreach ($projects as $sortOrder => [$title, $slug, $summary]) {
            Project::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'summary' => $summary,
                    'status' => 'published',
                    'sort_order' => $sortOrder + 1,
                    'published_at' => now(),
                ],
            );
        }
    }
}
