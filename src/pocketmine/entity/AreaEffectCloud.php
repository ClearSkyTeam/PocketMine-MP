<?php
namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\item\Potion;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class AreaEffectCloud extends Entity {
	const NETWORK_ID = 95;

	public $width = 5;
	public $length = 5;
	public $height = 1;

	private $PotionId = 0;
	private $Radius = 3;
	private $RadiusOnUse = -0.5;
	private $RadiusPerTick = -0.005;
	private $WaitTime = 10;
	private $TileX = 0;
	private $TileY = 0;
	private $TileZ = 0;
	private $Duration = 600;
	private $DurationOnUse = 0;

	public function initEntity() {
		parent::initEntity();

		if (!isset($this->namedtag->PotionId) or !($this->namedtag->PotionId instanceof ShortTag)) {
			$this->namedtag->PotionId = new ShortTag("PotionId", $this->PotionId);
		}
		$this->PotionId = $this->namedtag->PotionId->getValue();

		if (!isset($this->namedtag->Radius) or !($this->namedtag->Radius instanceof FloatTag)) {
			$this->namedtag->Radius = new FloatTag("Radius", $this->Radius);
		}
		$this->Radius = $this->namedtag->Radius->getValue();

		if (!isset($this->namedtag->RadiusOnUse) or !($this->namedtag->RadiusOnUse instanceof FloatTag)) {
			$this->namedtag->RadiusOnUse = new FloatTag("RadiusOnUse", $this->RadiusOnUse);
		}
		$this->RadiusOnUse = $this->namedtag->RadiusOnUse->getValue();

		if (!isset($this->namedtag->RadiusPerTick) or !($this->namedtag->RadiusPerTick instanceof FloatTag)) {
			$this->namedtag->RadiusPerTick = new FloatTag("RadiusPerTick", $this->RadiusPerTick);
		}
		$this->RadiusPerTick = $this->namedtag->RadiusPerTick->getValue();

		if (!isset($this->namedtag->WaitTime) or !($this->namedtag->WaitTime instanceof IntTag)) {
			$this->namedtag->WaitTime = new IntTag("WaitTime", $this->WaitTime);
		}
		$this->WaitTime = $this->namedtag->WaitTime->getValue();

		if (!isset($this->namedtag->TileX) or !($this->namedtag->TileX instanceof IntTag)) {
			$this->namedtag->TileX = new IntTag("TileX", (int)round($this->getX()));
		}
		$this->TileX = $this->namedtag->TileX->getValue();

		if (!isset($this->namedtag->TileY) or !($this->namedtag->TileY instanceof IntTag)) {
			$this->namedtag->TileY = new IntTag("TileY", (int)round($this->getY()));
		}
		$this->TileY = $this->namedtag->TileY->getValue();

		if (!isset($this->namedtag->TileZ) or !($this->namedtag->TileZ instanceof IntTag)) {
			$this->namedtag->TileZ = new IntTag("TileZ", (int)round($this->getZ()));
		}
		$this->TileZ = $this->namedtag->TileZ->getValue();

		if (!isset($this->namedtag->Duration) or !($this->namedtag->Duration instanceof IntTag)) {
			$this->namedtag->Duration = new IntTag("Duration", $this->Duration);
		}
		$this->Duration = $this->namedtag->Duration->getValue();

		if (!isset($this->namedtag->DurationOnUse) or !($this->namedtag->DurationOnUse instanceof IntTag)) {
			$this->namedtag->DurationOnUse = new IntTag("DurationOnUse", $this->DurationOnUse);
		}
		$this->DurationOnUse = $this->namedtag->DurationOnUse->getValue();

		$this->setDataProperty(self::DATA_POTION_AMBIENT, self::DATA_TYPE_SHORT, $this->PotionId);
		$this->setDataFlag(self::DATA_BOUNDING_BOX_HEIGHT, self::DATA_TYPE_FLOAT, $this->height);
	}

	public function onUpdate($currentTick) {
		if ($this->closed) {
			return false;
		}

		$this->timings->startTiming();

		$hasUpdate = parent::onUpdate($currentTick);

		if ($this->age > $this->Duration || $this->PotionId == 0 || $this->Radius <= 0) {
			$this->close();
			$hasUpdate = true;
		} else {
			$potion = ItemItem::get(ItemItem::POTION, $this->PotionId);
			if ($potion instanceof Potion) ;//blamephpstorm
			$effects = $potion->getAdditionalEffects();
			if (empty($effects) || !$effects[0] instanceof Effect) {
				$this->close();
				$this->timings->stopTiming();
				return true;
			}
			$effect = $effects[0];
			if ($effect instanceof Effect) ;//blamephpstorm
			$this->Radius += $this->RadiusPerTick;
			$this->setDataFlag(self::DATA_BOUNDING_BOX_WIDTH, self::DATA_TYPE_FLOAT, $this->Radius * 2);
			if ($this->WaitTime > 0) {
				$this->WaitTime--;
				$this->timings->stopTiming();
				return true;
			}
			$effect->setDuration($this->DurationOnUse + 20);//would do nothing at 0
			$bb = new AxisAlignedBB($this->x - $this->Radius, $this->y, $this->z - $this->Radius, $this->x + $this->Radius, $this->y + $this->height, $this->z + $this->Radius);
			$used = false;
			foreach ($this->getLevel()->getCollidingEntities($bb, $this) as $collidingEntity) {
				if ($collidingEntity instanceof Living && $collidingEntity->distance($this) <= $this->Radius) {
					$used = true;
					$collidingEntity->addEffect($effect);
				}
			}
			if ($used) {
				$this->Duration -= $this->DurationOnUse;
				$this->WaitTime = 10;
			}
		}

		$this->timings->stopTiming();

		return $hasUpdate;
	}

	public function getName() {
		return "Area Effect Cloud";
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
		$pk->metadata = $this->dataProperties;//data color
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}