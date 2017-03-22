<?php
namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class CaveSpider extends Monster{
	const NETWORK_ID = 40;

	public $width = 1.438;
	public $length = 1.188;
	public $height = 0.547;
	
	protected $exp_min = 5;
	protected $exp_max = 5;
	protected $maxHealth = 12;

	public function initEntity(){
		parent::initEntity();
	}

	public function getName(){
		return "Cave Spider";
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
		return[
			ItemItem::get(ItemItem::STRING, 0, mt_rand(0, 2)),
			ItemItem::get(ItemItem::SPIDER_EYE, 0, mt_rand(0, 1))
		];
	 }
  	
}
