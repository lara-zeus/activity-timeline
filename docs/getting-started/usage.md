---
title: Usage
weight: 3
---

## Usage

This plugin is already accessible within the Infolists builder and now supports both the `->state([])` and `->record()` methods.

```php
public function activityTimelineInfolist(Infolist $infolist): Infolist
{
    return $infolist
        ->state([
            'activities' => [
                    [
                        'title' => "Published Article 🔥 - <span class='italic font-normal dark:text-success-400 text-success-600'>Published with Laravel Filament and Tailwind CSS</span>",
                        'description' => "<span>Approved and published. Here is the <a href='#' class='font-bold hover:underline dark:text-orange-300'>link.</a></span>",
                        'status' => 'published',
                        'created_at' => now()->addDays(8),
                    ],
                    [
                        'title' => 'Reviewing Article - Final Touches',
                        'description' => "<span class='italic'>Reviewing the article and making it ready for publication.</span>",
                        'status' => '',
                        'created_at' => now()->addDays(5),
                    ],
                    [
                        'title' => "Drafting Article - <span class='text-sm italic font-normal text-purple-800 dark:text-purple-300'>Make it ready for review</span>",
                        'description' => 'Drafting the article and making it ready for review.',
                        'status' => 'drafting',
                        'created_at' => now()->addDays(2),
                    ],
                    [
                        'title' => 'Ideation - Looking for Ideas 🤯',
                        'description' => 'Idea for my article.',
                        'status' => 'ideation',
                        'created_at' => now()->subDays(7),
                    ]
                ]
        ])
        ->schema([

         /*
    	    You should enclose the entire components within a personalized "ActivitySection" entry.
            This section functions identically to the repeater entry; you simply have to provide the array state's key.
    	 */

            ActivitySection::make('activities')
                ->label('My Activities')
                ->description('These are the activities that have been recorded.')
                ->schema([
                    ActivityTitle::make('title')
                        ->placeholder('No title is set')
                        ->allowHtml(), // Be aware that you will need to ensure that the HTML is safe to render, otherwise your application will be vulnerable to XSS attacks.
                    ActivityDescription::make('description')
                        ->placeholder('No description is set')
                        ->allowHtml(),
                    ActivityDate::make('created_at')
                        ->date('F j, Y', 'Asia/Manila')
                        ->placeholder('No date is set.'),
                    ActivityIcon::make('status')
                        ->icon(fn (string | null $state): string | null => match ($state) {
                            'ideation' => 'heroicon-m-light-bulb',
                            'drafting' => 'heroicon-m-bolt',
                            'reviewing' => 'heroicon-m-document-magnifying-glass',
                            'published' => 'heroicon-m-rocket-launch',
                            default => null,
                        })
                        ->color(fn (string | null $state): string | null => match ($state) {
                            'ideation' => 'purple',
                            'drafting' => 'info',
                            'reviewing' => 'warning',
                            'published' => 'success',
                            default => 'gray',
                        }),
                ])
                ->showItemsCount(2) // Show up to 2 items
                ->showItemsLabel('View Old') // Show "View Old" as link label
                ->showItemsIcon('heroicon-m-chevron-down') // Show button icon
                ->showItemsColor('gray') // Show button color and it supports all colors
                ->aside(true)
                ->compact() // remove section card chrome when nested in another container
                ->headingVisible(true) // make heading visible or not
                ->extraAttributes(['class'=>'my-new-class']) // add extra class
        ]);
}
```

When utilizing the `->record()` function, you provide your model in a manner similar to the code showcased below:

```php
protected $activities;

public function __construct()
{
    $this->activities = User::query()->with('activities')->where('id', auth()->user()->id)->first();
}

public function activityTimelineInfolist(Infolist $infolist): Infolist
{
    return $infolist
        ->record($this->activities)
        // ... remaining code
}
```

Sometimes, when we don't have any info to show to users, it's important to improve their experience by displaying something. So, I include an empty state, like the one in the [Filament Table Empty State](https://filamentphp.com/docs/3.x/tables/empty-state).

```php
public function activityTimelineInfolist(Infolist $infolist): Infolist
{
    return $infolist
        ->state([
            'activities' => []
        )]
        ->schema([
            ActivitySection::make('activities')
                // ... other code
                ->emptyStateHeading('No activities yet.')
                ->emptyStateDescription('Check back later for activities that have been recorded.')
                ->emptyStateIcon('heroicon-o-bolt-slash')
        ])
}
```

## Usage with Spatie Activity Log Package

This plugin works with [spatie/laravel-activitylog](https://github.com/spatie/laravel-activitylog), making it easy to log user actions in your app. It can also automatically log model events, storing everything in the `activity_log` table. To use the plugin, just install `spatie/laravel-activitylog`, set it up, and you're good to go.

### Creating a Custom Page

You are required to create a custom page within your `resources` to display all activities based on the record passed to the route.

```php
php artisan make:filament-page ViewOrderActivities --resource=OrderResource --type=custom
```

### Including the Page in your Resource Class

Simply include the custom page in the `getPages()` method so that we can access it.

```php
public static function getPages(): array
{
    return [
        // ... other pages
        // The format of route doesn't matter, as long as it includes the route parameter {record}.
        'activities' => Pages\ViewOrderActivities::route('/order/{record}/activities'),
    ];
}
```

In the `actions` method of your table, include an additional custom action. This action should redirect users to the custom page we've generated earlier.

```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            // ...
        ])
        ->filters([
            // ...
        ])
        ->actions([
            Tables\Actions\Action::make('view_activities')
                ->label('Activities')
                ->icon('heroicon-m-bolt')
                ->color('purple')
                ->url(fn ($record) => OrderResource::getUrl('activities', ['record' => $record])),
        ])
        ->bulkActions([
            // ...
        ]);
}
```

### Setting up your Custom Page

Changes are needed in your custom page. Instead of extending using the regular `Page` class will make use of a specific class called `ActivityTimelinePage` provided by the plugin. Additionally, you should include your resource class.

```php
use App\Filament\Resources\OrderResource;
use LaraZeus\ActivityTimeline\Pages\ActivityTimelinePage;

class ViewOrderActivities extends ActivityTimelinePage
{
    protected static string $resource = OrderResource::class;
}
```
