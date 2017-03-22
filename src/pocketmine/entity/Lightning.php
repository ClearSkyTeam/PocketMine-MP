<?php
namespace pocketmine\entity;

use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Lightning extends Entity{
	const NETWORK_ID = 93;

	public $width = 0;
	public $length = 0;//TODO
	public $height = 0;

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
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}

	public function onUpdate($currentTick) {
		$this->close();//prevent them from staying
	}
}