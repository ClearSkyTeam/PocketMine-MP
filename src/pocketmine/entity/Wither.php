<?php
namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Wither extends Monster implements ProjectileSource{
    const NETWORK_ID = 52;

    public $height = 2;
    public $width = 3;
    public $lenght = 1;//TODO: check
	
	protected $exp_min = 20;
	protected $exp_max = 20;
	protected $maxHealth = 600;

    public function initEntity(){
        parent::initEntity();
    }

 	public function getName(){
        return "Wither Boss";
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
		return [ItemItem::get(ItemItem::NETHER_STAR)];
    }
}
