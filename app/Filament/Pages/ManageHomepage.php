<?php

namespace App\Filament\Pages;

use App\Models\Media;
use App\Models\Page as ContentPage;
use App\Models\WebsiteSetting;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageHomepage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Homepage Editor';

    protected static ?string $slug = 'homepage';

    protected static ?string $title = 'Homepage Editor';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.manage-homepage';

    public ?array $data = [];

    public function mount(): void
    {
        $homePage = ContentPage::query()->firstOrCreate(
            ['slug' => 'home'],
            ['title' => 'Home', 'content' => '', 'status' => 'published', 'published_at' => now()],
        );

        $campaign = ContentPage::query()->firstOrCreate(
            ['slug' => 'pink-chai-day-2025'],
            ['title' => 'Pink Chai Day 2025', 'content' => '', 'excerpt' => '', 'status' => 'draft'],
        );

        $settings = WebsiteSetting::query()->pluck('value', 'key');
        $this->form->fill([
            'home_hero_eyebrow' => $settings->get('home_hero_eyebrow'),
            'home_hero_title' => $settings->get('home_hero_title'),
            'home_hero_description' => $settings->get('home_hero_description'),
            'home_hero_cta' => $settings->get('home_hero_cta'),
            'home_hero_cta_url' => $settings->get('home_hero_cta_url'),
            'hero_image_id' => $homePage->og_image_media_id,
            'home_actions' => $this->decode($settings->get('home_actions')),
            'home_projects_eyebrow' => $settings->get('home_projects_eyebrow'),
            'home_projects_title' => $settings->get('home_projects_title'),
            'home_projects_description' => $settings->get('home_projects_description'),
            'home_main_cta' => $settings->get('home_main_cta'),
            'home_main_cta_label' => $settings->get('home_main_cta_label'),
            'home_main_cta_url' => $settings->get('home_main_cta_url'),
            'home_main_cta_image_id' => $settings->get('home_main_cta_image_id'),
            'home_campaign_eyebrow' => $settings->get('home_campaign_eyebrow'),
            'campaign_title' => $campaign->title,
            'campaign_description' => $campaign->excerpt,
            'campaign_cta' => $settings->get('home_campaign_cta'),
            'campaign_cta_url' => $settings->get('home_campaign_cta_url'),
            'campaign_image_id' => $campaign->og_image_media_id,
            'home_gallery_eyebrow' => $settings->get('home_gallery_eyebrow'),
            'home_gallery_title' => $settings->get('home_gallery_title'),
            'gallery_media_ids' => $homePage->mediaAttachments()->pluck('media.id')->all(),
            'home_blog_eyebrow' => $settings->get('home_blog_eyebrow'),
            'home_blog_title' => $settings->get('home_blog_title'),
            'home_blog_paragraphs' => collect($this->decode($settings->get('home_blog_paragraphs')))
                ->map(fn ($paragraph) => ['paragraph' => $paragraph])->all(),
            'home_blog_cta' => $settings->get('home_blog_cta'),
            'home_blog_cta_url' => $settings->get('home_blog_cta_url'),
            'home_empty_projects' => $settings->get('home_empty_projects'),
            'home_empty_blog' => $settings->get('home_empty_blog'),
            'home_nav_items' => $this->decode($settings->get('home_nav_items')),
            'site_logo_media_id' => $settings->get('site_logo_media_id'),
            'site_website_display' => $settings->get('site_website_display'),
            'site_website_url' => $settings->get('site_website_url'),
            'contact_email' => $settings->get('contact_email'),
            'address' => $settings->get('address'),
            'social_facebook' => $settings->get('social_facebook'),
            'social_twitter' => $settings->get('social_twitter'),
            'social_instagram' => $settings->get('social_instagram'),
            'social_youtube' => $settings->get('social_youtube'),
            'footer_heading' => $settings->get('footer_heading'),
            'footer_links_heading' => $settings->get('footer_links_heading'),
            'footer_contact_heading' => $settings->get('footer_contact_heading'),
            'footer_links' => $this->decode($settings->get('footer_links')),
            'footer_text' => $settings->get('footer_text'),
            'footer_credit' => $settings->get('footer_credit'),
            'footer_copyright_text' => $settings->get('footer_copyright_text'),
            'footer_charity_label' => $settings->get('footer_charity_label'),
            'home_actions_aria' => $settings->get('home_actions_aria'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('1. Hero section')
                    ->description('The first section visitors see. Upload images in Media first, then choose them here.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('home_hero_eyebrow')->label('Small heading'),
                        TextInput::make('home_hero_title')->label('Main heading')->required(),
                        Textarea::make('home_hero_description')->label('Description')->rows(3)->columnSpanFull(),
                        TextInput::make('home_hero_cta')->label('Button text'),
                        TextInput::make('home_hero_cta_url')->label('Button URL'),
                        $this->mediaSelect('hero_image_id', 'Hero image'),
                    ]),
                Section::make('2. Action cards')
                    ->description('Add, remove, reorder, and edit the three support cards. Each card can have its own image.')
                    ->schema([
                        TextInput::make('home_actions_aria')->label('Accessible section label'),
                        Repeater::make('home_actions')
                            ->label('Cards')
                            ->schema([
                                TextInput::make('title')->label('Card heading')->required(),
                                Textarea::make('description')->label('Card description')->rows(3),
                                TextInput::make('label')->label('Button text'),
                                TextInput::make('url')->label('Button URL'),
                                $this->mediaSelect('image_media_id', 'Card image'),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add support card')
                            ->defaultItems(0),
                    ]),
                Section::make('3. Projects section')
                    ->description('Edit the project cards themselves from Content → Projects. These fields control the section heading.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('home_projects_eyebrow')->label('Small heading'),
                        TextInput::make('home_projects_title')->label('Section heading'),
                        Textarea::make('home_projects_description')->label('Section description')->rows(3)->columnSpanFull(),
                        Textarea::make('home_empty_projects')->label('Message when there are no published projects')->rows(2)->columnSpanFull(),
                    ]),
                Section::make('4. Main call to action')
                    ->columns(2)
                    ->schema([
                        Textarea::make('home_main_cta')->label('Main message')->rows(3)->columnSpanFull(),
                        TextInput::make('home_main_cta_label')->label('Button text'),
                        TextInput::make('home_main_cta_url')->label('Button URL'),
                        $this->mediaSelect('home_main_cta_image_id', 'Background image'),
                    ]),
                Section::make('5. Campaign section')
                    ->description('Pink Chai Day is stored as a Page. Its title, description, and image are editable here or from Content → Pages.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('home_campaign_eyebrow')->label('Small heading'),
                        TextInput::make('campaign_title')->label('Campaign title')->required(),
                        Textarea::make('campaign_description')->label('Campaign description')->rows(5)->columnSpanFull(),
                        TextInput::make('campaign_cta')->label('Button text'),
                        TextInput::make('campaign_cta_url')->label('Button URL'),
                        $this->mediaSelect('campaign_image_id', 'Campaign image'),
                    ]),
                Section::make('6. Gallery section')
                    ->description('Choose uploaded images in their display order. Image alt text and captions are managed in Media.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('home_gallery_eyebrow')->label('Small heading'),
                        TextInput::make('home_gallery_title')->label('Section heading'),
                        Select::make('gallery_media_ids')
                            ->label('Gallery images')
                            ->multiple()
                            ->options(fn () => $this->mediaOptions())
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ]),
                Section::make('7. Blog section')
                    ->description('Blog cards come from published posts in Content → Blog Posts.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('home_blog_eyebrow')->label('Small heading'),
                        TextInput::make('home_blog_title')->label('Section heading'),
                        Repeater::make('home_blog_paragraphs')
                            ->label('Introduction paragraphs')
                            ->schema([Textarea::make('paragraph')->label('Paragraph')->rows(3)->required()])
                            ->addActionLabel('Add paragraph')
                            ->defaultItems(0)
                            ->columnSpanFull(),
                        TextInput::make('home_blog_cta')->label('Button text'),
                        TextInput::make('home_blog_cta_url')->label('Button URL'),
                        Textarea::make('home_empty_blog')->label('Message when there are no published posts')->rows(2)->columnSpanFull(),
                    ]),
                Section::make('8. Header, contact and navigation')
                    ->schema([
                        $this->mediaSelect('site_logo_media_id', 'Site logo'),
                        TextInput::make('contact_email')->label('Contact email')->email(),
                        TextInput::make('address')->label('Address'),
                        TextInput::make('site_website_display')->label('Website text'),
                        TextInput::make('site_website_url')->label('Website URL')->url(),
                        TextInput::make('social_facebook')->label('Facebook URL')->url(),
                        TextInput::make('social_twitter')->label('Twitter URL')->url(),
                        TextInput::make('social_instagram')->label('Instagram URL')->url(),
                        TextInput::make('social_youtube')->label('YouTube URL')->url(),
                        Repeater::make('home_nav_items')
                            ->label('Navigation links')
                            ->schema([
                                TextInput::make('label')->label('Link text')->required(),
                                TextInput::make('url')->label('URL')->required(),
                                \Filament\Forms\Components\Toggle::make('is_cta')->label('Highlight as button'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Add navigation link')
                            ->defaultItems(0),
                    ]),
                Section::make('9. Footer')
                    ->columns(2)
                    ->schema([
                        TextInput::make('footer_heading')->label('Footer about heading'),
                        TextInput::make('footer_links_heading')->label('Footer links heading'),
                        TextInput::make('footer_contact_heading')->label('Footer contact heading'),
                        TextInput::make('footer_website_label')->label('Website label'),
                        TextInput::make('footer_email_label')->label('Email label'),
                        TextInput::make('footer_address_label')->label('Address label'),
                        TextInput::make('footer_copyright_text')->label('Copyright line'),
                        TextInput::make('footer_charity_label')->label('Charity number label'),
                        Textarea::make('footer_text')->label('Footer text')->rows(3)->columnSpanFull(),
                        Repeater::make('footer_links')
                            ->label('Footer links')
                            ->schema([
                                TextInput::make('label')->label('Link text')->required(),
                                TextInput::make('url')->label('URL')->required(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add footer link')
                            ->defaultItems(0)
                            ->columnSpanFull(),
                        TextInput::make('footer_credit')->label('Footer credit text')->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $homePage = ContentPage::query()->firstOrCreate(
            ['slug' => 'home'],
            ['title' => 'Home', 'content' => '', 'status' => 'published', 'published_at' => now()],
        );
        $homePage->forceFill(['og_image_media_id' => $data['hero_image_id'] ?: null])->save();

        $campaign = ContentPage::query()->firstOrCreate(
            ['slug' => 'pink-chai-day-2025'],
            ['title' => 'Pink Chai Day 2025', 'content' => '', 'excerpt' => '', 'status' => 'draft'],
        );
        $campaign->forceFill([
            'title' => $data['campaign_title'],
            'excerpt' => $data['campaign_description'] ?? '',
            'content' => $data['campaign_description'] ?? '',
            'og_image_media_id' => $data['campaign_image_id'] ?: null,
        ])->save();

        $homePage->mediaAttachments()->sync(
            collect($data['gallery_media_ids'] ?? [])->mapWithKeys(fn ($id, $index) => [$id => ['role' => 'gallery', 'sort_order' => $index]])->all(),
        );

        $values = [
            'home_hero_eyebrow' => $data['home_hero_eyebrow'] ?? '',
            'home_hero_title' => $data['home_hero_title'] ?? '',
            'home_hero_description' => $data['home_hero_description'] ?? '',
            'home_hero_cta' => $data['home_hero_cta'] ?? '',
            'home_hero_cta_url' => $data['home_hero_cta_url'] ?? '',
            'home_actions_aria' => $data['home_actions_aria'] ?? '',
            'home_actions' => $this->json($data['home_actions'] ?? []),
            'home_projects_eyebrow' => $data['home_projects_eyebrow'] ?? '',
            'home_projects_title' => $data['home_projects_title'] ?? '',
            'home_projects_description' => $data['home_projects_description'] ?? '',
            'home_empty_projects' => $data['home_empty_projects'] ?? '',
            'home_main_cta' => $data['home_main_cta'] ?? '',
            'home_main_cta_label' => $data['home_main_cta_label'] ?? '',
            'home_main_cta_url' => $data['home_main_cta_url'] ?? '',
            'home_main_cta_image_id' => $data['home_main_cta_image_id'] ?? '',
            'home_campaign_eyebrow' => $data['home_campaign_eyebrow'] ?? '',
            'home_campaign_cta' => $data['campaign_cta'] ?? '',
            'home_campaign_cta_url' => $data['campaign_cta_url'] ?? '',
            'home_gallery_eyebrow' => $data['home_gallery_eyebrow'] ?? '',
            'home_gallery_title' => $data['home_gallery_title'] ?? '',
            'home_blog_eyebrow' => $data['home_blog_eyebrow'] ?? '',
            'home_blog_title' => $data['home_blog_title'] ?? '',
            'home_blog_paragraphs' => $this->json(collect($data['home_blog_paragraphs'] ?? [])->pluck('paragraph')->all()),
            'home_blog_cta' => $data['home_blog_cta'] ?? '',
            'home_blog_cta_url' => $data['home_blog_cta_url'] ?? '',
            'home_empty_blog' => $data['home_empty_blog'] ?? '',
            'home_nav_items' => $this->json($data['home_nav_items'] ?? []),
            'site_logo_media_id' => $data['site_logo_media_id'] ?? '',
            'site_website_display' => $data['site_website_display'] ?? '',
            'site_website_url' => $data['site_website_url'] ?? '',
            'contact_email' => $data['contact_email'] ?? '',
            'address' => $data['address'] ?? '',
            'social_facebook' => $data['social_facebook'] ?? '',
            'social_twitter' => $data['social_twitter'] ?? '',
            'social_instagram' => $data['social_instagram'] ?? '',
            'social_youtube' => $data['social_youtube'] ?? '',
            'footer_heading' => $data['footer_heading'] ?? '',
            'footer_links_heading' => $data['footer_links_heading'] ?? '',
            'footer_contact_heading' => $data['footer_contact_heading'] ?? '',
            'footer_website_label' => $data['footer_website_label'] ?? '',
            'footer_email_label' => $data['footer_email_label'] ?? '',
            'footer_address_label' => $data['footer_address_label'] ?? '',
            'footer_links' => $this->json($data['footer_links'] ?? []),
            'footer_text' => $data['footer_text'] ?? '',
            'footer_credit' => $data['footer_credit'] ?? '',
            'footer_copyright_text' => $data['footer_copyright_text'] ?? '',
            'footer_charity_label' => $data['footer_charity_label'] ?? '',
        ];

        foreach ($values as $key => $value) {
            $group = match (true) {
                str_starts_with($key, 'footer_') => 'footer',
                in_array($key, ['contact_email', 'address'], true) => 'contact',
                str_starts_with($key, 'social_') => 'social',
                $key === 'site_logo_media_id' => 'branding',
                str_starts_with($key, 'site_website_') => 'general',
                default => 'homepage',
            };

            WebsiteSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group],
            );
        }

        Notification::make()->title('Homepage updated')->success()->send();
    }

    private function mediaSelect(string $name, string $label): Select
    {
        return Select::make($name)
            ->label($label)
            ->options(fn () => $this->mediaOptions())
            ->searchable()
            ->preload()
            ->nullable()
            ->helperText('Choose an image already uploaded in Media.');
    }

    private function mediaOptions(): array
    {
        return Media::query()
            ->orderBy('original_filename')
            ->get(['id', 'original_filename', 'alt_text'])
            ->mapWithKeys(fn (Media $media) => [$media->id => $media->original_filename.($media->alt_text ? ' — '.$media->alt_text : '')])
            ->all();
    }

    private function decode(?string $value): array
    {
        $decoded = json_decode($value ?: '[]', true);

        return is_array($decoded) ? $decoded : [];
    }

    private function json(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
