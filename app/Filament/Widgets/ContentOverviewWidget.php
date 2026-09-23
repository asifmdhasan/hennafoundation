<?php

namespace App\Filament\Widgets;

use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Page;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Published projects', Project::query()->where('status', 'published')->count())
                ->description('Live on the website')
                ->color('success'),
            Stat::make('Published stories', BlogPost::query()->where('status', 'published')->count())
                ->description('Visible in the blog')
                ->color('primary'),
            Stat::make('Pages', Page::query()->count())
                ->description('Draft and published')
                ->color('warning'),
            Stat::make('Media files', Media::query()->count())
                ->description('Available to add to sections')
                ->color('gray'),
        ];
    }
}
