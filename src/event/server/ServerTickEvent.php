<?php

namespace pocketmine\event\server;

class ServerTickEvent extends ServerEvent{
	public function __construct(protected int $tick){ }

	/**
	 * @return int
	 */
	public function getTick() : int{
		return $this->tick;
	}
}