<?php
namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\level\Level;
use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\Particle;
use pocketmine\level\sound\GenericSound;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\LevelEventPacket;
use pocketmine\Player;

class ThrownEnderPearl extends Projectile {
	const NETWORK_ID = 87;

	public $width = 0.25;
	public $length = 0.25;
	public $height = 0.25;

	protected $gravity = 0.03;
	protected $drag = 0.01;

	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null) {
		parent::__construct($level, $nbt, $shootingEntity);
	}

	public function onUpdate($currentTick) {
		if ($this->closed) {
			return false;
		}

		$this->timings->startTiming();

		$hasUpdate = parent::onUpdate($currentTick);

		if ($this->isCollided && $this->shootingEntity !== null && $this->shootingEntity instanceof Player) {
			$this->getLevel()->getServer()->getPluginManager()->callEvent($ev = new EntityTeleportEvent($this->shootingEntity, $this->shootingEntity->getPosition(), $this->getPosition()));
			if (!$ev->isCancelled()) {
				$this->getLevel()->getServer()->getPluginManager()->callEvent($dev = new EntityDamageEvent($this->shootingEntity, EntityDamageEvent::CAUSE_FALL, 5));
				if (!$dev->isCancelled()) {
					$this->shootingEntity->attack($dev->getFinalDamage(), $dev);
				}
				$this->shootingEntity->teleport($ev->getTo(), $this->shootingEntity->getYaw(), $this->shootingEntity->getPitch());
				$this->getLevel()->addSound(new GenericSound($ev->getFrom(), LevelEventPacket::EVENT_SOUND_ENDERMAN_TELEPORT));
				$this->getLevel()->addSound(new GenericSound($ev->getTo(), LevelEventPacket::EVENT_SOUND_ENDERMAN_TELEPORT));
				$this->getLevel()->addParticle(new GenericParticle($ev->getFrom(), Particle::TYPE_PORTAL));
				$this->getLevel()->addParticle(new GenericParticle($ev->getTo(), Particle::TYPE_PORTAL));
			}
		}
		if ($this->age > 1200 || $this->isCollided) {
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