<?php

namespace App\Models;

use App\Models\Build\BuildComment;
use App\Models\Build\BuildHeroStats;
use App\Models\Build\BuildTower;
use App\Models\Build\BuildWatch;
use App\Models\Build\BuildWave;
use App\Models\Like\ILikeableModel;
use App\Models\Traits\HasSteamUser;
use App\Models\Traits\TLikeable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property-read int $ID
 * @property-read string $date
 * @property-read string $author
 * @property-read string $title
 * @property-read string $expPerRun
 * @property-read string $timePerRun
 * @property-read string $description
 * @property-read int $views
 * @property-read int $gameModeID
 * @property-read int $difficultyID
 * @property-read int $mapID
 * @property-read int $comments
 * @property-read int $isDeleted
 * @property-read int $buildStatus
 * @property-read int $afkAble
 * @property-read int $hardcore
 * @property-read int $likes
 * @property-read string $steamID
 *
 * @property-read Like $likeValue
 * @property-read GameMode $gameMode
 * @property-read Difficulty $difficulty
 * @property-read Map $map
 * @property-read BuildWatch $watchStatus
 */
class Build extends AbstractModel implements ILikeableModel {
	use HasFactory;
	use HasSteamUser;
	use TLikeable;

	/** @var int public build status (everyone can view the build) */
	public const STATUS_PUBLIC = 1;

	/** @var int unlisted build status (build ist not listed, but everyone can view the build) */
	public const STATUS_UNLISTED = 2;

	/** @var int private build status (only creator can view the build) */
	public const STATUS_PRIVATE = 3;

	protected $table = 'build';

	protected $perPage = 21;

	protected $primaryKey = 'ID';

	public $timestamps = false;

	protected $fillable = [
		'mapID',
		'difficultyID',
		'steamID',
		'author',
		'title',
		'description',
		'date',
		'buildStatus',
		'gameModeID',
		'hardcore',
		'afkAble',
		'rifted',
		'views',
		'likes',
		'comments',
		'timePerRun',
		'expPerRun',
		'isDeleted',
	];

	public $validSortFields = [
		'author',
		'likes',
		'mapID',
		'title',
		'views',
		'date',
		'difficultyID',
		'gameModeID',
	];

	public function watchStatus() {
		return $this->hasOne(BuildWatch::class, 'buildID', 'ID')->where('steamID', auth()->id() ?? 0);
	}

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

	public function commentList() {
		return $this->hasMany(BuildComment::class, 'buildID', 'ID')->orderBy('date', 'desc');
	}

	public function addStats($stats) {
		if ( !$this->ID ) {
			return false;
		}

		$stats['buildID'] = $this->ID;
		if ( $stats['hp'] || $stats['damage'] || $stats['range'] || $stats['rate'] ) {
			$this->heroStats()->create($stats);
		}

		return true;
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
			$maps = Map::query()->whereIn('name', explode(',', $searchParameters['map']))->get();
			if ( count($maps) ) {
				$query->whereIn('mapID', $maps->pluck('ID'));
			}
		}
		if ( $searchParameters['gameMode'] ) {
			$gameModes = GameMode::query()->whereIn('name', explode(',', $searchParameters['gameMode']))->get();
			if ( count($gameModes) ) {
				$query->whereIn('gameModeID', $gameModes->pluck('ID'));
			}
		}
		if ( $searchParameters['difficulty'] ) {
			$difficulties = Difficulty::query()->whereIn('name', explode(',', $searchParameters['difficulty']))->get();
			if ( $difficulties !== null ) {
				$query->whereIn('difficultyID', $difficulties->pluck('ID'));
			}
		}

		$query->where($where)->where(function ($query) {
			$query->where('buildStatus', '=', self::STATUS_PUBLIC);
			if ( auth()->id() ) {
				$query->orWhere($this->table.'.steamID', '=', auth()->id());
			}
		});
	}

	public function generateThumbnail() {
		$mapResource = imagecreatefrompng($this->map->getPublicPath());
		if ( !$mapResource ) {
			return false;
		}

		$mapResource = imagescale($mapResource, 1024, 1024, IMG_BICUBIC_FIXED);
		if ( !$mapResource ) {
			return false;
		}

		/** @var BuildTower $tower */
		foreach ( $this->waves()->first()->towers()->get() as $tower ) {
			$towerSizeX = $towerSizeY = 35;

			if ( $tower->towerInfo->hero->name === 'monk' ) {
				$towerSizeX = $towerSizeY = 100;
			}
			elseif ( $tower->towerInfo->hero->name === 'huntress' ) {
				$towerSizeX = $towerSizeY = 45;
			}
			elseif ( $tower->towerInfo->hero->name === 'seriesEVA' ) {
				$towerSizeX = $towerSizeY = 0;
			}

			$towerResource = imagecreatefrompng($tower->getPublicPath());
			if ( $towerSizeX && $towerSizeY ) {
				$towerResource = imagescale($towerResource, $towerSizeX, $towerSizeY);
			}
			else {
				$imageInfo = getimagesize($tower->getPublicPath());
				$towerSizeX = $imageInfo[0];
				$towerSizeY = $imageInfo[1];
			}

			$rotation = -$tower->rotation;
			$x = $tower->x;
			$y = $tower->y;
			if ( $rotation ) {
				$png = imagecreatetruecolor($towerSizeX, $towerSizeY);
				imagesavealpha($png, true);
				$pngTransparency = imagecolorallocatealpha($png, 0, 0, 0, 127);
				$towerResource = imagerotate($towerResource, $rotation, $pngTransparency);
				$newTowerSizeX = imagesx($towerResource);
				$newTowerSizeY = imagesy($towerResource);
				$x -= ($newTowerSizeX - $towerSizeX) / 2;
				$y -= ($newTowerSizeY - $towerSizeY) / 2;
				$towerSizeX = $newTowerSizeX;
				$towerSizeY = $newTowerSizeY;
			}

			imagecopy($mapResource, $towerResource, $x, $y, 0, 0, $towerSizeX, $towerSizeY);
		}

		return imagepng(
			imagescale($mapResource, 200, 200, IMG_BICUBIC_FIXED),
			$this->getPublicThumbnailPath()
		);
	}

	public function getPublicThumbnailPath() {
		return public_path('assets/images/thumbnail/'.$this->ID.'.png');
	}

	public function getNotificationData() {
		return [
			'ID' => $this->ID,
			'title' => $this->title,
		];
	}
}