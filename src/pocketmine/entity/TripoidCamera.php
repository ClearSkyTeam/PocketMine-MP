<?php
namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class TripoidCamera extends Snake{
    const NETWORK_ID = 62;

    public $height = 1;
    public $width = 1;
    public $lenght = 1;//TODO: Size
	protected $maxHealth = 1;
	
	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
	}

    public function initEntity(){
        parent::initEntity();
    }

    public function getName(){
        return "Tripoid Camera";
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
        return [ItemItem::get(ItemItem::CAMERA, 0, 1)];
    }
}
