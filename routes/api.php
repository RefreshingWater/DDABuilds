<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildCommentController;
use App\Http\Controllers\BuildController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\IssueCommentController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// routes where an authentication is not required
Route::group(['middleware' => ['auth:guest']], function () {
	Route::get('/auth/steam', [AuthController::class, 'auth']);
});

// routes where an authentication is required
Route::group(['middleware' => ['auth:user']], function () {
	Route::delete('/auth', [AuthController::class, 'logout']);

	Route::post('/builds/{build}/watch', [BuildController::class, 'watch']);

	Route::get('/notifications/', [NotificationController::class, 'index']);
	Route::get('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
	Route::post('/notifications/mark-as-read/{notificationID}', [NotificationController::class, 'markAsRead']);

	Route::post('/like/', [LikeController::class, 'like']);

	Route::apiResources([
		'maps' => MapController::class,
		'issues' => IssueController::class,
		'issues.comments' => IssueCommentController::class,
	]);

	if ( app()->environment('local') ) {
		Route::get('/notifications/debug', [NotificationController::class, 'debug']);
	}
});

Route::get('/changelogs', [ChangelogController::class, 'index']);
Route::get('/maps/editor/{map}', [MapController::class, 'editor']);
Route::apiResources([
	'builds' => BuildController::class,
	'builds.comments' => BuildCommentController::class,
]);

// every other route -> not found
Route::get('{any}', [IndexController::class, 'notFound'])->where('any', '.*?');