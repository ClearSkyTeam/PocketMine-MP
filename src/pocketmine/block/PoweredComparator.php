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
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\Tile;

class PoweredComparator extends Transparent{

	protected $id = self::POWERED_COMPARATOR;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function place(Item $item, Block $block, Block $target, int $face, Vector3 $facePos, Player $player = null): bool{
		if (!$this->canStay()) return false;
		$faces = [
			2 => 3,
			3 => 0,
			0 => 1,
			1 => 2,
		];
		$this->meta = $faces[$player != null ? $player->getDirection() : $this->meta];
		$this->getLevel()->setBlock($block, $this, true, true);
		Tile::createTile(Tile::COMPARATOR, $this->getLevel(), Tile::createNBT($this));
		return true;
	}

	public function canBeActivated(){
		return true;
	}

	public function getName(): string{
		return "Comparator";
	}

	public function onActivate(Item $item, Player $player = null): bool{
		$this->meta += 4;
		$this->meta = $this->meta & 7;
		$this->getLevel()->setBlock($this, $this);
		return true;
	}

	public function onUpdate(int $type){
		if (!$this->canStay()) $this->getLevel()->useBreakOn($this);
		return $type;
	}

	public function getDrops(Item $item): array{
		return [[Item::COMPARATOR, 0, 1]];
	}

	private function canStay(){
		if ($this->getSide(0)->isTransparent())
			return ((in_array($this->getSide(0)->getId(), [Item::STONE_SLAB, Item::STONE_SLAB2, Item::WOODEN_SLAB]) && (($this->getSide(0)->getDamage() & 0x08) > 0)) || ($this->getSide(0) instanceof Stair && (($this->getSide(0)->getDamage() & 0x04) > 0)));
		return true;
	}
}