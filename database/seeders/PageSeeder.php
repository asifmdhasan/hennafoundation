<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $campaign = 'Please donate to Henna Foundation’s Pink Chai Day 2025 campaign, raising vital awareness of breast cancer across the UK. Breast cancer will touch 1 in 7 women in their lifetime, yet many still miss screening due to stigma, language and access barriers. Your gift funds culturally sensitive outreach, translations, myth-busting talks, and signposting so women book screening earlier. It also trains local volunteers and organises community events that share life-saving messages over a cup of pink chai. Give what you can. Donate—help save lives.';

        Page::query()->firstOrCreate(
            ['slug' => 'pink-chai-day-2025'],
            [
                'title' => 'Pink Chai Day 2025',
                'excerpt' => $campaign,
                'content' => $campaign,
                'status' => 'published',
                'published_at' => now(),
            ],
        );

        foreach ([
            'about-us' => 'About Us',
            'projects' => 'Projects',
            'blog' => 'Blog',
            'contact-us' => 'Contact Us',
            'donate' => 'Donate',
            'partnership-registration' => 'Partnership Registration',
            'volunteer-registration' => 'Volunteer Registration',
        ] as $slug => $title) {
            Page::query()->firstOrCreate(
                ['slug' => $slug],
                ['title' => $title, 'content' => '', 'status' => 'draft'],
            );
        }

        Page::query()->firstOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Home',
                'content' => '',
                'status' => 'published',
                'published_at' => now(),
            ],
        );
    }
}
