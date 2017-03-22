<?php
namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class EnderSignal extends Entity{
	const NETWORK_ID = 70;

	public $width = 0;
	public $length = 0;
	public $height = 0;

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
	}

	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}

		$this->timings->startTiming();

		$hasUpdate = parent::onUpdate($currentTick);

		if($this->age > 50){
			$this->kill();
			$hasUpdate = true;
			$this->getLevel()->dropItem($this, ItemItem::get(ItemItem::EYE_OF_ENDER), $this);
		}

		$this->timings->stopTiming();

		return $hasUpdate;
	}

	public function getName(){
		return "Eye of Ender";
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
}