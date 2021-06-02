<?php

namespace Tests\Unit;

use App\Services\ChangelogParser;
use PHPUnit\Framework\TestCase;
use Tests\CreatesApplication;

class ChangelogParserTest extends TestCase {
	use CreatesApplication;

	public function testParser() {
		$changelogParser = app(ChangelogParser::class);

		$testCases = [
			[
				'code' => '## [Unreleased]
### Added
- test',
				'assert' => [
					[
						'version' => 'Unreleased',
						'date' => null,
						'changeTypes' => [
							['type' => 'Added', 'entries' => ['test']],
						]
					]
				],
			],
			[
				'code' => '## 1.0.0
### Added
- test
## 2.0.0
### Added
- test',
				'assert' => [
					[
						'version' => '1.0.0',
						'date' => null,
						'changeTypes' => [
							['type' => 'Added', 'entries' => ['test']],
						]
					],
					[
						'version' => '2.0.0',
						'date' => null,
						'changeTypes' => [
							['type' => 'Added', 'entries' => ['test']],
						]
					]
				],
			],
			[
				'code' => '## 1.0.0
### Added
- test 2
- test',
				'assert' => [
					[
						'version' => '1.0.0',
						'date' => null,
						'changeTypes' => [
							['type' => 'Added', 'entries' => ['test 2', 'test']],
						]
					],
				],
			],
			[
				'code' => '## 1.0.0
### Fixed
- foo
- bar
### Added
- test 2
- test',
				'assert' => [
					[
						'version' => '1.0.0',
						'date' => null,
						'changeTypes' => [
							['type' => 'Fixed', 'entries' => ['foo', 'bar']],
							['type' => 'Added', 'entries' => ['test 2', 'test']],
						]
					],
				],
			],
			[
				'code' => '## 1.0.0 - 2020-01-01
### Fixed
- foo
- bar
### Added
- test 2
- test',
				'assert' => [
					[
						'version' => '1.0.0',
						'date' => '2020-01-01',
						'changeTypes' => [
							['type' => 'Fixed', 'entries' => ['foo', 'bar']],
							['type' => 'Added', 'entries' => ['test 2', 'test']],
						]
					],
				],
			],
			[
				'code' => '## [Unreleased]
### Added
- test
## 1.0.0 - 2020-01-01
### Fixed
- foo
- bar
### Added
- test 2
- test',
				'assert' => [
					[
						'version' => 'Unreleased',
						'date' => null,
						'changeTypes' => [
							['type' => 'Added', 'entries' => ['test']],
						]
					],
					[
						'version' => '1.0.0',
						'date' => '2020-01-01',
						'changeTypes' => [
							['type' => 'Fixed', 'entries' => ['foo', 'bar']],
							['type' => 'Added', 'entries' => ['test 2', 'test']],
						]
					],
				],
			],
		];

		foreach ( $testCases as $testCase ) {
			$array = $changelogParser->parse($testCase['code']);
			$this->assertSame($testCase['assert'], $array);
		}
	}
}
