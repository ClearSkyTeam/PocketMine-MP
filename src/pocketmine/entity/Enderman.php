<?php
namespace pocketmine\entity;

use pocketmine\nbt\tag\IntTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Enderman extends Monster{
	const NETWORK_ID = 38;

	public $height = 2.875;
	public $width = 1.094;
	public $lenght = 0.5;
	
	protected $exp_min = 5;
	protected $exp_max = 5;
	protected $maxHealth = 40;

	public function initEntity(){
		parent::initEntity();
		#for($i = 10; $i < 25; $i++){
		#	$this->setDataProperty($i, self::DATA_TYPE_BYTE, 1);
		#}
		if(!isset($this->namedtag->Angry)){
			$this->setAngry(false);
		}
	}

	public function getName(){
		return "Enderman";
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

	/*public function getDrops(){
		return [
			ItemItem::get(ItemItem::ENDERPEARL, 0, mt_rand(0, 1))
			// holding Block
		];
	}*/
	
	public function setAngry($angry = true){
		$this->namedtag->Angry = new IntTag("Angry", $angry);
		$this->setDataProperty(18, self::DATA_TYPE_BYTE, $angry);
	}

	public function getAngry(){
		return $this->namedtag["Angry"];
	}

}
