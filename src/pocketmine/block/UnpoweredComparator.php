<?php

/*
 *
 *  ____			_		_   __  __ _				  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___	  |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|	 |_|  |_|_|
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
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\Tile;

class UnpoweredComparator extends Transparent {

	protected $id = self::UNPOWERED_COMPARATOR_BLOCK;

	public function __construct($meta = 0) {
		$this->meta = $meta;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null) {
		if (!$this->canStay()) return false;
		$faces = [
			2 => 3,
			3 => 0,
			0 => 1,
			1 => 2,
		];
		$this->meta = $faces[$player != null ? $player->getDirection() : $this->meta];
		$this->getLevel()->setBlock($block, $this, true, true);
		$nbt = new CompoundTag("", [
			new StringTag("id", Tile::COMPARATOR),
			new IntTag("x", $this->x),
			new IntTag("y", $this->y),
			new IntTag("z", $this->z),
			new IntTag("OutputSignal", 0)
		]);
		Tile::createTile(Tile::COMPARATOR, $this->getLevel(), $nbt);
		return true;
	}

	public function canBeActivated() {
		return true;
	}

	public function getName() {
		return "Comparator";
	}

	public function onActivate(Item $item, Player $player = null){
		$this->meta += 4;
		$this->meta = $this->meta & 7;
		$this->getLevel()->setBlock($this, $this);
		return true;
	}

	public function onUpdate($type) {
		if (!$this->canStay()) $this->getLevel()->useBreakOn($this);
		return $type;
	}

	public function getDrops(Item $item) {
		return [[Item::COMPARATOR, 0, 1]];
	}

	private function canStay() {
		if ($this->getSide(0)->isTransparent())
			return ((in_array($this->getSide(0)->getId(), [Item::SLAB, Item::SLAB2, Item::WOODEN_SLAB]) && (($this->getSide(0)->getDamage() & 0x08) > 0)) || ($this->getSide(0) instanceof Stair && (($this->getSide(0)->getDamage() & 0x04) > 0)));
		return true;
	}
}