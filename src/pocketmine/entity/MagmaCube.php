<?php
namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class MagmaCube extends Living{
	const NETWORK_ID = 42;
    const DATA_SIZE = 16;

	public $width = 2;
	public $length = 2;
	public $height = 2;//TODO: Size
	
	protected $exp_min = 1;
	protected $exp_max = 1; //TODO: Size
	protected $maxHealth = 1; //TODO Size

	public function initEntity(){
		parent::initEntity();
		if(!isset($this->namedtag->Size)){
			$this->setSize(mt_rand(0, 3));
		}
	}

	public function getName(){
		return "Magma Cube";
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
	
	//TODO: Stop lava/fire damage

	public function getDrops(){
		return [
			ItemItem::get(ItemItem::MAGMA_CREAM, 0, mt_rand(0, 2))
		];
	}

    public function setSize($value){
        $this->namedtag->Size = new IntTag("Size", $value);//TODO: check if isset
		$this->setDataProperty(self::DATA_SIZE, self::DATA_TYPE_BYTE, $value);
    }

    public function getSize(){
        return $this->namedtag["Size"];
    }
}
