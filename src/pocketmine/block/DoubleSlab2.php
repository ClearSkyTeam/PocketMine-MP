<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\block;

use pocketmine\item\Item;

class DoubleSlab2 extends DoubleSlab{

    const RED_SANDSTONE = 1;
    const PURPUR = 1;

    protected $id = Block::DOUBLE_SLAB2;

    public function __construct($meta = 0){
        $this->meta = $meta;
    }

	public function getName(){
        static $names = [
            self::RED_SANDSTONE => "Double Red Sandstone Slab",
            self::PURPUR => "Double Purpur Slab",
        ];
        return $names[$this->meta & 0x0f];
    }

	public function getDrops(Item $item) : array {
		if($item->isPickaxe() >= 1){
			return [
				[Item::SLAB2, $this->meta, 2],
			];
		}else{
			return [];
		}
	}
}