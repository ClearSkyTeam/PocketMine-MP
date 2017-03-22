<?php
namespace pocketmine\entity;

use pocketmine\nbt\tag\IntTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class ZombieVillager extends Zombie{
	const NETWORK_ID = 44;
	
	public $width = 1.031;
	public $length = 0.891;
	public $height = 2.125;
	protected $maxHealth = 20;

	public function initEntity(){
		parent::initEntity();
		if(!isset($this->namedtag->Profession) || $this->getVariant() > 4){
			$this->setVariant(mt_rand(0, 4));
		}
		$this->setDataProperty(16, self::DATA_TYPE_BYTE, $this->getVariant());
	}

	public function getName(){
		return "Zombie Villager";
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
	

	/**
	 * Sets the zombievillager profession
	 *
	 * @param $profession
	 */
	public function setVariant($type){
		$this->namedtag->Profession = new IntTag("Profession", $type);
		$this->setDataProperty(16, self::DATA_TYPE_BYTE, $type);
	}

	public function getVariant(){
		return $this->namedtag["Profession"];
	}

}
