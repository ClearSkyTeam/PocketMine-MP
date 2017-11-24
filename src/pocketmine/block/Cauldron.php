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

namespace pocketmine\block;

use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\Potion;
use pocketmine\item\Tool;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\tile\Cauldron as TileCauldron;
use pocketmine\tile\Tile;
use pocketmine\utils\Color;

class Cauldron extends Solid{

	protected $id = self::CAULDRON_BLOCK;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness(): float{
		return 2.0;
	}

	public function getName(): string{
		return "Cauldron";
	}

	public function canBeActivated(): bool{
		return true;
	}

	public function getToolType(): int{
		return Tool::TYPE_PICKAXE;
	}

	public function place(Item $item, Block $block, Block $target, int $face, Vector3 $facePos, Player $player = null): bool{
		$nbt = Tile::createNBT($this);

		if ($item->hasCustomBlockData()){
			foreach ($item->getCustomBlockData() as $key => $v){
				$nbt->{$key} = $v;
			}
		}

		Tile::createTile(Tile::CAULDRON, $this->getLevel(), $nbt);
		$this->getLevel()->setBlock($block, $this, true, true);
		return true;
	}

	public function getDrops(Item $item): array{
		if ($item->isPickaxe() >= 1){
			return [
				[ItemIds::CAULDRON, 0, 1]
			];
		}
		return [];
	}

