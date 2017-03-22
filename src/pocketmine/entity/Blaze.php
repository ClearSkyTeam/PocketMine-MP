<?php
namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Blaze extends Monster{
	const NETWORK_ID = 43;

	public $height = 1.5;
	public $width = 1.25;
	public $lenght = 0.906;
	
	protected $exp_min = 10;
	protected $exp_max = 10;
	protected $maxHealth = 20;

	public function initEntity(){
		parent::initEntity();
	}

	public function getName(){
		return "Blaze";
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
			ItemItem::get(ItemItem::BLAZE_ROD, 0, mt_rand(0, 1))
		];
	}
}