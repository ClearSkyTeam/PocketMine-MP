<?php

namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class MinecartTNT extends Snake{
	const NETWORK_ID = 97;
	public $height = 0.9;
	public $width = 1.1;
	protected $maxHealth = 4;
	public $drag = 0.1;
	public $gravity = 0.5;
	public $isMoving = false;
	public $moveSpeed = 0.4;
	public $isFreeMoving = false;
	public $isLinked = false;

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
		return "Minecart TNT";
	}

	public function getDrops(){
		return [ItemItem::get(ItemItem::MINECART, 0, 1),ItemItem::get(ItemItem::TNT, 0, 1)];
	}
}
