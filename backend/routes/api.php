<?php
use App\Http\Controllers\Api\V1\ActivityController;
use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\AttachmentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AutomationController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\FeatureFlagController;
use App\Http\Controllers\Api\V1\IssueLabelController;
use App\Http\Controllers\Api\V1\IssueController;
use App\Http\Controllers\Api\V1\IssueStatusController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Http\Controllers\Api\V1\TeamMemberController;
use App\Http\Controllers\Api\V1\TimeEntryController;
use App\Http\Controllers\Api\V1\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::patch('me', [AuthController::class, 'updateMe']);
            Route::get('teams', [AuthController::class, 'teams']);
        });
    });

    // Invitations (public token-based)
    Route::post('invitations/{token}/accept', [TeamMemberController::class, 'acceptInvitation'])->middleware('auth:sanctum');

    Route::middleware('auth:sanctum')->group(function () {

        // Teams
        Route::apiResource('teams', TeamController::class)->except(['index']);
        Route::get('teams', [TeamController::class, 'index']);

        Route::prefix('teams/{team}')->middleware('App\Http\Middleware\EnsureTeamMember')->group(function () {
            // Members & Invitations
            Route::get('members', [TeamMemberController::class, 'index']);
            Route::post('invitations', [TeamMemberController::class, 'invite']);
            Route::patch('members/{member}', [TeamMemberController::class, 'update']);
            Route::delete('members/{member}', [TeamMemberController::class, 'destroy']);

            // Analytics & Activity
            Route::get('analytics', [AnalyticsController::class, 'team']);
            Route::get('activity', [ActivityController::class, 'team']);

            // Notifications
            Route::get('notifications', [NotificationController::class, 'index']);
            Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
            Route::patch('notifications/{id}', [NotificationController::class, 'markRead']);

            // Webhooks
            Route::get('webhooks', [WebhookController::class, 'index']);
            Route::post('webhooks', [WebhookController::class, 'store']);
            Route::patch('webhooks/{webhook}', [WebhookController::class, 'update']);
            Route::delete('webhooks/{webhook}', [WebhookController::class, 'destroy']);
            Route::post('webhooks/{webhook}/test', [WebhookController::class, 'test']);

            // Search
            Route::get('search', SearchController::class);

            // Projects
            Route::get('projects', [ProjectController::class, 'index']);
            Route::post('projects', [ProjectController::class, 'store']);
            Route::get('projects/{project}', [ProjectController::class, 'show']);
            Route::patch('projects/{project}', [ProjectController::class, 'update']);
            Route::delete('projects/{project}', [ProjectController::class, 'destroy']);

            // Project analytics
            Route::get('projects/{project}/analytics', [AnalyticsController::class, 'project']);

            // Issue Statuses
            Route::post('projects/{project}/statuses', [IssueStatusController::class, 'store']);
            Route::patch('projects/{project}/statuses/{status}', [IssueStatusController::class, 'update']);
            Route::delete('projects/{project}/statuses/{status}', [IssueStatusController::class, 'destroy']);

            // Issue Labels
            Route::post('projects/{project}/labels', [IssueLabelController::class, 'store']);
            Route::patch('projects/{project}/labels/{label}', [IssueLabelController::class, 'update']);
            Route::delete('projects/{project}/labels/{label}', [IssueLabelController::class, 'destroy']);

            // Automations
            Route::get('projects/{project}/automations', [AutomationController::class, 'index']);
            Route::post('projects/{project}/automations', [AutomationController::class, 'store']);
            Route::patch('projects/{project}/automations/{rule}', [AutomationController::class, 'update']);
            Route::delete('projects/{project}/automations/{rule}', [AutomationController::class, 'destroy']);

            // Issues
            Route::get('projects/{project}/issues', [IssueController::class, 'index']);
            Route::post('projects/{project}/issues', [IssueController::class, 'store']);
            Route::post('projects/{project}/issues/bulk', [IssueController::class, 'bulkUpdate']);
            Route::get('projects/{project}/issues/{issue}', [IssueController::class, 'show']);
            Route::patch('projects/{project}/issues/{issue}', [IssueController::class, 'update']);
            Route::delete('projects/{project}/issues/{issue}', [IssueController::class, 'destroy']);

            // Issue sub-resources
            Route::get('projects/{project}/issues/{issue}/comments', [CommentController::class, 'index']);
            Route::post('projects/{project}/issues/{issue}/comments', [CommentController::class, 'store']);
            Route::patch('projects/{project}/issues/{issue}/comments/{comment}', [CommentController::class, 'update']);
            Route::delete('projects/{project}/issues/{issue}/comments/{comment}', [CommentController::class, 'destroy']);

            Route::get('projects/{project}/issues/{issue}/attachments', [AttachmentController::class, 'index']);
            Route::post('projects/{project}/issues/{issue}/attachments', [AttachmentController::class, 'store']);
            Route::delete('projects/{project}/issues/{issue}/attachments/{attachment}', [AttachmentController::class, 'destroy']);

            Route::get('projects/{project}/issues/{issue}/time-entries', [TimeEntryController::class, 'index']);
            Route::post('projects/{project}/issues/{issue}/time-entries', [TimeEntryController::class, 'store']);
            Route::delete('projects/{project}/issues/{issue}/time-entries/{entry}', [TimeEntryController::class, 'destroy']);

            Route::get('projects/{project}/issues/{issue}/activity', [ActivityController::class, 'issue']);
        });

        // Admin
        Route::prefix('admin')->middleware('can:adminAccess')->group(function () {
            Route::apiResource('feature-flags', FeatureFlagController::class);
        });
    });
});
