<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\Painting as PaintingEntity;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;

class Painting extends Item{
	public function __construct(int $meta = 0){
		parent::__construct(self::PAINTING, $meta, "Painting");
	}

	public function onActivate(Level $level, Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool{
		if($blockClicked->isTransparent() === false and $face > 1 and $blockReplace->isSolid() === false){
			$faces = [
				2 => 1,
				3 => 3,
				4 => 0,
				5 => 2,
			];

			$motives = PaintingEntity::MOTIVES;

			$face2side = [4, 5, 3, 2];
			$motive = 0;
			$foundApplicableMotive = false;
			$searchedMotives = [];
			while (!$foundApplicableMotive){
				if (count($searchedMotives) == count($motives)){
					break;
				}
				$rand = -1;
				while (!isset($motives[$rand])){
					$rand = mt_rand(0, count($motives) - 1);
				}
				$motive = $motives[$rand];
				$foundApplicableMotive = true;
				$searchedMotives[$rand] = true;
				for ($x = 0; $x < $motive[1] && $foundApplicableMotive; $x++){
					for ($z = 0; $z < $motive[2] && $foundApplicableMotive; $z++){
						if ($blockClicked->getSide($face2side[$face - 2], $x)->isTransparent() ||
							$blockClicked->getSide(Vector3::SIDE_UP, $z)->isTransparent() ||
							$blockReplace->getSide($face2side[$face - 2], $x)->isSolid() ||
							$blockReplace->getSide(Vector3::SIDE_UP, $z)->isSolid()
						){
							$foundApplicableMotive = false;
						}
					}
				}
			}
			if (!$foundApplicableMotive){
				return false;
			}

			//WIP overlapping calculation AND BTW: EVEN MCPE DOESN'T DO THIS!
			/*
			for ($x = 0; $x < $motive[1] && $valid; $x++){
				for ($z = 0; $z < $motive[2] && $valid; $z++){
					$entPos = $target->getSide($right[$face - 2], $x);
					//getEntitiesAtThatPos (high intensive calculation)
					if ($entity instanceof PaintingEntity){
						return false;
					}
				}
			}*/

			$data = [
				"x" => $blockClicked->x,
				"y" => $blockClicked->y,
				"z" => $blockClicked->z,
				"yaw" => $faces[$face] * 90,
				"Motive" => $motive[0],
			];
			if ($motive[2] <= 1){
				$data["y"] = $data["y"] + 1;
			}
			if ($motive[2] >= 2){
				$data["y"] = $data["y"] + 0.4;
			}
			if ($motive[2] >= 3){
				$data["y"] = $data["y"] + 0.1;
			}
			switch ($face2side[$face - 2]){
				case Vector3::SIDE_NORTH:
					$data["z"] = $data["z"] + 0.1;
					break;
				case Vector3::SIDE_SOUTH:
					$data["z"] = $data["z"] - 0.1;
					break;
				case Vector3::SIDE_WEST:
					$data["x"] = $data["x"] + 0.1;
					break;
				case Vector3::SIDE_EAST:
					$data["x"] = $data["x"] - 0.1;
					break;
			}

			$nbt = new CompoundTag("", [
				new StringTag("Motive", $data["Motive"]),
				new ListTag("Pos", [
					new DoubleTag("", $data["x"]),
					new DoubleTag("", $data["y"]),
					new DoubleTag("", $data["z"])
				]),
				new ListTag("Motion", [
					new DoubleTag("", 0),
					new DoubleTag("", 0),
					new DoubleTag("", 0)
				]),
				new ListTag("Rotation", [
					new FloatTag("", $data["yaw"]),
					new FloatTag("", 0)
				]),
			]);

			$painting = new PaintingEntity($player->getLevel(), $nbt);
			$painting->spawnToAll();
			unset($player->getLevel()->updateEntities[$painting->getId()]);

			if ($player->isSurvival()){
				$item = $player->getInventory()->getItemInHand();
				$count = $item->getCount();
				if (--$count <= 0){
					$player->getInventory()->setItemInHand(Item::get(Item::AIR));
				}

				$item->setCount($count);
				$player->getInventory()->setItemInHand($item);
			}

			return true;
		}

		return false;
	}

}