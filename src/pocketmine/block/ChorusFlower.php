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

use pocketmine\block\ChorusPlant;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\item\Item;
use pocketmine\item\enchantment\enchantment;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;

class ChorusFlower extends Transparent{

    protected $id = self::CHORUS_FLOWER;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness(){
		return 0.4;
	}

    public function getBlastResistance(){
        return 2;
    }

    public function getToolType(){
        return Tool::TYPE_AXE;
    }

    public function getName(){
        return "Chorus Flower";
    }

	public function getDrops(Item $item){
        return [[Item::CHORUS_FLOWER, $this->meta === 0x05?5:0, 1]];
	}

	public function canBeActivated(){
		return true;
	}

	public function onActivate(Item $item, Player $player = null){
		if($item->isHoe()){
			$item->useOn($this);
			$this->onUpdate(Level::BLOCK_UPDATE_RANDOM);

			return true;
		}

		return false;
	}

	public function onUpdate($type){
		print 'Got update: '.$type.PHP_EOL;
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->shouldBreak()){
				print 'invalid, break it'.PHP_EOL;
				$this->getLevel()->useBreakOn($this);
			}
		}
		if($type === Level::BLOCK_UPDATE_RANDOM){
			if($this->meta === 5){
				print 'is 5 return'.PHP_EOL;
				return;
			}
			if($this->shouldBreak()){
				print 'invalid, break it/let it die'.PHP_EOL;
				$this->setDamage(5);
				#$this->getLevel()->setBlock($this, $this);
			}
			$to = $this->canSpreadTo();
			if(!empty($to)){
			$block = $to[array_rand($to)];
				Server::getInstance()->getPluginManager()->callEvent($ev = new BlockSpreadEvent($block, $this, new ChorusFlower()));
				if(!$ev->isCancelled()){
					$this->getLevel()->setBlock($this, new ChorusPlant());
					$this->getLevel()->setBlock($block, $ev->getNewState());
				}
			}else{
				$this->setDamage(5);
				$this->getLevel()->setBlock($this, $this);
			}
		}
	}

    public function canSpreadTo(){
		$freespace = [];
		if(($below = $this->getSide(Vector3::SIDE_DOWN)) instanceof ChorusPlant && $below->countHorizontalStems() > 0 || $below->getId() === self::END_STONE){
			//it has a stem next to it so MUST spead up
			$freespace[] = $this->getSide(Vector3::SIDE_UP);
		}else{
			for($side = 1;$side<=5;$side++){
				$block = $this->getSide($side);
				$canGoHere = true;
				for($sideCheck = 2;$sideCheck<=5;$sideCheck++){
					if(!in_array($block->getSide($sideCheck)->getId(), [self::AIR, self::CHORUS_FLOWER])){//flower, its not yet replaced
						$canGoHere = false;
						var_dump($canGoHere);
					}
				}
				if($canGoHere) $freespace[] = $block;
			}
		}
		var_dump($freespace);
		return $freespace;
	}

    public function hasValidStem(Vector3 $target){
		if(in_array($this->getSide(Vector3::SIDE_DOWN)->getId(), [self::CHORUS_PLANT,self::END_STONE])) {
			print 'has valid below'.PHP_EOL;
			return true;
		}
		foreach([Vector3::SIDE_NORTH,Vector3::SIDE_SOUTH,Vector3::SIDE_WEST,Vector3::SIDE_EAST] as $side){
			print 'Testing side '.$side.PHP_EOL;
			if($this->getSide($side)->getId() === self::CHORUS_PLANT){
				print 'has valid next to it'.PHP_EOL;
				return true;
			}
		}
		return false;
	}

	public function shouldBreak(){
		if($this->hasValidStem($this)) {
			print 'flower has valid stem'.PHP_EOL;
			return false;
		}
		#print 'test side shit'.PHP_EOL;
		/*
		$tobreak = false;
		foreach([Vector3::SIDE_NORTH,Vector3::SIDE_SOUTH,Vector3::SIDE_WEST,Vector3::SIDE_EAST] as $side){
			if($this->getSide($side)->getId() === self::CHORUS_PLANT && $this->getSide(Vector3::SIDE_DOWN)->getSide($side)->getId() === self::CHORUS_PLANT){
				if($this->getId() === self::CHORUS_FLOWER){
					$tobreak = true;
				}
				else{
					$this->getLevel()->useBreakOn($this->getSide($side));
					$this->getLevel()->useBreakOn($this->getSide(Vector3::SIDE_DOWN));
					$this->tobreak = true;
				}
			}
		}
		return $tobreak;*/
		return true;
	}
}
#Server::getInstance()->getPluginManager()->callEvent($ev = new BlockSpreadEvent($block, $this, new ChorusFlower()));
#if(!$ev->isCancelled()){
#	$this->getLevel()->setBlock($block, $ev->getNewState());
#}