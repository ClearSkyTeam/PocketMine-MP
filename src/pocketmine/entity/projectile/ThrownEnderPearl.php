<?php

namespace pocketmine\entity;

use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\Particle;
use pocketmine\level\sound\GenericSound;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;

class ThrownEnderPearl extends Projectile{
	const NETWORK_ID = 87;

	public $width = 0.25;
	public $length = 0.25;
	public $height = 0.25;

	protected $gravity = 0.03;
	protected $drag = 0.01;

	public function onUpdate(int $currentTick): bool{
		if ($this->closed){
			return false;
		}

		$this->timings->startTiming();

		$hasUpdate = parent::onUpdate($currentTick);

		if ($this->isCollided && $this->getOwningEntity() !== null && $this->getOwningEntity() instanceof Player){
			$this->getLevel()->getServer()->getPluginManager()->callEvent($ev = new EntityTeleportEvent($this->getOwningEntity(), $this->getOwningEntity()->getPosition(), $this->getPosition()));
			if (!$ev->isCancelled()){
				$this->getLevel()->getServer()->getPluginManager()->callEvent($dev = new EntityDamageEvent($this->getOwningEntity(), EntityDamageEvent::CAUSE_FALL, 5));
				if (!$dev->isCancelled()){
					$this->getOwningEntity()->attack($dev);
				}
				$this->getOwningEntity()->teleport($ev->getTo(), $this->getOwningEntity()->getYaw(), $this->getOwningEntity()->getPitch());
				$this->getLevel()->addSound(new GenericSound($ev->getFrom(), LevelEventPacket::EVENT_SOUND_ENDERMAN_TELEPORT));
				$this->getLevel()->addSound(new GenericSound($ev->getTo(), LevelEventPacket::EVENT_SOUND_ENDERMAN_TELEPORT));
				$this->getLevel()->addParticle(new GenericParticle($ev->getFrom(), Particle::TYPE_PORTAL));
				$this->getLevel()->addParticle(new GenericParticle($ev->getTo(), Particle::TYPE_PORTAL));
			}
		}

		$this->timings->stopTiming();

		return $hasUpdate;
	}
}