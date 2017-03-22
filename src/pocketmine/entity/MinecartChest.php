<?php
namespace pocketmine\entity;

use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class MinecartChest extends Snake{

	const NETWORK_ID = 98;
	protected $maxHealth = 4;
     
    public function initEntity(){
        parent::initEntity();
    }

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = self::NETWORK_ID;
		$pk->eid = $this->getId();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}

    public function getName(){
        return "Minecart Chest";
    }

    public function onUpdate($currentTick){
    	if($this->isAlive()){
    		$this->timings->startTiming();
    		$hasUpdate = false;
    		/*
    		if($this->isLinked() && $this->getlinkedTarget() !== null){
    			$hasUpdate = true;
				$newx = $this->getX() - $this->getlinkedTarget()->getX();
				$newy = $this->getY() - $this->getlinkedTarget()->getY();
				$newz = $this->getZ() - $this->getlinkedTarget()->getZ();
				$this->move($newx, $newy, $newz);
				$this->updateMovement();
			}*/
			if($this->getHealth() < $this->getMaxHealth()){
				$this->heal(0.1, new EntityRegainHealthEvent($this, 0.1, EntityRegainHealthEvent::CAUSE_CUSTOM));
				$hasUpdate = true;
    		}
    			
    		$this->timings->stopTiming();
    
    		return $hasUpdate;
    	}
    }

    public function getDrops(){
        return [ItemItem::get(ItemItem::MINECART, 0, 1),ItemItem::get(ItemItem::CHEST, 0, 1)];
    }
    
    //TODO: Open inventory, add inventory, drop inventory contents
}
