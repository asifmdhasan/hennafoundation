<?php

namespace App\Filament\Pages;

use App\Forms\Components\MediaPickerField;
use App\Models\Media;
use App\Models\WebsiteSetting;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\On;

class ManageFooter extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|\UnitEnum|null $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Footer';
    protected static ?string $slug = 'footer';
    protected static ?string $title = 'Footer';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.manage-footer';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = WebsiteSetting::query()->pluck('value', 'key');
        $contacts = $this->decode($settings->get('footer_contact_items'));
        if ($contacts === []) {
            $contacts = array_values(array_filter([
                ['type' => 'Website', 'label' => $settings->get('footer_website_label', 'Website'), 'value' => $settings->get('site_website_display', 'www.hennafoundation.org.uk'), 'url' => $settings->get('site_website_url', 'https://hennafoundation.org.uk/'), 'icon_media_id' => ''],
                ['type' => 'Email', 'label' => $settings->get('footer_email_label', 'Email'), 'value' => $settings->get('contact_email', 'hello@hennafoundation.org.uk'), 'url' => filled($settings->get('contact_email')) ? 'mailto:'.$settings->get('contact_email') : '', 'icon_media_id' => ''],
                ['type' => 'Address', 'label' => $settings->get('footer_address_label', 'Address'), 'value' => $settings->get('address', 'Worthing, West Sussex, United Kingdom'), 'url' => '', 'icon_media_id' => ''],
            ], fn ($item) => filled($item['value'])));
        }
        $social = $this->decode($settings->get('footer_social_items'));
        if ($social === []) {
            foreach (['facebook' => 'Facebook', 'twitter' => 'Twitter', 'instagram' => 'Instagram', 'youtube' => 'YouTube'] as $key => $label) {
                if (filled($url = $settings->get('social_'.$key))) {
                    $social[] = ['platform' => $label, 'url' => $url, 'icon_media_id' => ''];
                }
            }
        }

        $description = $settings->get('footer_text');
        if (blank($description)) {
            $existingDescription = $settings->get('purpose_statement')
                ?: 'For the public benefit, the prevention of poverty in Worthing, West Sussex, and Bangladesh by providing or assisting in the provision of education, training, health, and all necessary support designed to enable individuals to generate sustainable income and self-sufficient.';
            $description = '<p>'.e($existingDescription).'</p><p><strong>Registered Charity Number: '.e($settings->get('charity_number', '1203971')).'</strong></p>';
        }
        $legacyCredit = (string) $settings->get('footer_credit', '');
        preg_match('/^Developed by\s+(.+)$/i', $legacyCredit, $creditMatch);

        $this->form->fill([
            'site_logo_media_id' => $settings->get('site_logo_media_id'),
            'footer_logo_media_id' => $settings->get('footer_logo_media_id', $settings->get('site_logo_media_id')),
            'favicon_media_id' => $settings->get('favicon_media_id'),
            'home_nav_items' => $this->decode($settings->get('home_nav_items')),
            'footer_heading' => $settings->get('footer_heading', $settings->get('site_name', 'Henna Foundation')),
            'footer_links_heading' => $settings->get('footer_links_heading', 'Quick Links'),
            'footer_contact_heading' => $settings->get('footer_contact_heading', 'Contact Us'),
            'footer_links' => $this->decode($settings->get('footer_links')),
            'charity_number' => $settings->get('charity_number'),
            'footer_text' => $description,
            'footer_copyright_text' => $settings->get('footer_copyright_text') ?: 'Copyright '.now()->year.' © All Rights Reserved by Henna Foundation',
            'footer_charity_label' => $settings->get('footer_charity_label', 'Registered Charity Number'),
            'contact_items' => $contacts,
            'social_items' => $social,
            'footer_credit' => $settings->get('footer_credit', ''),
            'developer_text' => $settings->get('footer_developer_text', 'Developed by'),
            'developer_name' => $settings->get('footer_developer_name', $creditMatch[1] ?? 'Printagraphy'),
            'developer_url' => $settings->get('footer_developer_url', 'https://printagraphy.com/'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Branding and description')->columns(2)->schema([
                $this->mediaPicker('footer_logo_media_id', 'Footer logo'),
                $this->mediaPicker('site_logo_media_id', 'Header logo'),
                $this->mediaPicker('favicon_media_id', 'Favicon'),
                TextInput::make('footer_heading')->label('Footer brand name'),
                RichEditor::make('footer_text')->label('Footer description')->columnSpanFull(),
            ]),
            Section::make('Quick links')->columns(2)->schema([
                TextInput::make('footer_links_heading')->label('Heading'),
                Repeater::make('footer_links')->label('Footer links')->schema([
                    TextInput::make('label')->label('Link text')->required(),
                    TextInput::make('url')->label('URL')->required(),
                ])->columns(2)->defaultItems(0)->reorderable()->addActionLabel('Add footer link')->columnSpanFull(),
            ]),
            Section::make('Contact us')->columns(2)->schema([
                TextInput::make('footer_contact_heading')->label('Heading'),
                Repeater::make('contact_items')->label('Contact items')->schema([
                    Select::make('type')->options(['Website' => 'Website', 'Email' => 'Email', 'Telephone' => 'Telephone', 'Address' => 'Address', 'Other' => 'Other'])->native(false),
                    TextInput::make('label')->label('Label')->required(),
                    TextInput::make('value')->label('Displayed value')->required(),
                    TextInput::make('url')->label('Link URL or contact value'),
                    $this->mediaPicker('icon_media_id', 'Icon image'),
                ])->columns(2)->defaultItems(0)->reorderable()->addActionLabel('Add contact item')->columnSpanFull(),
            ]),
            Section::make('Social media')->schema([
                Repeater::make('social_items')->label('Social links')->schema([
                    TextInput::make('platform')->label('Platform or label')->required(),
                    TextInput::make('url')->label('Profile URL')->url()->required(),
                    $this->mediaPicker('icon_media_id', 'Icon image'),
                ])->columns(2)->defaultItems(0)->reorderable()->addActionLabel('Add social link'),
            ]),
            Section::make('Footer credits')->columns(2)->schema([
                TextInput::make('footer_credit')->label('Footer Credit Text'),
                TextInput::make('footer_copyright_text')->label('Copyright message'),
                TextInput::make('developer_text')->label('Developer text'),
                TextInput::make('developer_name')->label('Developer/company name'),
                TextInput::make('developer_url')->label('Developer URL')->url(),
            ]),
            Section::make('Header navigation')->schema([
                Repeater::make('home_nav_items')->label('Navigation links')->schema([
                    TextInput::make('label')->label('Link text')->required(),
                    TextInput::make('url')->label('URL')->required(),
                    \Filament\Forms\Components\Toggle::make('is_cta')->label('Highlight as button'),
                ])->columns(3)->defaultItems(0)->reorderable()->addActionLabel('Add navigation link'),
                TextInput::make('charity_number')->label('Charity registration number'),
                TextInput::make('footer_charity_label')->label('Charity number label'),
            ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach (['site_logo_media_id', 'footer_logo_media_id', 'favicon_media_id'] as $field) {
            $data[$field] = filled($data[$field] ?? null) ? $data[$field] : null;
        }
        foreach (['contact_items', 'social_items'] as $list) {
            $data[$list] = collect($data[$list] ?? [])->map(function (array $item): array {
                $item['icon_media_id'] = filled($item['icon_media_id'] ?? null) ? $item['icon_media_id'] : null;
                return $item;
            })->all();
        }
        Validator::make($data, [
            'site_logo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'footer_logo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'favicon_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'contact_items' => ['array'], 'contact_items.*.icon_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'social_items' => ['array'], 'social_items.*.icon_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'footer_links' => ['array'], 'home_nav_items' => ['array'],
        ])->validate();

        $contacts = array_values($data['contact_items'] ?? []);
        $socials = array_values($data['social_items'] ?? []);
        $values = [
            'site_logo_media_id' => $data['site_logo_media_id'] ?? '',
            'footer_logo_media_id' => $data['footer_logo_media_id'] ?? '',
            'favicon_media_id' => $data['favicon_media_id'] ?? '',
            'home_nav_items' => $this->json($data['home_nav_items'] ?? []),
            'site_website_display' => $data['site_website_display'] ?? '',
            'site_website_url' => $data['site_website_url'] ?? '',
            'charity_number' => $data['charity_number'] ?? '',
            'footer_heading' => $data['footer_heading'] ?? '',
            'footer_links_heading' => $data['footer_links_heading'] ?? '',
            'footer_contact_heading' => $data['footer_contact_heading'] ?? '',
            'footer_links' => $this->json($data['footer_links'] ?? []),
            'footer_text' => $data['footer_text'] ?? '',
            'footer_credit' => $data['footer_credit'] ?? '',
            'footer_copyright_text' => $data['footer_copyright_text'] ?? '',
            'footer_charity_label' => $data['footer_charity_label'] ?? '',
            'footer_contact_items' => $this->json($contacts),
            'footer_social_items' => $this->json($socials),
            'footer_developer_text' => $data['developer_text'] ?? '',
            'footer_developer_name' => $data['developer_name'] ?? '',
            'footer_developer_url' => $data['developer_url'] ?? '',
        ];
        foreach (['facebook' => 'Facebook', 'twitter' => 'Twitter', 'instagram' => 'Instagram', 'youtube' => 'YouTube'] as $key => $platform) {
            $match = collect($socials)->first(fn ($item) => strtolower($item['platform'] ?? '') === strtolower($platform));
            $values['social_'.$key] = $match['url'] ?? '';
        }
        foreach ($contacts as $item) {
            $type = strtolower($item['type'] ?? '');
            if ($type === 'website') { $values['footer_website_label'] = $item['label'] ?? ''; $values['site_website_display'] = $item['value'] ?? ''; $values['site_website_url'] = $item['url'] ?? ''; }
            if ($type === 'email') { $values['footer_email_label'] = $item['label'] ?? ''; $values['contact_email'] = $item['value'] ?? ''; }
            if ($type === 'address') { $values['footer_address_label'] = $item['label'] ?? ''; $values['address'] = $item['value'] ?? ''; }
        }
        foreach ($values as $key => $value) {
            $group = match (true) {
                str_starts_with($key, 'footer_') => 'footer',
                in_array($key, ['contact_email', 'address'], true) => 'contact',
                str_starts_with($key, 'social_') => 'social',
                in_array($key, ['site_logo_media_id', 'footer_logo_media_id', 'favicon_media_id'], true) => 'branding',
                in_array($key, ['site_website_display', 'site_website_url', 'charity_number'], true) => 'general',
                default => 'homepage',
            };
            WebsiteSetting::query()->updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        }
        Notification::make()->title('Footer settings updated')->success()->send();
    }

    #[On('media-picker-selected')]
    public function applyMediaPickerSelection(string $statePath, array $mediaIds, bool $multiple = false): void
    {
        if (! str_starts_with($statePath, 'data.')) { return; }
        $field = str($statePath)->afterLast('.')->toString();
        if (! in_array($field, ['site_logo_media_id', 'footer_logo_media_id', 'favicon_media_id', 'icon_media_id'], true) || $multiple) { return; }
        $validIds = Media::query()->whereIn('id', $mediaIds)->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (count($validIds) !== count(array_unique($mediaIds))) { return; }
        $state = $this->data ?? [];
        data_set($state, substr($statePath, 5), $validIds[0] ?? null);
        $this->data = $state;
    }

    private function mediaPicker(string $name, string $label): MediaPickerField
    {
        return MediaPickerField::make($name)->label($label)->nullable()->rules(['nullable', 'integer', 'exists:media,id']);
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
