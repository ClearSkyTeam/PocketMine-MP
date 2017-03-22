<?php
namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class ElderGuardian extends Guardian{
	const NETWORK_ID = 50;

	public $width = 0.75;
	public $length = 0.75;
	public $height = 1;
	
	protected $exp_min = 10;
	protected $exp_max = 10;
	protected $maxHealth = 80;

	public function initEntity(){
		parent::initEntity();
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ELDER, true, self::DATA_TYPE_BYTE);
	}

	public function getName(){
		return "Elder Guardian";
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
		$drops = [ItemItem::get(ItemItem::PRISMARINE_SHARD, 0, mt_rand(0, 2))];
		
		if($this->getLastDamageCause() === EntityDamageEvent::CAUSE_FIRE){//#TODO these are random
			$drops[] = ItemItem::get(ItemItem::COOKED_FISH, 0, mt_rand(0, 1));
		}
		else{
			$drops[] = ItemItem::get(ItemItem::RAW_FISH, 0, mt_rand(0, 1));
		}
		
		$drops[] = ItemItem::get(ItemItem::PRISMARINE_CRYSTAL, 0, mt_rand(0, 100) < 33?1:0);
		
		$drops[] = ItemItem::get(ItemItem::SPONGE, 1);
		
		return $drops;
	}
	
	public function isLeashableType(){
		return false;
	}
}
