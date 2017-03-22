<?php

namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Sheep extends Animal implements Colorable{
	const NETWORK_ID = 13;
	public $lenght = 1.484;
	public $width = 0.719;
	public $height = 1.406;
	protected $exp_min = 1;
	protected $exp_max = 3;
	protected $maxHealth = 8;

	public function initEntity(){
		parent::initEntity();
		
		if(!isset($this->namedtag->Color) || $this->getVariant() > 15){
			$this->setVariant(mt_rand(0, 15));
		}
		$this->setDataProperty(16, self::DATA_TYPE_BYTE, $this->getVariant());
	}

	public function getName(){
		return "Sheep";
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

	public function setVariant($value){
		$this->namedtag->Color = new IntTag("Color", $value);
		$this->setDataProperty(16, self::DATA_TYPE_BYTE, $value);
	}

	public function getVariant(){
		return $this->namedtag["Color"];
	}

	public function getDrops(){
		return [ItemItem::get(ItemItem::WOOL, $this->getVariant(), 1)];
	}

	public function sheer(){
		for($i = 0; $i <= mt_rand(0, 2); $i++){
			$this->getLevel()->dropItem($this, new ItemItem(ItemItem::WOOL, $this->getVariant()));//TODO: check amount
		}
	}
}