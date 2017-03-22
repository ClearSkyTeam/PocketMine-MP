<?php
namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class PolarBear extends Monster{
	const NETWORK_ID = 28;

	public $width = 1.031;
	public $length = 0.891;
	public $height = 2;
	
	protected $exp_min = 1;
	protected $exp_max = 3;
	protected $maxHealth = 30;

	public function initEntity(){
		parent::initEntity();
	}

	public function getName(){
		return "Polar Bear";
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
		$drops = [mt_rand(0, 3) == 0?ItemItem::get(ItemItem::RAW_FISH):ItemItem::get(ItemItem::RAW_SALMON)];
		
		return $drops;
	}
}
