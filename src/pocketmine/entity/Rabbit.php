<?php
namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Rabbit extends Animal{
    const NETWORK_ID = 18;

    const TYPE_BROWN = 0;
    const TYPE_BLACK = 1;
    const TYPE_ALBINO = 2;
    const TYPE_SPOTTED = 3;
    const TYPE_SALT_PEPPER = 4;
    const TYPE_GOLDEN = 5;

    public $height = 0.5;
    public $width = 0.5;
    public $lenght = 0.5;
	
	protected $exp_min = 1;
	protected $exp_max = 3;
	protected $maxHealth = 3;

    public function initEntity(){
        parent::initEntity();
        
        if(!isset($this->namedtag->Type) || $this->getVariant() > 5){
            $this->setVariant(mt_rand(0, 5));
        }
		$this->setDataProperty(18, self::DATA_TYPE_BYTE, $this->getVariant());
    }

    public function getName(){
        return "Rabbit";
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

    public function setVariant($type){
        $this->namedtag->Type = new IntTag("Type", $type);
		$this->setDataProperty(18, self::DATA_TYPE_BYTE, $type);
    }

    public function getVariant(){
        return $this->namedtag["Type"];
    }

    public function getDrops(){
        $drops = [ItemItem::get(ItemItem::RABBIT_HIDE, 0, mt_rand(0, 2))];

        if($this->getLastDamageCause() === EntityDamageEvent::CAUSE_FIRE){
            $drops[] = ItemItem::get(ItemItem::COOKED_RABBIT, 0, mt_rand(1, 2));
        }else{
            $drops[] = ItemItem::get(ItemItem::RAW_RABBIT, 0, mt_rand(1, 2));
        }

        return $drops;
    }


}