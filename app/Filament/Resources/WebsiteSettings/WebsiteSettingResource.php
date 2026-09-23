<?php

namespace App\Filament\Resources\WebsiteSettings;

use App\Filament\Resources\WebsiteSettings\Pages\ManageWebsiteSettings;
use App\Models\WebsiteSetting;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WebsiteSettingResource extends Resource
{
    protected static ?string $model = WebsiteSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Website Settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Website setting')
                    ->description('Choose a setting, then enter its value. Upload logos and favicons in Media first and enter their public storage path here.')
                    ->schema([
                        Select::make('key')
                            ->label('Setting')
                            ->options([
                                'site_name' => 'Site name',
                                'contact_email' => 'Contact email',
                                'contact_phone' => 'Contact phone',
                                'address' => 'Address',
                                'social_facebook' => 'Facebook link',
                                'social_twitter' => 'Twitter / X link',
                                'social_instagram' => 'Instagram link',
                                'social_youtube' => 'YouTube link',
                                'social_youtube_label' => 'YouTube link label',
                                'default_meta_title' => 'Default SEO title',
                                'default_meta_description' => 'Default SEO description',
                                'purpose_statement' => 'Purpose statement',
                                'charity_number' => 'Charity number',
                                'blog_page_description' => 'Blog page description',
                                'blog_page_empty' => 'Blog page empty message',
                            ])
                            ->searchable()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Select::make('group')
                            ->options([
                                'general' => 'General',
                                'branding' => 'Branding',
                                'contact' => 'Contact',
                                'social' => 'Social links',
                                'footer' => 'Footer',
                                'seo' => 'Default SEO',
                                'homepage' => 'Homepage',
                            ])
                            ->default('general')
                            ->required(),
                        Textarea::make('value')
                            ->label('Value')
                            ->rows(4)
                            ->helperText('Do not enter passwords, API keys, payment credentials, or other secrets.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->label('Setting')->searchable()->sortable(),
                TextColumn::make('group')->badge()->sortable(),
                TextColumn::make('value')->limit(60)->placeholder('Not set'),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->defaultSort('group')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageWebsiteSettings::route('/'),
        ];
    }
}
