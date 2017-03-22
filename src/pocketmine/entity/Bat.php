<?php
namespace pocketmine\entity;

use pocketmine\nbt\tag\IntTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Bat extends Animal{
	const NETWORK_ID = 19;

	public $width = 0.469;
	public $length = 0.484;
	public $height = 0.5;

	public static $range = 16;
	public static $speed = 0.25;
	public static $jump = 1.8;
	public static $mindist = 3;
	protected $maxHealth = 6;

	public function initEntity(){
		parent::initEntity();
		/*for($i = 1; $i < 40; $i++){
			$this->setDataProperty($i, self::DATA_TYPE_BYTE, 1);
		}*/
	}

	public function getName(){
		return "Bat";
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
	
	public function setVariant($type){
		$this->namedtag->Variant = new IntTag("Variant", $type);
		$this->setDataProperty(16, self::DATA_TYPE_BYTE, $type);
	}

	public function getVariant(){
		return $this->namedtag["Variant"];
	}

}
