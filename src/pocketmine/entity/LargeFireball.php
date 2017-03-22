<?php
namespace pocketmine\entity;

use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class LargeFireball extends Projectile {
	const NETWORK_ID = 85;

	public $height = 1.5;
	public $width = 1.25;
	public $lenght = 0.906;//TODO

	public function initEntity(){
		parent::initEntity();
	}

	public function getName(){
		return "LargeFireball";
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
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}