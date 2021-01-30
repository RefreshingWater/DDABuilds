<?php

namespace App\Models;

use App\Models\Build\BuildHeroStats;
use App\Models\Build\BuildWave;
use App\Models\Like\ILikeableModel;
use App\Models\Traits\TLikeable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property-read int        $ID
 * @property-read string     $date
 * @property-read string     $author
 * @property-read string     $title
 * @property-read string     $expPerRun
 * @property-read string     $timePerRun
 * @property-read string     $description
 * @property-read int        $views
 * @property-read int        $gameModeID
 * @property-read int        $difficultyID
 * @property-read int        $mapID
 * @property-read int        $comments
 * @property-read int        $isDeleted
 * @property-read int        $buildStatus
 * @property-read int        $afkAble
 * @property-read int        $hardcore
 * @property-read int        $likes
 * @property-read int        $steamID
 *
 * @property-read Like       $likeValue
 * @property-read GameMode   $gameMode
 * @property-read Difficulty $difficulty
 * @property-read Map        $map
 */
class Build extends AbstractModel implements ILikeableModel {
	use HasFactory;
	use TLikeable;

	/** @var int public build status (everyone can view the build) */
	public const STATUS_PUBLIC = 1;

	/** @var int unlisted build status (build ist not listed, but everyone can view the build) */
	public const STATUS_UNLISTED = 2;

	/** @var int private build status (only creator can view the build) */
	public const STATUS_PRIVATE = 3;

	/** @inheritdoc */
	protected $table = 'build';

	/** @inheritdoc */
	protected $perPage = 21;

	/** @inheritdoc */
	protected $primaryKey = 'ID';

	/** @inheritdoc */
	public $timestamps = false;

	/** @inheritdoc */
	protected $fillable = [
		'author',
		'title',
		'mapID',
		'difficultyID',
		'description',
		'date',
		'steamID',
		'buildStatus',
		'gameModeID',
		'hardcore',
		'afkAble',
		// 'views',
		'likes',
		// 'comments',
		'timePerRun',
		'expPerRun',
		'isDeleted',
	];

	public $validSortFields = ['author', 'likes', 'mapID', 'title', 'views', 'date', 'difficultyID', 'gameModeID'];

	public function map() {
		return $this->hasOne(Map::class, 'ID', 'mapID');
	}

	public function difficulty() {
		return $this->hasOne(Difficulty::class, 'ID', 'difficultyID');
	}

	public function gameMode() {
		return $this->hasOne(GameMode::class, 'ID', 'gameModeID');
	}

	public function waves() {
		return $this->hasMany(BuildWave::class, 'buildID', 'ID');
	}

	public function heroStats() {
		return $this->hasMany(BuildHeroStats::class, 'buildID', 'ID');
	}

	public function scopeSort(Builder $query, $column = 'date', $direction = 'asc') {
		if ( $column === null && $direction === null ) {
			$column = 'date';
			$direction = 'desc';
		}

		if ( $column === null ) {
			$column = 'date';
		}
		if ( $direction === null ) {
			$direction = 'asc';
		}

		if ( in_array($column, $this->validSortFields) ) {
			$query->orderBy($this->table.'.'.$column, $direction);
		}
	}

	public function scopeSearch(Builder $query, array $searchParameters) {
		$searchParameters = array_merge([
			'isDeleted' => 0,
			'title' => null,
			'author' => null,
			'map' => null,
			'difficulty' => null,
			'gameMode' => null,
		], $searchParameters);

		$where = [
			['isDeleted', '=', 0],
		];

		if ( $searchParameters['title'] ) {
			$where[] = ['title', 'like', '%'.$searchParameters['title'].'%'];
		}
		if ( $searchParameters['author'] ) {
			$where[] = ['author', 'like', '%'.$searchParameters['author'].'%'];
		}

		if ( $searchParameters['map'] ) {
			/** @var Collection $maps */
			$maps = Map::whereIn('name', explode(',', $searchParameters['map']))->get();
			if ( count($maps) ) {
				$query->whereIn('mapID', array_column($maps->toArray(), 'ID'));
			}
		}
		if ( $searchParameters['gameMode'] ) {
			/** @var GameMode $gameMode */
			$gameMode = GameMode::where('name', $searchParameters['gameMode'])->first();
			if ( $gameMode !== null ) {
				$where[] = ['gameModeID', '=', $gameMode->ID];
			}
		}
		if ( $searchParameters['difficulty'] ) {
			/** @var Difficulty $difficulty */
			$difficulty = Difficulty::where('name', $searchParameters['difficulty'])->first();
			if ( $difficulty !== null ) {
				$where[] = ['difficultyID', '=', $difficulty->ID];
			}
		}

		$query->where($where)->where(function ($query) {
			$query->where('buildStatus', '=', self::STATUS_PUBLIC);
			if ( auth()->id() ) {
				$query->orWhere($this->table.'.steamID', '=', auth()->id());
			}
		});
	}
}