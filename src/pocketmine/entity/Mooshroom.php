<?php
namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Mooshroom extends Animal{
	const NETWORK_ID = 16;

	public $height = 1.875;
	public $width = 0.891;
	public $lenght = 1.781;
	
	protected $exp_min = 1;
	protected $exp_max = 3;
	protected $maxHealth = 10;

	public function initEntity(){
		parent::initEntity();
	}

	public function getName(){
		return "Mooshroom";
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
		$drops = [
			ItemItem::get(ItemItem::LEATHER, 0, mt_rand(0, 2))
		];

		if($this->getLastDamageCause() === EntityDamageEvent::CAUSE_FIRE){
			$drops[] = ItemItem::get(ItemItem::COOKED_BEEF, 0, mt_rand(1, 3));
		}else{
			$drops[] = ItemItem::get(ItemItem::RAW_BEEF, 0, mt_rand(1, 3));
		}
		return $drops;
	}

	public function sheer(){
		for($i = 0; $i <= mt_rand(0, 2); $i++){
			$this->getLevel()->dropItem($this, new ItemItem(ItemItem::RED_MUSHROOM));//TODO: check amount, remove Mooshroom and make cow
		}
	}
}