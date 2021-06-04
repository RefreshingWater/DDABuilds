<?php

namespace App\Http\Controllers;

use App\Services\ChangelogParser;
use Illuminate\Support\Facades\Cache;

class ChangelogController extends AbstractController {
	protected function filterChangelogs(array $changelogs) : array {
		$filtered = [];
		foreach ( $changelogs as $changelog ) {
			if ( !empty($changelog['changeTypes']) ) {
				$filtered[] = $changelog;
			}
		}

		return $filtered;
	}

	public function index(ChangelogParser $changelogParser) : array {
		// use local CHANGELOG.md in dev mode
		if ( app()->environment('local') ) {
			return $this->filterChangelogs($changelogParser->parse(file_get_contents(base_path('CHANGELOG.md'))));
		}

		return Cache::remember('github-changelog', now()->addHour(), function () use ($changelogParser) {
			return $this->filterChangelogs($changelogParser->parse(file_get_contents('https://raw.githubusercontent.com/RefreshingWater/DDABuilds/master/CHANGELOG.md')));
		});
	}
}