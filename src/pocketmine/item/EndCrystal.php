<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
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
use pocketmine\entity\EnderCrystal;
use pocketmine\entity\Entity;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;

class EndCrystal extends Item{
	public function __construct(int $meta = 0){
		parent::__construct(self::END_CRYSTAL, $meta, "End Crystal");
	}

	public function onActivate(Level $level, Player $player, Block $block, Block $target, int $face, Vector3 $facePos): bool{
		$nbt = new CompoundTag("", [
			new ListTag("Pos", [
				new DoubleTag("", $block->getX() + 0.5),
				new DoubleTag("", $block->getY()),
				new DoubleTag("", $block->getZ() + 0.5)
			]),
			new ListTag("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			new ListTag("Rotation", [
				new FloatTag("", lcg_value() * 360),
				new FloatTag("", 0)
			]),
		]);

		if ($this->hasCustomName()){
			$nbt->CustomName = new StringTag("CustomName", $this->getCustomName());
		}

		$entity = Entity::createEntity(EnderCrystal::NETWORK_ID, $level, $nbt);

		if ($entity instanceof Entity){
			if ($player->isSurvival()){
				--$this->count;
			}
			$entity->spawnToAll();
			return true;
		}

		return false;
	}
}
