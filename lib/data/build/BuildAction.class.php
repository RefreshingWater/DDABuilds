<?php

namespace data\build;

use data\build\status\BuildStatus;
use data\build\wave\BuildWaveAction;
use data\DatabaseObjectAction;
use data\difficulty\Difficulty;
use data\gamemode\Gamemode;
use data\heroClass\HeroClass;
use data\heroClass\HeroClassList;
use data\map\Map;
use system\Core;
use system\exception\PermissionDeniedException;
use system\exception\UserInputException;
use system\request\LinkHandler;
use system\util\StringUtil;

class BuildAction extends DatabaseObjectAction {
	/**
	 * @throws PermissionDeniedException
	 */
	public function validateWatch() {
		if ( empty($this->objects) ) {
			$this->readObjects();
		}

		/** @var Build $build */
		foreach ( $this->objects as $build ) {
			if ( $build->isCreator() ) {
				throw new PermissionDeniedException();
			}
		}
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function watch() {
		$newState = [];
		$selectStatement = Core::getDB()->prepareStatement('SELECT * FROM build_watch WHERE buildID = ? AND steamID = ?');
		$insertStatement = Core::getDB()->prepareStatement('INSERT INTO build_watch (buildID, steamID) VALUES (?, ?)');
		$deleteStatement = Core::getDB()->prepareStatement('DELETE FROM build_watch WHERE buildID = ? AND steamID = ?');

		foreach ( $this->objectIDs as $buildID ) {
			$selectStatement->execute([$buildID, Core::getUser()->steamID]);
			if ( $selectStatement->rowCount() ) {
				$deleteStatement->execute([$buildID, Core::getUser()->steamID]);
				$newState[$buildID] = 0;
			}
			else {
				$insertStatement->execute([$buildID, Core::getUser()->steamID]);
				$newState[$buildID] = 1;
			}
		}

		return $newState;
	}

	/**
	 * @throws PermissionDeniedException
	 */
	public function validateTrash() {
		if ( empty($this->objects) ) {
			$this->readObjects();
		}

		/** @var Build $build */
		foreach ( $this->objects as $build ) {
			if ( !$build->isCreator() ) {
				throw new PermissionDeniedException();
			}
		}
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function trash() {
		/** @var Build $build */
		foreach ( $this->objects as $build ) {
			$build->update([
				'deleted' => 1,
			]);
		}

		return LinkHandler::getInstance()->getLink('BuildList');
	}

	/**
	 * @throws PermissionDeniedException
	 * @throws UserInputException
	 */
	public function validateSave() {
		$this->readBoolean('afkAble');
		$this->readBoolean('hardcore');

		foreach ( ['author', 'buildName', 'mapID', 'difficulty', 'buildStatus', 'towers'] as $field ) {
			if ( !isset($this->parameters[$field]) ) {
				throw new UserInputException($field);
			}
		}

		$this->parameters['author'] = substr(trim($this->parameters['author']), 0, 20);
		$this->parameters['buildName'] = substr(trim($this->parameters['buildName']), 0, 128);

		if ( empty($this->parameters['author']) ) {
			throw new UserInputException('author');
		}
		if ( empty($this->parameters['buildName']) ) {
			throw new UserInputException('buildName');
		}

		// validate name
		$map = new Map($this->parameters['mapID']);
		if ( !$map->getObjectID() ) {
			throw new UserInputException('mapID', 'invalid');
		}

		// validate difficulty
		$difficulty = new Difficulty($this->parameters['difficulty']);
		if ( !$difficulty->getObjectID() ) {
			throw new UserInputException('difficulty', 'invalid');
		}

		// validate build status
		$buildStatus = new BuildStatus($this->parameters['buildStatus']);
		if ( !$buildStatus->getObjectID() ) {
			throw new UserInputException('buildStatus', 'invalid');
		}

		$gamemode = new Gamemode($this->parameters['gamemodeID']);
		if ( !$gamemode->getObjectID() ) {
			throw new UserInputException('gamemodeID', 'invalid');
		}

		$this->parameters['image'] = !empty($this->parameters['image']) ? $this->parameters['image'] : null;
		$this->parameters['stats'] = !empty($this->parameters['stats']) && is_array($this->parameters['stats']) ? $this->parameters['stats'] : [];
		$this->parameters['towers'] = !empty($this->parameters['towers']) && is_array($this->parameters['towers']) ? $this->parameters['towers'] : [];
		$this->parameters['waves'] = !empty($this->parameters['customWaves']) && is_array($this->parameters['customWaves']) ? $this->parameters['customWaves'] : [];

		if ( empty($this->parameters['towers']) ) {
			throw new UserInputException('towers');
		}

		if ( empty($this->objects) ) {
			$this->readObjects();
		}

		/** @var Build $build */
		foreach ( $this->getObjects() as $build ) {
			if ( !$build->isCreator() ) {
				throw new PermissionDeniedException();
			}
		}
	}

	public function save() {
		$this->parameters['data'] = [
			'author'         => $this->parameters['author'],
			'name'           => $this->parameters['buildName'],
			'map'            => $this->parameters['mapID'],
			'difficulty'     => $this->parameters['difficulty'],
			'afkable'        => $this->parameters['afkAble'] ? 1 : 0,
			'hardcore'       => $this->parameters['hardcore'] ? 1 : 0,
			'description'    => isset($this->parameters['description']) ? StringUtil::removeInsecureHtml($this->parameters['description']) : '',
			'timePerRun'     => isset($this->parameters['timePerRun']) ? $this->parameters['timePerRun'] : '',
			'expPerRun'      => isset($this->parameters['expPerRun']) ? $this->parameters['expPerRun'] : '',
			'date'           => date('Y-m-d H:i:s'),
			'fk_user'        => Core::getUser()->steamID,
			'fk_buildstatus' => $this->parameters['buildStatus'],
			'gamemodeID'     => $this->parameters['gamemodeID'],
		];

		$returnValues = null;
		$deleteOldEntries = false;
		if ( empty($this->getObjectIDs()) ) {
			/** @var Build $object */
			$object = $this->create();
			$this->objects[] = $object;
			$returnValues = $object->getLink();
		}
		else {
			$deleteOldEntries = true;
			$this->update();
		}

		$deleteWaves = $deleteTowers = $deleteStats = null;
		if ( $deleteOldEntries ) {
			$deleteWaves = Core::getDB()->prepareStatement('DELETE FROM buildwaves WHERE fk_build = ?');
			$deleteTowers = Core::getDB()->prepareStatement('DELETE FROM placed WHERE fk_build = ?');
			$deleteStats = Core::getDB()->prepareStatement('DELETE FROM build_stats WHERE buildID = ?');
		}

		$insertTower = Core::getDB()->prepareStatement('INSERT INTO placed (fk_build, fk_tower, x, y, rotation, fk_buildwave, override_du) VALUES (?, ?, ?, ?, ?, ?, ?);');
		$insertStats = Core::getDB()->prepareStatement('INSERT INTO build_stats (buildID, classID, hp, damage, rate, `range`) VALUES (?, ?, ?, ?, ?, ?)');
		foreach ( $this->getObjects() as $build ) {
			if ( $deleteOldEntries ) {
				$deleteWaves->execute([$build->getObjectID()]);
				$deleteTowers->execute([$build->getObjectID()]);
				$deleteStats->execute([$build->getObjectID()]);
			}

			// create waves
			$validWaves = [0];
			if ( !empty($this->parameters['waves']) ) {
				$i = 1;
				foreach ( $this->parameters['waves'] as $waveId => $waveName ) {
					$wave = new BuildWaveAction([], 'create', [
						'data' => [
							'name'     => $waveName,
							'fk_build' => $build->getObjectID(),
						],
					]);
					$wave->executeAction();
					$validWaves[$waveId + 1] = $i++;
				}
			}

			// insert towers
			if ( !empty($this->parameters['towers']) ) {
				foreach ( $this->parameters['towers'] as $tower ) {
					if ( isset($validWaves[$tower['wave']]) ) {
						$insertTower->execute([
							$build->getObjectID(),
							$tower['towerID'],
							(int) $tower['x'],
							(int) $tower['y'],
							(int) $tower['rotation'],
							(int) $validWaves[$tower['wave']],
							(int) $tower['unitCost']
							// 0, // override du
						]);
					}
				}
			}

			// save hero class stats
			if ( !empty($this->parameters['stats']) ) {
				$heroClasses = new HeroClassList();
				$heroClasses->readObjects();
				$heroClasses = $heroClasses->getObjects();

				/** @var HeroClass[] $heroClasses */
				foreach ( $this->parameters['stats'] as $key => $statsValue ) {
					if ( !isset($heroClasses[$key]) || !$heroClasses[$key]->isHero ) {
						continue;
					}

					$hp = isset($statsValue['hp']) ? (int) $statsValue['hp'] : 0;
					$rate = isset($statsValue['rate']) ? (int) $statsValue['rate'] : 0;
					$range = isset($statsValue['range']) ? (int) $statsValue['range'] : 0;
					$damage = isset($statsValue['damage']) ? (int) $statsValue['damage'] : 0;

					// skip empty stats
					if ( max($hp, $rate, $range, $damage) === 0 ) {
						continue;
					}

					$insertStats->execute([
						$build->getObjectID(),
						$key,
						$hp,
						$damage,
						$rate,
						$range,
					]);
				}
			}

			// image
			if ( !empty($this->parameters['image']) ) {
				$build->saveScreenshot($this->parameters['image']);
			}
		}

		return $returnValues;
	}
}