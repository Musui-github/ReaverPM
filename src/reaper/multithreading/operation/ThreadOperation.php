<?php

namespace pocketmine\reaper\multithreading\operation;

use pocketmine\reaper\multithreading\storage\ClosureStorage;

abstract class ThreadOperation{
	protected string $id;

	public function __construct(\Closure $closure){ ClosureStorage::addClosure($this->id = uniqid(), $closure); }

	/**
	 * @return string
	 */
	public function getId() : string{
		return $this->id;
	}

	abstract public function run();
}