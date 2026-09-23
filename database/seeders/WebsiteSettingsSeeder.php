<?php

namespace Database\Seeders;

use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;

class WebsiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => ['Henna Foundation', 'general'],
            'purpose_statement' => ['For the public benefit, the prevention of poverty in Worthing, West Sussex, and Bangladesh by providing or assisting in the provision of education, training, health, and all necessary support designed to enable individuals to generate sustainable income and self-sufficient.', 'general'],
            'charity_number' => ['1203971', 'general'],
            'address' => ['Worthing, West Sussex, United Kingdom', 'contact'],
            'contact_email' => ['hello@hennafoundation.org.uk', 'contact'],
            'site_website_display' => ['www.hennafoundation.org.uk', 'general'],
            'site_website_url' => ['https://hennafoundation.org.uk/', 'general'],
            'footer_website_label' => ['Website', 'footer'],
            'footer_email_label' => ['Email', 'footer'],
            'footer_address_label' => ['Address', 'footer'],
            'social_youtube' => ['https://www.youtube.com/@HennaFoundation', 'social'],
            'social_youtube_label' => ['YouTube', 'social'],
            'social_facebook' => ['', 'social'],
            'social_twitter' => ['', 'social'],
            'social_instagram' => ['', 'social'],
            'site_logo_media_id' => ['', 'branding'],
            'home_hero_eyebrow' => ['GUARDIANS OF GIVING', 'homepage'],
            'home_hero_title' => ['Giving is a rewarding experience', 'homepage'],
            'home_hero_description' => ['At Henna Foundation, giving is not just an act; it’s a deeply rewarding experience that enriches both the giver and the recipient.', 'homepage'],
            'home_hero_cta' => ['About Us', 'homepage'],
            'home_hero_cta_url' => ['/about-us/', 'homepage'],
            'home_actions_aria' => ['Ways to support Henna Foundation', 'homepage'],
            'home_actions' => [json_encode([
                ['title' => 'Become a Volunteer', 'description' => 'Becoming a volunteer with Henna Foundation offers countless benefits beyond the act of service itself.', 'label' => 'Join Now', 'url' => '/volunteer-registration/'],
                ['title' => 'Make a Donation', 'description' => 'Making a donation to projects that enhance women empowerment is a powerful way to create positive change.', 'label' => 'Donate Now', 'url' => '/donate/'],
                ['title' => 'Become a Partner', 'description' => 'Becoming a partner with Henna Foundation offers numerous benefits that extend far beyond a mere collaboration.', 'label' => 'Join Now', 'url' => '/partnership-registration/'],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'homepage'],
            'home_projects_eyebrow' => ['OUR PROJECTS', 'homepage'],
            'home_projects_title' => ['Creating Awareness, Driving Change', 'homepage'],
            'home_projects_description' => ['Your support enables us to empower women for a better life, both locally and globally.', 'homepage'],
            'home_main_cta' => ['Together, let’s make a meaningful difference in the world and build a future where everyone has the opportunity to thrive.', 'homepage'],
            'home_main_cta_label' => ['Get Involved Now', 'homepage'],
            'home_main_cta_url' => ['/volunteer-registration/', 'homepage'],
            'home_main_cta_image_id' => ['', 'homepage'],
            'home_campaign_eyebrow' => ['OUR CAMPAIGN', 'homepage'],
            'home_campaign_cta' => ['Donate Now', 'homepage'],
            'home_campaign_cta_url' => ['/donate/', 'homepage'],
            'home_empty_projects' => ['Project details will be available soon.', 'homepage'],
            'home_empty_blog' => ['New stories will be shared here soon.', 'homepage'],
            'home_blog_cta' => ['View All Blog', 'homepage'],
            'home_blog_cta_url' => ['/blog/', 'homepage'],
            'home_nav_items' => [json_encode([
                ['label' => 'Home', 'url' => '/', 'is_cta' => false],
                ['label' => 'About Us', 'url' => '/about-us/', 'is_cta' => false],
                ['label' => 'Projects', 'url' => '/projects/', 'is_cta' => false],
                ['label' => 'Blog', 'url' => '/blog/', 'is_cta' => false],
                ['label' => 'Contact Us', 'url' => '/contact-us/', 'is_cta' => false],
                ['label' => 'Donate Now', 'url' => '/donate/', 'is_cta' => true],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'homepage'],
            'footer_heading' => ['Henna Foundation', 'footer'],
            'footer_links_heading' => ['Quick Links', 'footer'],
            'footer_contact_heading' => ['Contact Us', 'footer'],
            'footer_links' => [json_encode([
                ['label' => 'About Us', 'url' => '/about-us/'],
                ['label' => 'Projects', 'url' => '/projects/'],
                ['label' => 'Events', 'url' => '/events/'],
                ['label' => 'Blog', 'url' => '/blog/'],
                ['label' => 'Contact Us', 'url' => '/contact-us/'],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'footer'],
            'footer_credit' => ['Developed by printAgraphy', 'footer'],
            'footer_copyright_text' => ['All Rights Reserved.', 'footer'],
            'footer_charity_label' => ['Registered Charity Number', 'footer'],
            'home_gallery_title' => ['Portraits of Compassion', 'homepage'],
            'home_gallery_eyebrow' => ['OUR GALLERY', 'homepage'],
            'home_blog_title' => ['Sharing Women’s Stories of Strength and Inspiration', 'homepage'],
            'home_blog_eyebrow' => ['OUR BLOG', 'homepage'],
            'home_blog_paragraphs' => [json_encode([
                'Welcome to our blog! Here, we share stories that inspire change and celebrate women’s empowerment. From busy cities to quiet villages, each story shows how strong and determined women can be. Our blog gives a voice to women whose stories are often overlooked.',
                'Through personal stories, interesting articles, and interviews, we highlight the successes, struggles, and dreams of women from all over the world. Join us as we celebrate the power of storytelling and the amazing things women can achieve.',
            ], JSON_UNESCAPED_UNICODE), 'homepage'],
            'blog_page_description' => ['Stories and updates from Henna Foundation.', 'content'],
            'blog_page_empty' => ['New stories will be shared here soon.', 'content'],
        ];

        foreach ($settings as $key => [$value, $group]) {
            WebsiteSetting::query()->firstOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group],
            );
        }
    }
}
