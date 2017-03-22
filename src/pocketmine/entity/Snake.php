<?php

namespace pocketmine\entity;

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

use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\protocol\AnimatePacket;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\Server;

class Snake extends Entity{
	// Have fun guessing why
	protected $attackTime = 0;

	public function attack($damage, EntityDamageEvent $source){
		if($this->attackTime > 0 or $this->noDamageTicks > 0){
			$lastCause = $this->getLastDamageCause();
			if($lastCause !== null and $lastCause->getDamage() >= $damage){
				$source->setCancelled();
			}
		}
		
		parent::attack($damage, $source);
		
		if($source->isCancelled()){
			return;
		}
		
		if($source instanceof EntityDamageByEntityEvent){
			$e = $source->getDamager();
			if($source instanceof EntityDamageByChildEntityEvent){
				$e = $source->getChild();
			}
			
			if($e->isOnFire() > 0){
				$this->setOnFire(2 * $this->server->getDifficulty());
			}
		}
		$pk = new EntityEventPacket();
		$pk->eid = $this->getId();
		$pk->event = $this->getHealth() <= 0?EntityEventPacket::DEATH_ANIMATION:EntityEventPacket::HURT_ANIMATION; // Ouch!
		Server::getInstance()->broadcastPacket($this->hasSpawned, $pk);
		// TESTING
		for($i = 0; $i < 400; $i++){
			$pk = new AnimatePacket();
			$pk->eid = $this->getId();
			$pk->action = $i;
			Server::getInstance()->broadcastPacket($this->hasSpawned, $pk);
		}
		// END TESTING
		
		$this->attackTime = 0; // 0.5 seconds cooldown
	}

	public function kill(){
		if(!$this->isAlive()){
			return;
		}
		parent::kill();
		foreach($this->getDrops() as $item){
			$this->getLevel()->dropItem($this, $item);
		}
		if(!$this->closed){
			$this->close();
		}
	}

	public function getDrops(){
		return [];
	}
}