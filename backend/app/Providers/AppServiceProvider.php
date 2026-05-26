<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Issue;
use App\Observers\CommentObserver;
use App\Observers\IssueObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Issue::observe(IssueObserver::class);
        Comment::observe(CommentObserver::class);

        Gate::define('adminAccess', fn($user) => $user->is_admin);

        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(300)->by($request->user()->id)
                : Limit::perMinute(60)->by($request->ip());
        });
    }
}