	public function onActivate(Item $item, Player $player = null): bool{//TODO: make checks check for potion type
		$tile = $this->getLevel()->getTile($this);
		if (!($tile instanceof TileCauldron)){
			return false;
		}
		switch ($item->getId()){
			case ItemIds::BUCKET:
				if ($item->getDamage() === 0){//empty bucket
					if (!$this->isFull() or $tile->hasCustomColor() or $tile->hasPotion()){
						break;
					}
					$bucket = clone $item;
					$bucket->setDamage(8);//water bucket
					Server::getInstance()->getPluginManager()->callEvent($ev = new PlayerBucketFillEvent($player, $this, 0, $item, $bucket));
					if (!$ev->isCancelled()){
						if ($player->isSurvival()){
							$player->getInventory()->setItemInHand($ev->getItem());
						}
						$this->meta = 0;//empty
						$this->getLevel()->setBlock($this, $this, true);
						$tile->clearCustomColor();
						$ev = new LevelEventPacket();
						$ev->data = 0;
						$ev->evid = LevelEventPacket::EVENT_CAULDRON_TAKE_WATER;
						$ev->position = $this->add(0.5, 0, 0.5);
						$this->getLevel()->addChunkPacket($this->x >> 4, $this->z >> 4, $ev);
					}
				} elseif ($item->getDamage() === 8){//water bucket
					if ($this->isFull() and !$tile->hasCustomColor() and !$tile->hasPotion()){
						break;
					}
					$bucket = clone $item;
					$bucket->setDamage(0);//empty bucket
					Server::getInstance()->getPluginManager()->callEvent($ev = new PlayerBucketEmptyEvent($player, $this, 0, $item, $bucket));
					if (!$ev->isCancelled()){
						if ($player->isSurvival()){
							$player->getInventory()->setItemInHand($ev->getItem());
						}
						if ($tile->hasPotion()){//if has potion
							$this->meta = 0;//empty
							$tile->setPotionId(-1);//reset potion
							$tile->setSplashPotion(false);
							$tile->clearCustomColor();
							$this->getLevel()->setBlock($this, $this, true);
							$ev = new LevelEventPacket();
							$ev->data = 0;
							$ev->evid = LevelEventPacket::EVENT_CAULDRON_EXPLODE;
							$ev->position = $this->add(0.5, 0, 0.5);
							$this->getLevel()->addChunkPacket($this->x >> 4, $this->z >> 4, $ev);
						} else{
							$this->meta = 6;//fill
							$tile->clearCustomColor();
							$this->getLevel()->setBlock($this, $this, true);
							$ev = new LevelEventPacket();
							$ev->data = 0;
							$ev->evid = LevelEventPacket::EVENT_CAULDRON_FILL_WATER;
							$ev->position = $this->add(0.5, 0, 0.5);
							$this->getLevel()->addChunkPacket($this->x >> 4, $this->z >> 4, $ev);
						}
						$this->update();
					}
				}
				break;
			case Item::DYE://TODO: can't put colors in old ones
				if ($tile->hasPotion()) break;
				$color = Color::getDyeColor($item->getDamage());
				if ($tile->hasCustomColor()){
					$color = Color::averageColor($color, $tile->getCustomColor());
				}
				if ($player->isSurvival()){
					$item->setCount($item->getCount() - 1);
					/*if($item->getCount() <= 0){
						$player->getInventory()->setItemInHand(Item::get(Item::AIR));
					}*/
				}
				$tile->setCustomColor($color);
				$ev = new LevelEventPacket();
				$ev->data = $color->toRGB();
				$ev->evid = LevelEventPacket::EVENT_CAULDRON_ADD_DYE;
				$ev->position = $this->add(0.5, 0, 0.5);
				$this->getLevel()->addChunkPacket($this->x >> 4, $this->z >> 4, $ev);
				$this->update();
				break;
			case Item::LEATHER_CAP:
			case Item::LEATHER_TUNIC:
			case Item::LEATHER_PANTS:
			case Item::LEATHER_BOOTS:
				/** @var Armor $item */
				if ($this->isEmpty()) break;
				if ($tile->hasPotion()) break;
				if ($tile->hasCustomColor()){
					--$this->meta;
					$this->getLevel()->setBlock($this, $this, true);
					$newItem = clone $item;
					/** @var Armor $newItem */
					$newItem->setCustomColor($tile->getCustomColor());
					$player->getInventory()->setItemInHand($newItem);
					$ev = new LevelEventPacket();
					$color = $tile->getCustomColor();
					$ev->data = $color->toRGB();
					$ev->evid = LevelEventPacket::EVENT_CAULDRON_DYE_ARMOR;
					$ev->position = $this->add(0.5, 0, 0.5);
					$this->getLevel()->addChunkPacket($this->x >> 4, $this->z >> 4, $ev);
					if ($this->isEmpty()){
						$tile->clearCustomColor();
					}
				} else{
					--$this->meta;
					$this->getLevel()->setBlock($this, $this, true);
					$newItem = clone $item;
					/** @var Armor $newItem */
					$newItem->clearNamedTag();
					if($item->hasCustomName())
						$newItem->setCustomName($item->getCustomName());
					$player->getInventory()->setItemInHand($newItem);
					$ev = new LevelEventPacket();
					$color = $item->getCustomColor();
					$ev->data = $color->toRGB();
					$ev->evid = LevelEventPacket::EVENT_CAULDRON_CLEAN_ARMOR;
					$ev->position = $this->add(0.5, 0, 0.5);
					$this->getLevel()->addChunkPacket($this->x >> 4, $this->z >> 4, $ev);
				}
				break;
			case Item::POTION:
			case Item::SPLASH_POTION:
				if (!$this->isEmpty() and (($tile->getPotionId() !== $item->getDamage() and $item->getDamage() !== Potion::WATER_BOTTLE) or
						($item->getId() === Item::POTION and $tile->getSplashPotion()) or
						($item->getId() === Item::SPLASH_POTION and !$tile->getSplashPotion()) and $item->getDamage() !== 0 or
						($item->getDamage() === Potion::WATER_BOTTLE and $tile->hasPotion()))
				){
					$this->meta = 0x00;
					$this->getLevel()->setBlock($this, $this, true);
					$tile->setPotionId(-1);//reset
					$tile->setSplashPotion(false);
					$tile->clearCustomColor();
					if ($player->isSurvival()){
						$player->getInventory()->setItemInHand(Item::get(Item::GLASS_BOTTLE));
					}
					$ev = new LevelEventPacket();
					$ev->data = 0;
					$ev->evid = LevelEventPacket::EVENT_CAULDRON_EXPLODE;
					$ev->position = $this->add(0.5, 0, 0.5);
					$this->getLevel()->addChunkPacket($this->x >> 4, $this->z >> 4, $ev);
				} elseif ($item->getDamage() === Potion::WATER_BOTTLE){
					$this->meta += 2;
					if ($this->meta > 0x06) $this->meta = 0x06;
					$this->getLevel()->setBlock($this, $this, true);
					if ($player->isSurvival()){
						$player->getInventory()->setItemInHand(Item::get(Item::GLASS_BOTTLE));
					}
					$tile->setPotionId(0);
					$tile->setSplashPotion(false);
					$tile->clearCustomColor();
					$ev = new LevelEventPacket();
					$ev->data = 0;
					$ev->evid = LevelEventPacket::EVENT_CAULDRON_FILL_POTION;
					$ev->position = $this->add(0.5, 0, 0.5);
					$this->getLevel()->addChunkPacket($this->x >> 4, $this->z >> 4, $ev);
				} elseif (!$this->isFull()){
					$this->meta += 2;
					if ($this->meta > 0x06) $this->meta = 0x06;
					$tile->setPotionId($item->getDamage());
					$tile->setSplashPotion($item->getId() === Item::SPLASH_POTION);
					$tile->clearCustomColor();
					$this->getLevel()->setBlock($this, $this, true);
					if ($player->isSurvival()){
						$player->getInventory()->setItemInHand(Item::get(Item::GLASS_BOTTLE));
					}
					$color = Potion::getColor($item->getDamage());
					$ev = new LevelEventPacket();
					$color = new Color($color[0], $color[1], $color[2]);
					$ev->data = $color->toRGB();
					$ev->evid = LevelEventPacket::EVENT_CAULDRON_FILL_POTION;
					$ev->position = $this->add(0.5, 0, 0.5);
					$this->getLevel()->addChunkPacket($this->x >> 4, $this->z >> 4, $ev);
				}
				break;
			case Item::GLASS_BOTTLE:
				if ($this->meta < 2){
					break;
				}
				if ($tile->hasPotion()){
					$this->meta -= 2;
					if ($tile->getSplashPotion() === true){
						$result = Item::get(Item::SPLASH_POTION, $tile->getPotionId());
					} else{
						$result = Item::get(Item::POTION, $tile->getPotionId());
					}
					if ($this->isEmpty()){
						$tile->setPotionId(-1);//reset
						$tile->setSplashPotion(false);
						$tile->clearCustomColor();
					}
					$this->getLevel()->setBlock($this, $this, true);
					$this->addItem($item, $player, $result);
					$color = Potion::getColor($result->getDamage());
					$color = new Color($color[0], $color[1], $color[2]);
					$ev = new LevelEventPacket();
					$ev->data = $color->toRGB();
					$ev->evid = LevelEventPacket::EVENT_CAULDRON_FILL_POTION;
					$ev->position = $this->add(0.5, 0, 0.5);
					$this->getLevel()->addChunkPacket($this->x >> 4, $this->z >> 4, $ev);
				} else{
					$this->meta -= 2;
					$this->getLevel()->setBlock($this, $this, true);
					if ($player->isSurvival()){
						$result = Item::get(Item::POTION, Potion::WATER_BOTTLE);
						$this->addItem($item, $player, $result);
					}
					$ev = new LevelEventPacket();
					$color = $tile->getCustomColor();
					$ev->data = $color->toRGB();
					$ev->evid = LevelEventPacket::EVENT_CAULDRON_TAKE_POTION;
					$ev->position = $this->add(0.5, 0, 0.5);
					$this->getLevel()->addChunkPacket($this->x >> 4, $this->z >> 4, $ev);
				}
				break;
		}
		return true;
	}

	public function isFull(){
		return $this->meta === 0x06;
	}

	public function update(){//umm... right update method...?
		$this->getLevel()->setBlock($this, Block::get($this->id, $this->meta + 1), true);
		$this->getLevel()->setBlock($this, $this, true);//Undo the damage value
	}

	public function isEmpty(){
		return $this->meta === 0x00;
	}

	public function addItem(Item $item, Player $player, Item $result){
		if ($item->getCount() <= 1){
			$player->getInventory()->setItemInHand($result);
		} else{
			$item->setCount($item->getCount() - 1);
			if ($player->getInventory()->canAddItem($result) === true){
				$player->getInventory()->addItem($result);
			} else{
				$motion = $player->getDirectionVector()->multiply(0.4);
				$position = clone $player->getPosition();
				$player->getLevel()->dropItem($position->add(0, 0.5, 0), $result, $motion, 40);
			}
		}
	}
}