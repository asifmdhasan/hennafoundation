<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Page;
use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AboutUsContentSeeder extends Seeder
{
    public function run(): void
    {
        if (WebsiteSetting::query()->where('key', 'about_content_seed_version')->exists()) {
            return;
        }

        $page = Page::query()->firstOrCreate(
            ['slug' => 'about-us'],
            ['title' => 'About Us', 'content' => '', 'status' => 'draft'],
        );

        $paragraphs = [
            'The inspiration behind Henna Foundation is Henna Chowdhury. It was her life journey from being an orphan at the age of 7 to becoming Mayor of Worthing and the struggles of her journey which motivated her to continue her mission to empower women to become the best version of themselves by undertaking formal education, training, health to becoming self-sustainable. Henna is an inspirational person, who has been appointed on the board of, Oak Grove College, Tafida Raqeeb Foundation, a member of Worthing Lions and an ambassador for Andy’s Angels. She is always helping those in need both in Worthing and Sylhet, Bangladesh.',
            'Henna Chowdhury, the founder of Henna Foundation, is a remarkable woman on a mission to empower women towards a better life. Her unwavering dedication to giving back and making a measurable difference through Henna Foundation is truly inspiring. With a heart full of compassion and a vision for positive change, Henna has embarked on a lifelong journey to help those in need.',
            'Through her leadership and tireless efforts of all involved, Henna Foundation will become a beacon of hope for women facing adversity in both local communities and abroad. Henna’s passion for empowering others is not just a mission—it’s a calling that drives her to create lasting impact and build a brighter future for generations to come.',
        ];

        $patronBiography = [
            'Ifty Islam, the Founder Chairman of Asian Tiger Capital Partners, is a prominent figure in the financial and technology sectors, with bases in Dhaka and London. His extensive experience includes advising the China Consortium on strategic investments by the Shenzhen and Shanghai Stock Exchanges in the Dhaka Stock Exchange. Additionally, he has spearheaded joint venture partnerships with leading Chinese Fintech Kingdom Shenzhen Sci. Tech.',
            'Prior to his role at Asian Tiger Capital Partners, Ifty served as CEO and co-founder of Miup, a pioneering Health AI company based in the University of Tokyo. During his tenure, Miup developed a primary healthcare platform utilising AI and big data technologies to advance pre-emptive medicine in combating Non-Communicable Diseases (NCDs).',
            'Before venturing into entrepreneurship, Ifty held significant positions at Citigroup and Deutsche Bank Securities, where he played instrumental roles in macro strategy, hedge fund research, and served as Managing Director. He is an alumnus of Queen’s College, Oxford, graduating with a BA/MA (First Class) in Politics, Philosophy, and Economics.',
        ];

        $settings = [
            'about_content_seed_version' => '1',
            'about_hero_breadcrumb_title' => 'About Us',
            'about_show_hero' => '1',
            'about_hero_overlay' => '1',
            'about_hero_text' => '',
            'about_show_inspiration' => '1',
            'about_inspiration_eyebrow' => 'Know US',
            'about_inspiration_heading' => 'The inspiration behind Henna Foundation',
            'about_inspiration_paragraphs' => $this->json($paragraphs),
            'about_inspiration_button_visible' => '1',
            'about_inspiration_button_label' => 'Donate Now',
            'about_inspiration_button_url' => '/donate/',
            'about_show_patron' => '1',
            'about_patron_eyebrow' => 'PATRON',
            'about_patron_name' => 'Ifty Islam',
            'about_patron_designation' => 'CEO AT Capital & Pathfinderhub CIC',
            'about_patron_biography' => $this->json($patronBiography),
            'about_patron_image_media_id' => '',
            'about_show_trustees' => '1',
            'about_trustees_eyebrow' => 'Our People',
            'about_trustees_heading' => 'The Board of Trustees',
            'about_trustees' => $this->json($this->items([
                ['name' => 'Fariha Ema Aniqa Chowdhury'],
                ['name' => 'Tasnim Maisha Chowdhury'],
                ['name' => 'Mariya Hossain Chowdhury'],
                ['name' => 'Anwar Ali OBE'],
                ['name' => 'Dr Musharraf Hussain'],
                ['name' => 'Tahmena Khan'],
            ])),
            'about_show_goals' => '1',
            'about_goals_eyebrow' => 'OUR GOALS',
            'about_goals_heading' => 'Building Foundations for a Better Tomorrow',
            'about_goals_intro' => 'Encapsulates the core mission of the Henna Foundation: to establish sustainable pillars that support and enhance the welfare of communities around the globe. Our approach is holistic, tackling the essential areas of life that form the backbone of thriving societies.',
            'about_goals' => $this->json($this->items([
                ['icon' => '', 'title' => 'Skills training', 'description' => 'We will work with our local education and skills providers to deliver community-based learning for people from marginalised communities'],
                ['icon' => '', 'title' => 'Medical & Health', 'description' => 'Providing essential healthcare services to improve well-being and save lives.'],
                ['icon' => '', 'title' => 'Health improvement', 'description' => 'We will work in partnership with our local health organisations to hold community-based health awareness for marginalised communities'],
                ['icon' => '', 'title' => 'Education', 'description' => 'Empowering individuals through education to create opportunities and foster development.'],
            ])),
            'about_show_heart' => '1',
            'about_heart_eyebrow' => '',
            'about_heart_heading' => 'The Heart of Henna Foundation',
            'about_heart_paragraphs' => $this->json([
                'At Henna Foundation, giving is not just an act; it’s a deeply rewarding experience that enriches both the giver and the recipient. Our organisation operates on the belief that generosity has the power to transform lives and communities. The joy that comes from knowing that we have made a positive difference in someone’s life is immeasurable, and it reinforces our commitment to serving others with compassion, empathy, and humility.',
                'Through the gift of giving, we not only create positive change in the world but also cultivate a sense of fulfilment and purpose that transcends individual actions.',
            ]),
            'about_show_plan' => '1',
            'about_plan_eyebrow' => 'OUR PLAN',
            'about_plan_heading' => 'Plan for Next 1-3 Years',
            'about_plan_intro' => 'Our plan is to establish the infrastructure of the charity by putting the following in place',
            'about_plan_items' => $this->json($this->items([
                ['title' => 'Governance training for trustees'],
                ['title' => 'Appointing patrons & ambassadors'],
                ['title' => 'Recruiting & training volunteers'],
                ['title' => 'Setting up the website, promotional material, policies, and procedures'],
                ['title' => 'Holding focus groups with women and carrying out needs’ surveys'],
                ['title' => 'Holding a launch event'],
                ['title' => 'Setting up projects such as women empowerment workshops and regular activities for women.'],
                ['title' => 'Currently developing the Live Well project for South Asian Women in Worthing'],
                ['title' => 'Education & Skills training projects – we will work with our local education and skills providers to deliver community-based learning'],
                ['title' => 'Health improvement projects – we will work in partnership with our local health organisations to hold community-based health awareness and'],
                ['title' => 'Health promotion activity'],
                ['title' => 'Once a year hold an annual event to present our annual report'],
                ['title' => 'Fundraising activities'],
                ['title' => 'Develop empowerment projects for women in Bangladesh.'],
                ['title' => 'Develop alliances and partnerships with local NGOs in Bangladesh to deliver empowerment, through education and economic development projects so women can live with dignity.'],
                ['title' => 'The current idea being developed is to embark on a project to raise awareness of breast cancer, providing screening and training in Bangladesh with a group of British doctors and nurses.'],
            ])),
            'about_show_charity_purpose' => '1',
            'about_charity_heading' => '',
            'about_charity_content' => 'For the public benefit, the prevention of poverty in Worthing, West Sussex, and Bangladesh by providing or assisting in the provision of education, training, health, and all necessary support designed to enable individuals to generate sustainable income and self-sufficient.',
            'about_charity_number' => '1203971',
        ];

        DB::transaction(function () use ($page, $settings): void {
            foreach ($settings as $key => $value) {
                WebsiteSetting::query()->firstOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => 'about-us'],
                );
            }

            if ($page->status === 'draft' && blank($page->content)) {
                $page->forceFill([
                    'title' => 'About Us',
                    'status' => 'published',
                    'published_at' => now(),
                ])->save();
            }

            if (! $page->mediaAttachments()->wherePivot('role', 'about-inspiration')->exists()) {
                $imageNames = ['image7.webp', 'image9.webp', 'image6.webp', 'image4.webp'];
                $mediaIds = Media::query()
                    ->whereIn('original_filename', $imageNames)
                    ->get()
                    ->sortBy(fn (Media $media): int => array_search($media->original_filename, $imageNames, true))
                    ->pluck('id')
                    ->values();

                foreach ($mediaIds as $sortOrder => $mediaId) {
                    $page->mediaAttachments()->attach($mediaId, [
                        'role' => 'about-inspiration',
                        'sort_order' => $sortOrder,
                    ]);
                }
            }
        });
    }

    private function items(array $items): array
    {
        return collect($items)->values()->map(function (array $item, int $sortOrder): array {
            return array_merge([
                'designation' => '',
                'image_media_id' => '',
                'facebook_url' => '',
                'twitter_url' => '',
                'linkedin_url' => '',
                'instagram_url' => '',
                'other_profile_url' => '',
                'description' => '',
                'is_visible' => true,
                'sort_order' => $sortOrder,
            ], $item);
        })->all();
    }

    private function json(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
