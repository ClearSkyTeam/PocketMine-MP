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

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;
use pocketmine\tile\BrewingStand as TileBrewingStand;
use pocketmine\tile\Tile;

class BrewingStand extends Transparent{

	protected $id = self::BREWING_STAND_BLOCK;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function canBeActivated(){
		return true;
	}

	public function getName(): string{
		return "Brewing Stand";
	}

	public function getHardness(): float{
		return 0.5;
	}

	public function getResistance(): float{
		return 2.5;
	}

	public function getLightLevel(): int{
		return 1;
	}

	public function onActivate(Item $item, Player $player = null): bool{
		if ($player instanceof Player){
			//TODO lock
			$t = $this->getLevel()->getTile($this);
			if ($t instanceof TileBrewingStand){
				$brewingStand = $t;
			} else{
				$brewingStand = Tile::createTile(Tile::BREWING_STAND, $this->getLevel(), Tile::createNBT($this));
			}
			$player->addWindow($brewingStand->getInventory());
		}
		return true;
	}

	public function getDrops(Item $item): array{
		$drops = [];
		if ($item->isPickaxe() >= Tool::TIER_WOODEN){
			$drops[] = [Item::BREWING_STAND, 0, 1];
		}
		return $drops;
	}

	/*public function collidesWithBB(AxisAlignedBB $bb, &$list = []){
		if($bb->intersectsWith($bb2 = AxisAlignedBB::getBoundingBoxFromPool(
			$this->x,
			$this->y,
			$this->z,
			$this->x + 1,
			$this->y + 0.125,
			$this->z + 1
		))){
			$list[] = $bb2;
		}
	}*/

	public function getToolType(): int{
		return Tool::TYPE_PICKAXE;
	}

	public function getVariantBitmask(): int{
		return 0;
	}

	protected function recalculateBoundingBox(): ?AxisAlignedBB{
		$thin = new AxisAlignedBB(
			$this->x + 0.4375,
			$this->y,
			$this->z + 0.4375,
			$this->x + 0.5625,
			$this->y + 0.875,
			$this->z + 0.5625
		);
		return $thin;
	}
}