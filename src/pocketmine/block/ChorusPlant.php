<?php

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\item\enchantment\enchantment;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;

class ChorusPlant extends Transparent
{
    protected $id = self::CHORUS_PLANT;

    public function __construct(){}

    public function getHardness()
    {
        return 0.4;
    }

    public function getToolType()
    {
        return Tool::TYPE_AXE;
    }

    public function getName()
    {
        return "Chorus Plant";
    }

    public function getDrops(Item $item){
        return [[Item::CHORUS_FRUIT, 0, mt_rand(0,1)]];
    }

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($this->shouldBreak(false)) {
			print 'You can\'t place there!'.PHP_EOL;
			return false;
		}
		return $this->getLevel()->setBlock($this, $this, true, true);
	}

	public function onUpdate($type){
		print 'Got update: '.$type.PHP_EOL;
		if($type === Level::BLOCK_UPDATE_NORMAL || $type === Level::BLOCK_UPDATE_RANDOM){
			if($this->shouldBreak()){
				print 'invalid, break it'.PHP_EOL;
				$this->getLevel()->scheduleUpdate($this, 20);
			}
		}elseif($type === Level::BLOCK_UPDATE_SCHEDULED){
			$this->getLevel()->useBreakOn($this, $item, null, true);
		}
	}

	public function shouldBreak($checkNeighbours = true){
		$count = $this->countHorizontalStems();
		#test solids
		foreach([Vector3::SIDE_NORTH,Vector3::SIDE_SOUTH,Vector3::SIDE_WEST,Vector3::SIDE_EAST] as $side){
			if($this->getSide($side) instanceof Solid){
				return true;
			}
		}
		if(!in_array($this->getSide(Vector3::SIDE_DOWN)->getId(), [self::CHORUS_PLANT,self::END_STONE])){
			print 'It has no valid stem below!'.PHP_EOL;
			$destroy = true;
			if($count>=1){
				print 'It has connected stems, test neighbours'.PHP_EOL;//check every connected one
				#this could cause an memleak if circle
				$destroy = false;
				if($checkNeighbours){
					foreach([Vector3::SIDE_NORTH,Vector3::SIDE_SOUTH,Vector3::SIDE_WEST,Vector3::SIDE_EAST] as $side){
						if(($stem = $this->getSide($side))->getId() === self::CHORUS_PLANT && $stem->shouldBreak(false)){
							print 'A connected requested destroy'.PHP_EOL;//check every connected one
							$destroy = true;// loop?
						}
					}
				}
			}
				/*
				check this down?
				if($this->getSide(Vector3::SIDE_UP)->getId() === self::CHORUS_PLANT && $this->getSide(Vector3::SIDE_DOWN)->getId() === self::CHORUS_PLANT){
					print 'has some next to it and above and below and side break it'.PHP_EOL;
					return true;
				}
				return false;
			}else{
				return true;
			}*/
			return $destroy;//test this may collide with next case
		}elseif($count >= 1){
			if($this->getSide(Vector3::SIDE_UP)->getId() === self::CHORUS_PLANT){
				print 'has connected and above, destroy'.PHP_EOL;
				return true;
			}elseif(in_array($this->getSide(Vector3::SIDE_DOWN)->getId(), [self::CHORUS_PLANT,self::END_STONE])){
				print 'has some next to it, no plant above'.PHP_EOL;
				if($this->getSide(Vector3::SIDE_DOWN)->getId() === self::AIR){
					print 'its air below'.PHP_EOL;
					#$this->getLevel()->scheduleUpdate($this->getSide(Vector3::SIDE_DOWN), 20);
					$this->getSide(Vector3::SIDE_DOWN)->onUpdate(Level::BLOCK_UPDATE_NORMAL);
					return true;
				}
				return false;
			}
		}
		return false;
	}

    public function countHorizontalStems(){
		$count = 0;
		foreach([Vector3::SIDE_NORTH,Vector3::SIDE_SOUTH,Vector3::SIDE_WEST,Vector3::SIDE_EAST] as $side){
			if($this->getSide($side)->getId() === self::CHORUS_PLANT){
				$count++;
			}
		}
		print 'It has ' . $count . ' connected stems' . PHP_EOL;
		return $count;
	}
}
