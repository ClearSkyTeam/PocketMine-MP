<?php
namespace pocketmine\entity;

use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Shulker extends Monster{
	const NETWORK_ID = 54;

	public $width = 1;
	public $length = 1;
	public $height = 1;
	
	protected $exp_min = 5;
	protected $exp_max = 5;
	protected $maxHealth = 30;

	public function initEntity(){
		parent::initEntity();
	}

	public function getName(){
		return "Shulker";
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

	public function getDrops(){
		/*$drops = [
			ItemItem::get(ItemItem::SHULKER_SHELL, 0, 1)
		];*/
		$drops = [];

		return $drops;
	}
}
