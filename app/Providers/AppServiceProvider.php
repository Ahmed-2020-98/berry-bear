<?php

namespace App\Providers;

use App\Models\ContactMessage;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('admin', fn (User $user) => $user->isAdmin());

        Paginator::defaultView('admin.partials.pagination');

        View::composer(['layouts.site', 'site.*'], function ($view) {
            $view->with('menu', once(fn () => MenuItem::whereNull('parent_id')
                ->with('children')->orderBy('sort')->orderBy('id')->get()));
            $view->with('contact', [
                'address' => setting('contact_address'),
                'email' => setting('contact_email'),
                'phone' => setting('contact_phone'),
                'hours' => setting('contact_hours'),
                'hours_long' => setting('contact_hours_long'),
            ]);
        });

        View::composer('admin.layout', function ($view) {
            $view->with('unreadMessages', ContactMessage::whereNull('read_at')->count());
        });
    }
}
