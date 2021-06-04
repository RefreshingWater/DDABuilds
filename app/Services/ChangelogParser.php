<?php

namespace App\Services;

use DateTimeImmutable;

class ChangelogParser {
	const VERSION_LINE_PREFIX = '^#{2}\s?';

	const CHANGE_LINE_PREFIX = '^#{3}\s?';

	const DETAILS_LINE_PREFIX = '^-\s?';

	const VERSION_NUMBER_REGEX = 'Unreleased|(?:\d+\.\d+\.\d+)';

	const DATE_REGEX = '\d{4}-\d{2}-\d{2}';

	const CHANGE_REGEX = '\w+';

	public function parse($content) : array {
		$changelog = [];

		// extract versions
		foreach ( $this->extractVersions($content) as $block ) {
			if ( !($version = $this->extractVersion($block)) ) {
				continue;
			}

			$date = $this->extractSingleLine(
				$block,
				sprintf(
					'/%s.+(%s)\r?\n?$/im',
					self::VERSION_LINE_PREFIX,
					self::DATE_REGEX
				)
			);
			$date = $date !== null ? $date : null;

			$changeTypes = [];
			foreach ( $this->extractChangeTypes($block) as $changeTypeBlock ) {
				$changeType = [
					'type' => $this->extractChangeType($changeTypeBlock),
					'entries' => [],
				];

				foreach ( $this->extractBlocks($changeTypeBlock, self::DETAILS_LINE_PREFIX) as $change ) {
					$changeType['entries'][] = $this->extractDetailsDescription($change);
				}
				$changeTypes[] = $changeType;
			}

			$changelog[] = [
				'version' => $version,
				'date' => $date,
				'changeTypes' => $changeTypes,
			];
		}

		return $changelog;
	}

	public function extractVersions(string $content) : array {
		return $this->extractBlocks($content, sprintf(
			'%s%s',
			self::VERSION_LINE_PREFIX,
			sprintf('\[?%s\]?', '(?:'.self::VERSION_NUMBER_REGEX.')')
		));
	}

	public function extractChangeTypes(string $content) : array {
		return $this->extractBlocks($content, sprintf(
			'%s%s',
			self::CHANGE_LINE_PREFIX,
			self::CHANGE_REGEX
		));
	}

	public function extractVersion(string $content) : ?string {
		return $this->extractSingleLine(
			$content,
			sprintf(
				'/%s%s/im',
				'^#{2}\s?',
				sprintf('\[?%s\]?', '('.self::VERSION_NUMBER_REGEX.')')
			)
		);
	}

	public function extractChangeType(string $content) : ?string {
		return $this->extractSingleLine(
			$content,
			sprintf('/%s%s/im', '^#{3}\s?', sprintf('\[?%s\]?', '('.self::CHANGE_REGEX.')'))
		);
	}

	public function extractDetailsDescription(string $content) : ?string {
		return $this->extractSingleLine(
			$content,
			sprintf('/%s((?:.|\n)*)$/im', self::DETAILS_LINE_PREFIX)
		);
	}

	private function extractSingleLine(string $content, string $regex) : ?string {
		if ( preg_match($regex, $content, $matches) === 1 ) {
			return trim($matches[2] ?? $matches[1]);
		}

		return null;
	}

	private function extractBlocks(string $content, string $arg) : array {
		if ( preg_match_all(sprintf('!(%1$s(?:.|\n)*?)(?=(?:%1$s)|\z)!im', $arg), $content, $matches) === 0 ) {
			return [];
		}

		return array_map('trim', $matches[1]);
	}
}