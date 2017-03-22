<?php
namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Chicken extends Animal{
	const NETWORK_ID = 10;

	public $width = 1;
	public $length = 0.5;
	public $height = 0.8;
	
	protected $exp_min = 1;
	protected $exp_max = 3;
	protected $maxHealth = 4;

	public function initEntity(){
		parent::initEntity();
	}

	public function getName(){
		return "Chicken";
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
		$drops = [ItemItem::get(ItemItem::FEATHER, 0, mt_rand(0, 2))];

		if($this->getLastDamageCause() === EntityDamageEvent::CAUSE_FIRE){
			$drops[] = ItemItem::get(ItemItem::COOKED_CHICKEN, 0, mt_rand(1, 2));
		}else{
			$drops[] = ItemItem::get(ItemItem::RAW_CHICKEN, 0, mt_rand(1, 2));
		}
		return $drops;
	}
}
