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

use pocketmine\inventory\EnderChestInventory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\EnderChest as TileEnderChest;
use pocketmine\tile\Tile;

class EnderChest extends Transparent{

	protected $id = self::ENDER_CHEST;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function canBeActivated(){
		return true;
	}

	public function getHardness(): float{
		return 22.5;
	}

	public function getResistance(): float{
		return 3000.0;
	}

	public function getLightLevel(): int{
		return 7;
	}

	public function getName(): string{
		return "Ender Chest";
	}

	public function getToolType(): int{
		return Tool::TYPE_PICKAXE;
	}

	protected function recalculateBoundingBox():?AxisAlignedBB{
		return new AxisAlignedBB(
			$this->x + 0.0625,
			$this->y,
			$this->z + 0.0625,
			$this->x + 0.9375,
			$this->y + 0.9475,
			$this->z + 0.9375
		);
	}

	public function place(Item $item, Block $block, Block $target, int $face, Vector3 $facePos, Player $player = null): bool{
		$faces = [
			0 => 4,
			1 => 2,
			2 => 5,
			3 => 3,
		];

		$chest = null;
		$this->meta = $faces[$player instanceof Player ? $player->getDirection() : 0];


		$this->getLevel()->setBlock($block, $this, true, true);
		Tile::createTile(Tile::ENDER_CHEST, $this->getLevel(), TileEnderChest::createNBT($this, $face, $item, $player));

		return true;
	}

	public function onActivate(Item $item, Player $player = null): bool{
		if ($player instanceof Player){
			$top = $this->getSide(1);
			if ($top->isTransparent() !== true){
				return true;
			}

			if (!$this->getLevel()->getTile($this) instanceof TileEnderChest){
				Tile::createTile(Tile::ENDER_CHEST, $this->getLevel(), TileEnderChest::createNBT($this));
			}

			$player->addWindow(new EnderChestInventory($this, $player));
		}

		return true;
	}

	public function getDrops(Item $item): array{
		if ($item->hasEnchantments() && $item->getEnchantment(Enchantment::SILK_TOUCH) !== null){
			return [
				[$this->id, 0, 1],
			];
		}
		return [
			[Item::OBSIDIAN, 0, 8],
		];
	}
}