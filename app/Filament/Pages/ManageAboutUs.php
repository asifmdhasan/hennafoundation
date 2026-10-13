<?php

namespace App\Filament\Pages;

use App\Forms\Components\MediaPickerField;
use App\Models\Media;
use App\Models\Page as ContentPage;
use App\Models\WebsiteSetting;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ManageAboutUs extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'About Us Editor';

    protected static ?string $slug = 'about-us-editor';

    protected static ?string $title = 'About Us Editor';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.manage-about-us';

    public ?array $data = [];

    public function mount(): void
    {
        $page = ContentPage::query()->firstOrCreate(
            ['slug' => 'about-us'],
            ['title' => 'About Us', 'content' => '', 'status' => 'draft'],
        );

        $settings = WebsiteSetting::query()
            ->where('group', 'about-us')
            ->pluck('value', 'key');

        $this->form->fill([
            'title' => $page->title,
            'status' => $page->status,
            'published_at' => $page->published_at,
            'hero_breadcrumb_title' => $settings->get('about_hero_breadcrumb_title'),
            'show_hero' => $this->boolean($settings->get('about_show_hero'), true),
            'hero_overlay' => $this->boolean($settings->get('about_hero_overlay'), true),
            'hero_text' => $settings->get('about_hero_text'),
            'hero_image_media_id' => $page->og_image_media_id,
            'show_inspiration' => $this->boolean($settings->get('about_show_inspiration'), true),
            'inspiration_eyebrow' => $settings->get('about_inspiration_eyebrow'),
            'inspiration_heading' => $settings->get('about_inspiration_heading'),
            'inspiration_paragraphs' => $this->paragraphItems($settings->get('about_inspiration_paragraphs')),
            'inspiration_image_ids' => $page->mediaAttachments()
                ->wherePivot('role', 'about-inspiration')
                ->orderByPivot('sort_order')
                ->pluck('media.id')
                ->all(),
            'inspiration_button_visible' => $this->boolean($settings->get('about_inspiration_button_visible'), true),
            'inspiration_button_label' => $settings->get('about_inspiration_button_label'),
            'inspiration_button_url' => $settings->get('about_inspiration_button_url'),
            'show_patron' => $this->boolean($settings->get('about_show_patron'), true),
            'patron_eyebrow' => $settings->get('about_patron_eyebrow'),
            'patron_name' => $settings->get('about_patron_name'),
            'patron_designation' => $settings->get('about_patron_designation'),
            'patron_biography' => $this->paragraphItems($settings->get('about_patron_biography')),
            'patron_image_media_id' => $settings->get('about_patron_image_media_id'),
            'show_trustees' => $this->boolean($settings->get('about_show_trustees'), true),
            'trustees_eyebrow' => $settings->get('about_trustees_eyebrow'),
            'trustees_heading' => $settings->get('about_trustees_heading'),
            'show_goals' => $this->boolean($settings->get('about_show_goals'), true),
            'goals_eyebrow' => $settings->get('about_goals_eyebrow'),
            'goals_heading' => $settings->get('about_goals_heading'),
            'goals_intro' => $settings->get('about_goals_intro'),
            'goals' => $this->decode($settings->get('about_goals')),
            'show_heart' => $this->boolean($settings->get('about_show_heart'), true),
            'heart_eyebrow' => $settings->get('about_heart_eyebrow'),
            'heart_heading' => $settings->get('about_heart_heading'),
            'heart_paragraphs' => $this->paragraphItems($settings->get('about_heart_paragraphs')),
            'show_plan' => $this->boolean($settings->get('about_show_plan'), true),
            'plan_eyebrow' => $settings->get('about_plan_eyebrow'),
            'plan_heading' => $settings->get('about_plan_heading'),
            'plan_intro' => $settings->get('about_plan_intro'),
            'plan_items' => $this->decode($settings->get('about_plan_items')),
            'show_charity_purpose' => $this->boolean($settings->get('about_show_charity_purpose'), true),
            'charity_heading' => $settings->get('about_charity_heading'),
            'charity_content' => $settings->get('about_charity_content'),
            'charity_number' => $settings->get('about_charity_number'),
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'canonical_url' => $page->canonical_url,
            'og_title' => $page->og_title,
            'og_description' => $page->og_description,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('1. Page hero and publishing')
                    ->description('Change the title and banner. Select images that have already been uploaded in Content → Media.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')->label('Page title')->required()->maxLength(255),
                        TextInput::make('hero_breadcrumb_title')->label('Breadcrumb title')->maxLength(255),
                        Toggle::make('show_hero')->label('Show hero/banner')->default(true),
                        Toggle::make('hero_overlay')->label('Show image overlay')->default(true),
                        $this->mediaSelect('hero_image_media_id', 'Hero/banner image'),
                        Textarea::make('hero_text')->label('Optional hero text')->rows(2)->columnSpanFull(),
                        Select::make('status')->label('Page status')->options(['draft' => 'Draft', 'published' => 'Published'])->in(['draft', 'published'])->required(),
                        DateTimePicker::make('published_at')->label('Publish date/time')->nullable(),
                    ]),
                Section::make('2. Inspiration and introduction')
                    ->columns(2)
                    ->schema([
                        Toggle::make('show_inspiration')->label('Show this section')->default(true),
                        TextInput::make('inspiration_eyebrow')->label('Small heading')->maxLength(255),
                        TextInput::make('inspiration_heading')->label('Section heading')->required(fn (Get $get): bool => (bool) $get('show_inspiration'))->maxLength(255)->columnSpanFull(),
                        Repeater::make('inspiration_paragraphs')
                            ->label('Content paragraphs')
                            ->schema([Textarea::make('paragraph')->label('Paragraph')->rows(4)->required()])
                            ->addActionLabel('Add paragraph')
                            ->defaultItems(0)
                            ->columnSpanFull(),
                        $this->mediaPicker('inspiration_image_ids', 'Images (shown in the current image layout)', true)
                            ->rules(['array'])
                            ->helperText('Drag selected images to change their display order. Manage image descriptions in Media.')
                            ->columnSpanFull(),
                        Toggle::make('inspiration_button_visible')->label('Show Donate button')->default(true),
                        TextInput::make('inspiration_button_label')->label('Button text')->maxLength(100),
                        TextInput::make('inspiration_button_url')->label('Button URL')->rules(['nullable', 'regex:/^(?:\/(?!\/)[^\s]*|https?:\/\/[^\s]+)$/i'])->maxLength(2048),
                    ]),
                Section::make('3. Patron')
                    ->columns(2)
                    ->schema([
                        Toggle::make('show_patron')->label('Show this section')->default(true),
                        TextInput::make('patron_eyebrow')->label('Section label')->maxLength(255),
                        TextInput::make('patron_name')->label('Name')->maxLength(255),
                        TextInput::make('patron_designation')->label('Designation')->maxLength(255),
                        $this->mediaSelect('patron_image_media_id', 'Patron photo'),
                        Repeater::make('patron_biography')
                            ->label('Biography paragraphs')
                            ->schema([Textarea::make('paragraph')->label('Paragraph')->rows(4)->required()])
                            ->addActionLabel('Add biography paragraph')
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),
                Section::make('4. Board of trustees')
                    ->schema([
                        Toggle::make('show_trustees')->label('Show this section')->default(true),
                        TextInput::make('trustees_eyebrow')->label('Small heading')->maxLength(255),
                        TextInput::make('trustees_heading')->label('Section heading')->required(fn (Get $get): bool => (bool) $get('show_trustees'))->maxLength(255),
                        \Filament\Forms\Components\Placeholder::make('trustees_admin_note')
                            ->label('Trustee profiles')
                            ->content('Create and manage trustee profiles under Content → Trustees. Set each profile to Draft or Published there; published profiles appear in this section.')
                            ->columnSpanFull(),
                    ]),
                Section::make('5. Our goals')
                    ->schema([
                        Toggle::make('show_goals')->label('Show this section')->default(true),
                        TextInput::make('goals_eyebrow')->label('Small heading')->maxLength(255),
                        TextInput::make('goals_heading')->label('Section heading')->required(fn (Get $get): bool => (bool) $get('show_goals'))->maxLength(255),
                        Textarea::make('goals_intro')->label('Introduction')->rows(3)->columnSpanFull(),
                        Repeater::make('goals')
                            ->label('Goal cards')
                            ->schema([
                                $this->mediaSelect('icon_media_id', 'Icon image'),
                                TextInput::make('title')->label('Goal title')->required()->maxLength(255),
                                Textarea::make('description')->label('Description')->rows(3)->required(),
                                Toggle::make('is_visible')->label('Visible on public page')->default(true),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add goal')
                            ->defaultItems(0)
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),
                Section::make('6. The Heart of Henna Foundation')
                    ->schema([
                        Toggle::make('show_heart')->label('Show this section')->default(true),
                        TextInput::make('heart_eyebrow')->label('Small heading')->maxLength(255),
                        TextInput::make('heart_heading')->label('Section heading')->required(fn (Get $get): bool => (bool) $get('show_heart'))->maxLength(255),
                        Repeater::make('heart_paragraphs')
                            ->label('Content paragraphs')
                            ->schema([Textarea::make('paragraph')->label('Paragraph')->rows(4)->required()])
                            ->addActionLabel('Add paragraph')
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),
                Section::make('7. Our plan / next 1–3 years')
                    ->schema([
                        Toggle::make('show_plan')->label('Show this section')->default(true),
                        TextInput::make('plan_eyebrow')->label('Small heading')->maxLength(255),
                        TextInput::make('plan_heading')->label('Section heading')->required(fn (Get $get): bool => (bool) $get('show_plan'))->maxLength(255),
                        Textarea::make('plan_intro')->label('Introduction')->rows(3)->columnSpanFull(),
                        Repeater::make('plan_items')
                            ->label('Plan items')
                            ->schema([
                                TextInput::make('title')->label('Item title/content')->required()->maxLength(1000),
                                Textarea::make('description')->label('Optional description')->rows(3),
                                Toggle::make('is_visible')->label('Visible on public page')->default(true),
                            ])
                            ->addActionLabel('Add plan item')
                            ->defaultItems(0)
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),
                Section::make('8. Charity purpose')
                    ->columns(2)
                    ->schema([
                        Toggle::make('show_charity_purpose')->label('Show this section')->default(true),
                        TextInput::make('charity_heading')->label('Optional heading')->maxLength(255),
                        Textarea::make('charity_content')->label('Public benefit statement')->rows(4)->columnSpanFull(),
                        TextInput::make('charity_number')->label('Registered charity number')->maxLength(100),
                    ]),
                Section::make('9. Search and social sharing')
                    ->description('These fields use the same Page SEO fields as the rest of the CMS. Leave optional fields blank to use the standard fallback.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('meta_title')->label('Meta title')->maxLength(255),
                        TextInput::make('canonical_url')->label('Canonical URL')->url()->nullable()->maxLength(2048),
                        Textarea::make('meta_description')->label('Meta description')->rows(3)->columnSpanFull(),
                        TextInput::make('og_title')->label('Open Graph title')->maxLength(255),
                        Textarea::make('og_description')->label('Open Graph description')->rows(3),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Validator::make($data, [
            'inspiration_image_ids' => ['nullable', 'array'],
            'inspiration_image_ids.*' => ['integer', 'exists:media,id'],
        ])->validate();

        DB::transaction(function () use ($data): void {
            $page = ContentPage::query()->where('slug', 'about-us')->firstOrFail();
            $heroImageId = $data['hero_image_media_id'] ?: null;

            $page->forceFill([
                'title' => $data['title'],
                'status' => $data['status'],
                'published_at' => $data['published_at'] ?: null,
                'og_image_media_id' => $heroImageId,
                'meta_title' => $data['meta_title'] ?: null,
                'meta_description' => $data['meta_description'] ?: null,
                'canonical_url' => $data['canonical_url'] ?: null,
                'og_title' => $data['og_title'] ?: null,
                'og_description' => $data['og_description'] ?: null,
            ])->save();

            $settings = [
                'about_hero_breadcrumb_title' => $data['hero_breadcrumb_title'] ?? '',
                'about_show_hero' => $this->asBoolean($data['show_hero'] ?? false),
                'about_hero_overlay' => $this->asBoolean($data['hero_overlay'] ?? false),
                'about_hero_text' => $data['hero_text'] ?? '',
                'about_show_inspiration' => $this->asBoolean($data['show_inspiration'] ?? false),
                'about_inspiration_eyebrow' => $data['inspiration_eyebrow'] ?? '',
                'about_inspiration_heading' => $data['inspiration_heading'] ?? '',
                'about_inspiration_paragraphs' => $this->json($this->paragraphValues($data['inspiration_paragraphs'] ?? [])),
                'about_inspiration_button_visible' => $this->asBoolean($data['inspiration_button_visible'] ?? false),
                'about_inspiration_button_label' => $data['inspiration_button_label'] ?? '',
                'about_inspiration_button_url' => $data['inspiration_button_url'] ?? '',
                'about_show_patron' => $this->asBoolean($data['show_patron'] ?? false),
                'about_patron_eyebrow' => $data['patron_eyebrow'] ?? '',
                'about_patron_name' => $data['patron_name'] ?? '',
                'about_patron_designation' => $data['patron_designation'] ?? '',
                'about_patron_biography' => $this->json($this->paragraphValues($data['patron_biography'] ?? [])),
                'about_patron_image_media_id' => $data['patron_image_media_id'] ?? '',
                'about_show_trustees' => $this->asBoolean($data['show_trustees'] ?? false),
                'about_trustees_eyebrow' => $data['trustees_eyebrow'] ?? '',
                'about_trustees_heading' => $data['trustees_heading'] ?? '',
                'about_show_goals' => $this->asBoolean($data['show_goals'] ?? false),
                'about_goals_eyebrow' => $data['goals_eyebrow'] ?? '',
                'about_goals_heading' => $data['goals_heading'] ?? '',
                'about_goals_intro' => $data['goals_intro'] ?? '',
                'about_goals' => $this->json($this->orderedItems($data['goals'] ?? [])),
                'about_show_heart' => $this->asBoolean($data['show_heart'] ?? false),
                'about_heart_eyebrow' => $data['heart_eyebrow'] ?? '',
                'about_heart_heading' => $data['heart_heading'] ?? '',
                'about_heart_paragraphs' => $this->json($this->paragraphValues($data['heart_paragraphs'] ?? [])),
                'about_show_plan' => $this->asBoolean($data['show_plan'] ?? false),
                'about_plan_eyebrow' => $data['plan_eyebrow'] ?? '',
                'about_plan_heading' => $data['plan_heading'] ?? '',
                'about_plan_intro' => $data['plan_intro'] ?? '',
                'about_plan_items' => $this->json($this->orderedItems($data['plan_items'] ?? [])),
                'about_show_charity_purpose' => $this->asBoolean($data['show_charity_purpose'] ?? false),
                'about_charity_heading' => $data['charity_heading'] ?? '',
                'about_charity_content' => $data['charity_content'] ?? '',
                'about_charity_number' => $data['charity_number'] ?? '',
            ];

            foreach ($settings as $key => $value) {
                WebsiteSetting::query()->updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => 'about-us'],
                );
            }

            $this->syncInspirationImages($page, $data['inspiration_image_ids'] ?? []);
        });

        Notification::make()->title('About Us page updated')->success()->send();
    }

    #[On('media-picker-selected')]
    public function applyMediaPickerSelection(string $statePath, array $mediaIds, bool $multiple = false): void
    {
        if (! str_starts_with($statePath, 'data.')) {
            return;
        }

        $relativePath = substr($statePath, 5);
        $fieldName = str($relativePath)->afterLast('.')->toString();
        $allowedFields = ['hero_image_media_id', 'inspiration_image_ids', 'patron_image_media_id', 'image_media_id', 'icon_media_id'];
        if (! in_array($fieldName, $allowedFields, true) || ($multiple !== ($fieldName === 'inspiration_image_ids'))) {
            return;
        }

        $validIds = Media::query()->whereIn('id', $mediaIds)->pluck('id')->map(fn ($id): int => (int) $id)->all();
        if (count($validIds) !== count(array_unique($mediaIds))) {
            return;
        }

        $state = $this->data ?? [];
        data_set($state, $relativePath, $multiple ? $validIds : ($validIds[0] ?? null));
        $this->data = $state;
    }

    private function mediaSelect(string $name, string $label): MediaPickerField
    {
        return $this->mediaPicker($name, $label);
    }

    private function mediaPicker(string $name, string $label, bool $multiple = false): MediaPickerField
    {
        return MediaPickerField::make($name)
            ->label($label)
            ->multiple($multiple)
            ->nullable()
            ->rules($multiple ? ['nullable', 'array'] : ['nullable', 'integer', 'exists:media,id']);
    }

    private function decode(?string $value): array
    {
        $decoded = json_decode($value ?: '[]', true);

        return is_array($decoded) ? $decoded : [];
    }

    private function paragraphItems(?string $value): array
    {
        return collect($this->decode($value))
            ->filter(fn ($paragraph): bool => is_string($paragraph) && filled($paragraph))
            ->map(fn (string $paragraph): array => ['paragraph' => $paragraph])
            ->values()
            ->all();
    }

    private function paragraphValues(array $items): array
    {
        return collect($items)
            ->pluck('paragraph')
            ->filter(fn ($paragraph): bool => is_string($paragraph) && filled($paragraph))
            ->values()
            ->all();
    }

    private function orderedItems(array $items): array
    {
        return collect($items)
            ->values()
            ->map(function (array $item, int $sortOrder): array {
                $item['sort_order'] = $sortOrder;

                return $item;
            })
            ->all();
    }

    private function syncInspirationImages(ContentPage $page, array $mediaIds): void
    {
        $relation = $page->mediaAttachments();
        $pivotQuery = DB::table('media_attachments')
            ->where('mediable_type', $page->getMorphClass())
            ->where('mediable_id', $page->getKey())
            ->where('role', 'about-inspiration');

        $pivotQuery->delete();

        foreach (array_values(array_unique($mediaIds)) as $sortOrder => $mediaId) {
            $relation->attach((int) $mediaId, ['role' => 'about-inspiration', 'sort_order' => $sortOrder]);
        }
    }

    private function boolean(?string $value, bool $default = false): bool
    {
        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function asBoolean(bool|string|int|null $value): string
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
    }

    private function json(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
