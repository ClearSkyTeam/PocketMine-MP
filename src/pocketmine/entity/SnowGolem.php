<?php
namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class SnowGolem extends Animal{
    const NETWORK_ID = 21;

    public $height = 1.875;
    public $width = 1.281;
    public $lenght = 0.688;
	
	protected $exp_min = 0;
	protected $exp_max = 0;
	protected $maxHealth = 4;

    public function initEntity(){
        parent::initEntity();
    }

    public function getName(){
        return "Snow Golem";
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
            ItemItem::get(ItemItem::SNOWBALL, 0, mt_rand(0, 15))
        ];
    }

    public function isLeashableType() {
    	return false;
    }
}