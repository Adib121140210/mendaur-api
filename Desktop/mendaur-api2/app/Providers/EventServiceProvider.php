<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

// Badge tracking events and listeners
use App\Events\TabungSampahCreated;
use App\Events\PoinTransaksiCreated;
use App\Listeners\UpdateBadgeProgressOnTabungSampah;
use App\Listeners\UpdateBadgeProgressOnPoinChange;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Badge Tracking System Events
        TabungSampahCreated::class => [
            UpdateBadgeProgressOnTabungSampah::class,
        ],

        PoinTransaksiCreated::class => [
            UpdateBadgeProgressOnPoinChange::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
