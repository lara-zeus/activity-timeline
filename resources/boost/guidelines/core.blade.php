# Lara Zeus Activity Timeline (`lara-zeus/activity-timeline`)

FilamentPHP v5 Infolist timeline components and optional `spatie/laravel-activitylog` resource page for rendering chronological activity feeds.

## Key Classes
- **Timeline Container**: `LaraZeus\ActivityTimeline\Components\ActivitySection` (extends `Filament\Infolists\Components\Entry`, uses `HasEmptyState`)
- **Item Entries**: `ActivityTitle`, `ActivityDescription`, `ActivityDate`, `ActivityIcon` under `LaraZeus\ActivityTimeline\Components\*`
- **Enum**: `LaraZeus\ActivityTimeline\Enums\IconAnimation` (`Spin`, `Ping`, `Pulse`, `Bounce`)
- **Resource Page**: `LaraZeus\ActivityTimeline\Pages\ActivityTimelinePage` (uses `HasSetting` and `InteractsWithRecord`, integrates with `spatie/laravel-activitylog`)
- **Service Provider**: `LaraZeus\ActivityTimeline\ActivityTimelineServiceProvider` (`$name = 'activity-timeline'`)

## Rules & Anti-Patterns
- **No Filament Panel Plugin**: Do not register a panel plugin; use `ActivitySection` inside Infolist/Schema components or extend `ActivityTimelinePage` in a Filament Resource.
- **PHP 8.4+ required**: `composer.json` requires `"php": "^8.4"` and `"filament/filament": "^5.0"`.
- **Wrap entries in `ActivitySection`**: `ActivityTitle`, `ActivityDescription`, `ActivityDate`, and `ActivityIcon` must be placed inside `ActivitySection::make('activities')->schema([...])`, where `'activities'` matches the array/collection key on `->state([...])` or a relationship/attribute on `->record($model)`.
- **XSS caution with `->allowHtml()`**: Only call `->allowHtml()` on `ActivityTitle` or `ActivityDescription` when rendering trusted/sanitized HTML.
- **`ActivityTimelinePage` requires `spatie/laravel-activitylog`**: `HasSetting::getActivites()` resolves `config('activitylog.activity_model')`; do not extend `ActivityTimelinePage` unless `spatie/laravel-activitylog` is installed and configured.
