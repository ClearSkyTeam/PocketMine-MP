<?php
namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Ghast extends Monster{
	const NETWORK_ID = 41;

	public $width = 4.5;
	public $length = 4.5;
	public $height = 4.5;

 	public static $range = 16;
	public static $speed = 0.25;
	public static $jump = 1.8;
	public static $mindist = 3;
	
	protected $exp_min = 5;
	protected $exp_max = 5;
	protected $maxHealth = 10;

	public function initEntity(){
		parent::initEntity();
	}

	public function getName(){
		return "Ghast";
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
		return [
			ItemItem::get(ItemItem::GHAST_TEAR, 0, mt_rand(0, 1)),
			ItemItem::get(ItemItem::GUNPOWDER, 0, mt_rand(0, 2))
		];
	}

}
