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

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\Chest as TileChest;
use pocketmine\tile\Tile;

class StickyPiston extends Piston {

	protected $id = self::STICKY_PISTON;

	public function __construct($meta = 0) {
		$this->meta = $meta;
	}

	public function getName() {
		return "Sticky Piston";
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($player instanceof Player){
			$pitch = $player->getPitch();
			if(abs($pitch) >= 45){
				if($pitch < 0) $f = 0;
				else $f = 1;
			}
			else
				$f = $player->getDirection() + 2;
		}
		else
			$f = 0;//#TODO: fix direction if used piston:meta
		$faces = [0 => 0, 1 => 1, 2 => 5, 3 => 3, 4 => 4, 5 => 2];
		$this->meta = $faces[$f];
		$this->getLevel()->setBlock($block, $this, true, true);
		$nbt = new CompoundTag("", [
			new StringTag("id", Tile::PISTON),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z),
			new ByteTag("isMovable", (bool) true),
			new ByteTag("Sticky", (bool) true),
			new ByteTag("State", 0),
			new FloatTag("Progress", 0.0),
			new ByteTag("NewState", 0),
			new FloatTag("LastProgress", 0.0),
			new CompoundTag("BreakBlocks", []),
			new CompoundTag("AttachedBlocks", [])
		]);

		Tile::createTile(Tile::PISTON, $this->getLevel(), $nbt);

		return true;
	}
}