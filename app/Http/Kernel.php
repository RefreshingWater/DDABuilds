<?php

namespace App\Http;

use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\IsAuthenticated;
use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel {
	/** @inheritdoc */
	protected $middleware = [
		PreventRequestsDuringMaintenance::class,
		// \Fruitcake\Cors\HandleCors::class,
		StartSession::class,
		ValidatePostSize::class,
		TrimStrings::class,
		ConvertEmptyStringsToNull::class,
	];

	/** @inheritdoc */
	protected $middlewareGroups = [
		'web' => [
			'throttle:web',
			EncryptCookies::class,
			AddQueuedCookiesToResponse::class,
			ShareErrorsFromSession::class,
			VerifyCsrfToken::class,
			'bindings',
		],
		'api' => [
			'throttle:api',
			'bindings',
			ForceJsonResponse::class,
			LocaleMiddleware::class,
		],
	];

	/** @inheritdoc */
	protected $routeMiddleware = [
		'auth' => IsAuthenticated::class,
		'can' => Authorize::class,
		'bindings' => SubstituteBindings::class,
		'throttle' => ThrottleRequests::class,
	];
}