<?php
namespace pocketmine\entity;

use pocketmine\block\Block;
use pocketmine\item\Item as ItemItem;
use pocketmine\item\Potion;
use pocketmine\level\Level;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\ItemBreakParticle;
use pocketmine\level\particle\SpellParticle;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class LingeringPotion extends Projectile {
	const NETWORK_ID = 101;
	const DATA_POTION_ID = 16;//TODO: update this
	public $width = 0.25;
	public $length = 0.25;
	public $height = 0.25;
	protected $gravity = 0.1;
	protected $drag = 0.05;

	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null) {
		if (!isset($nbt->PotionId)) {
			$nbt->PotionId = new ShortTag("PotionId", Potion::AWKWARD);
		}
		parent::__construct($level, $nbt, $shootingEntity);
		unset($this->dataProperties[self::DATA_SHOOTER_ID]);
		$this->setDataProperty(self::DATA_VARIANT, self::DATA_TYPE_SHORT, $this->getPotionId());
	}

	public function getPotionId() {
		return (int)$this->namedtag["PotionId"];
	}

	public function kill() {
		if ($this->isAlive()) {
			#$color = Potion::getColor($this->getPotionId());
			$this->getLevel()->addParticle(new ItemBreakParticle($this, ItemItem::get(ItemItem::LINGERING_POTION)));
			$this->getLevel()->addParticle(new DestroyBlockParticle($this, Block::get(Block::GLASS)));//hack
			#$this->getLevel()->addParticle(new GenericParticle($this, Particle::TYPE_MOB_SPELL_INSTANTANEOUS, ((255 & 0xff) << 24) | (($color[0] & 0xff) << 16) | (($color[1] & 0xff) << 8) | ($color[2] & 0xff)));

			$aec = null;
			$level = $this->chunk;

			if (!($level instanceof Level)) {
				return false;
			}

			$nbt = new CompoundTag("", [
				"Pos" => new ListTag("Pos", [
					new DoubleTag("", $this->getX()),
					new DoubleTag("", $this->getY()),
					new DoubleTag("", $this->getZ())
				]),
				"Motion" => new ListTag("Motion", [
					new DoubleTag("", 0),
					new DoubleTag("", 0),
					new DoubleTag("", 0)
				]),
				"Rotation" => new ListTag("Rotation", [
					new FloatTag("", 0),
					new FloatTag("", 0)
				])
			]);
			$nbt->Age = new IntTag("Age", 0);
			$nbt->PotionId = new ShortTag("PotionId", $this->getPotionId());
			$nbt->Radius = new FloatTag("Radius", 3);
			$nbt->RadiusOnUse = new FloatTag("RadiusOnUse", -0.5);
			$nbt->RadiusPerTick = new FloatTag("RadiusPerTick", -0.005);
			$nbt->WaitTime = new IntTag("WaitTime", 10);
			$nbt->TileX = new IntTag("TileX", (int) round($this->getX()));
			$nbt->TileY = new IntTag("TileY", (int) round($this->getY()));
			$nbt->TileZ = new IntTag("TileZ", (int) round($this->getZ()));
			$nbt->Duration = new IntTag("Duration", 600);
			$nbt->DurationOnUse = new IntTag("DurationOnUse", 0);

			$aec = Entity::createEntity("AreaEffectCloud", $level, $nbt);
			if ($aec instanceof Entity) {
				$aec->spawnToAll();
			}
		}
		parent::kill();
	}

	public function onUpdate($currentTick) {
		if ($this->closed) {
			return false;
		}
		$this->timings->startTiming();
		$hasUpdate = parent::onUpdate($currentTick);
		$this->age++;
		if ($this->age > 1200 or $this->isCollided) {
			$this->kill();
			$this->close();
			$hasUpdate = true;
		}
		$this->timings->stopTiming();
		return $hasUpdate;
	}

	public function spawnTo(Player $player) {
		$pk = new AddEntityPacket();
		$pk->type = self::NETWORK_ID;
		$pk->eid = $this->getId();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}
