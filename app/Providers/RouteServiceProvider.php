<?php

namespace App\Providers;

use App\Models\Build;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider {
	public function boot() {
		$this->configureRateLimiting();
		$this->configureRoutePatterns();

		Route::model('build', Build::class);

		$this->routes(function () {
			Route::prefix('api')
				 ->middleware('api')
				 ->namespace($this->namespace)
				 ->group(base_path('routes/api.php'));

			Route::middleware('web')
				 ->namespace($this->namespace)
				 ->group(base_path('routes/web.php'));
		});
	}

	/**
	 * configure the route parameter patterns
	 */
	protected function configureRoutePatterns() {
		Route::pattern('id', '[0-9]+');
	}

	/**
	 * Configure the rate limiters for the application.
	 *
	 * @return void
	 */
	protected function configureRateLimiting() {
		RateLimiter::for('api', function (Request $request) {
			return Limit::perMinute(180); // (3 requests per second, should be enough)
		});
		RateLimiter::for('web', function (Request $request) {
			return Limit::perMinute(120); // (90 requests per minute - this is only the request page)
		});
	}
}