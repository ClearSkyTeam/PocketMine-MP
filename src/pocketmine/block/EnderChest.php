<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\block;

use pocketmine\inventory\EnderChestInventory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\EnderChest as TileEnderChest;
use pocketmine\tile\Tile;

class EnderChest extends Transparent{

    protected $id = self::ENDER_CHEST;

    public function __construct($meta = 0){
        $this->meta = $meta;
    }

    public function canBeActivated(){
        return true;
    }

    public function getHardness(){
        return 22.5;
    }

    public function getResistance(){
        return 3000;
    }

    public function getLightLevel(){
        return 7;
    }

    public function getName(){
        return "Ender Chest";
    }

    public function getToolType(){
        return Tool::TYPE_PICKAXE;
    }

    protected function recalculateBoundingBox(){
        return new AxisAlignedBB(
            $this->x + 0.0625,
            $this->y,
            $this->z + 0.0625,
            $this->x + 0.9375,
            $this->y + 0.9475,
            $this->z + 0.9375
        );
    }

    public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
        $faces = [
            0 => 4,
            1 => 2,
            2 => 5,
            3 => 3,
        ];

        $chest = null;
        $this->meta = $faces[$player instanceof Player ? $player->getDirection() : 0];


        $this->getLevel()->setBlock($block, $this, true, true);
        $nbt = new CompoundTag("", [
            new StringTag("id", Tile::ENDER_CHEST),
            new IntTag("x", $this->x),
            new IntTag("y", $this->y),
            new IntTag("z", $this->z)
        ]);

        if($item->hasCustomName()){
            $nbt->CustomName = new StringTag("CustomName", $item->getCustomName());
        }

        Tile::createTile(Tile::ENDER_CHEST, $this->getLevel(), $nbt);

        return true;
    }

    public function onActivate(Item $item, Player $player = null){
        if($player instanceof Player){
            $top = $this->getSide(1);
            if($top->isTransparent() !== true){
                return true;
            }

            $t = $this->getLevel()->getTile($this);
            $chest = null;
            if($t instanceof TileEnderChest){
                $chest = $t;
            }else{
                $nbt = new CompoundTag("", [
                    new StringTag("id", Tile::ENDER_CHEST),
                    new IntTag("x", $this->x),
                    new IntTag("y", $this->y),
                    new IntTag("z", $this->z)
                ]);
                $chest = Tile::createTile(Tile::ENDER_CHEST, $this->getLevel(), $nbt);
            }

            if($chest instanceof TileEnderChest){
                $player->addWindow(new EnderChestInventory($this, $player));
            }
        }

        return true;
    }

    public function getDrops(Item $item){
        if ($item->hasEnchantments() && $item->getEnchantment(Enchantment::TYPE_MINING_SILK_TOUCH) !== null){
            return [
                [$this->id, 0, 1],
            ];
        }
        return [
            [Item::OBSIDIAN, 0, 8],
        ];
    }
}