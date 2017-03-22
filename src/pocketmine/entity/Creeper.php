<?php
namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Creeper extends Monster implements Explosive{
    const NETWORK_ID = 33;
	
	protected $exp_min = 5;
	protected $exp_max = 5;
	protected $maxHealth = 20;

    public function initEntity(){
        parent::initEntity();

        if(!isset($this->namedtag->Powered)){
            $this->setPowered(0);
        }
    }

    public function getName(){
        return "Creeper";
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

    public function explode(){
        //TODO: CreeperExplodeEvent
    }

    public function setPowered($value){
        $this->namedtag->Powered = new IntTag("Powered", $value);
		$this->setDataProperty(19, self::DATA_TYPE_BYTE, $value);
    }

    public function isPowered(){
        return $this->namedtag["Powered"];
    }

    public function getDrops(){
        $drops = [];
        if($this->lastDamageCause instanceof EntityDamageByEntityEvent and $this->lastDamageCause->getEntity() instanceof Player){
            $drops = [
                ItemItem::get(ItemItem::GUNPOWDER, 0, mt_rand(0, 2))
            ];
        }

        if($this->lastDamageCause instanceof EntityExplodeEvent and $this->lastDamageCause->getEntity() instanceof Creeper && $this->lastDamageCause->getEntity()->isPowered()){
            $drops = [
                ItemItem::get(ItemItem::SKULL, 4, 1)
            ];
        }

        return $drops;
    }
}
