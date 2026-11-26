---
name: zeus-activity-timeline
description: Use when building chronological activity timelines in Filament infolists or integrating Spatie Activitylog resource pages with lara-zeus/activity-timeline.
---

# Lara Zeus Activity Timeline

## Overview & Requirements
`lara-zeus/activity-timeline` provides `ActivitySection` and timeline entry components for Filament Infolists, plus `ActivityTimelinePage` for displaying `spatie/laravel-activitylog` records on a Filament Resource.
- **PHP**: `^8.4`
- **Filament**: `^5.0`
- **Dependencies**: `spatie/laravel-package-tools ^1.15` (optional `spatie/laravel-activitylog` for `ActivityTimelinePage`)
- **Package Name**: `lara-zeus/activity-timeline` (Service Provider: `LaraZeus\ActivityTimeline\ActivityTimelineServiceProvider`)

## Installation
```bash
composer require lara-zeus/activity-timeline
```

## Configuration & Migrations
No package config files or migrations are shipped. If you use `ActivityTimelinePage`, install and configure `spatie/laravel-activitylog` so `config('activitylog.activity_model')` is defined.

## Filament Panel Registration
No panel plugin registration is required. Use the Infolist components directly or extend `ActivityTimelinePage` in a Resource page.

## Core Usage & Examples

### Infolist Timeline (`ActivitySection`)
```php
use Filament\Schemas\Schema;
use LaraZeus\ActivityTimeline\Components\ActivityDate;
use LaraZeus\ActivityTimeline\Components\ActivityDescription;
use LaraZeus\ActivityTimeline\Components\ActivityIcon;
use LaraZeus\ActivityTimeline\Components\ActivitySection;
use LaraZeus\ActivityTimeline\Components\ActivityTitle;
use LaraZeus\ActivityTimeline\Enums\IconAnimation;

public function activityTimelineInfolist(Schema $schema): Schema
{
    return $schema
        ->state([
            'activities' => [
                [
                    'title' => 'Published Article',
                    'description' => 'Approved and published.',
                    'status' => 'published',
                    'created_at' => now(),
                ],
            ],
        ])
        ->components([
            ActivitySection::make('activities')
                ->label('My Activities')
                ->description('Recorded activities.')
                ->schema([
                    ActivityTitle::make('title')
                        ->placeholder('No title is set')
                        ->allowHtml(),
                    ActivityDescription::make('description')
                        ->placeholder('No description is set')
                        ->allowHtml(),
                    ActivityDate::make('created_at')
                        ->date('F j, Y', 'UTC')
                        ->placeholder('No date is set.'),
                    ActivityIcon::make('status')
                        ->icon(fn (?string $state): ?string => match ($state) {
                            'published' => 'heroicon-m-rocket-launch',
                            default => null,
                        })
                        ->color(fn (?string $state): ?string => match ($state) {
                            'published' => 'success',
                            default => 'gray',
                        })
                        ->animation(IconAnimation::Pulse),
                ])
                ->showItemsCount(2)
                ->showItemsLabel('View Old')
                ->showItemsIcon('heroicon-m-chevron-down')
                ->showItemsColor('gray')
                ->aside()
                ->compact()
                ->headingVisible()
                ->emptyStateHeading('No activities yet.')
                ->emptyStateDescription('Check back later.')
                ->emptyStateIcon('heroicon-o-bolt-slash'),
        ]);
}
```

### Resource Page with `spatie/laravel-activitylog`
Create a resource page extending `LaraZeus\ActivityTimeline\Pages\ActivityTimelinePage`, register it in the Resource's `getPages()` with a `{record}` route parameter, and optionally override `protected function configuration(): array` (keys: `activity_section`, `activity_title`, `activity_description`, `activity_date`, `activity_icon`):
```php
use App\Filament\Resources\OrderResource;
use LaraZeus\ActivityTimeline\Pages\ActivityTimelinePage;

class ViewOrderActivities extends ActivityTimelinePage
{
    protected static string $resource = OrderResource::class;
}
```

## Common Pitfalls / Anti-Patterns
- **Passing `0` to `showItemsCount(0)`**: `ActivitySection::getShowItemsCount()` treats `0` as `null` (shows all items without truncation).
- **Forgetting `{record}` in resource route**: `ActivityTimelinePage::mount(int|string $record)` uses `InteractsWithRecord::resolveRecord($record)`, so the resource route must include `{record}`.
- **Using `ActivityTimelinePage` without `activitylog`**: `HasSetting::getActivites()` instantiates `config('activitylog.activity_model')` directly and will fail if `spatie/laravel-activitylog` is not configured.
