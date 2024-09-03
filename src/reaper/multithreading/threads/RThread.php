<?php

namespace pocketmine\reaper\multithreading\threads;

use pmmp\thread\ThreadSafeArray;
use pocketmine\reaper\multithreading\operation\ThreadOperation;
use pocketmine\snooze\SleeperHandlerEntry;

class RThread{
	protected ReaperThread $thread;

	public function __construct(
		private string $threadId,
		private SleeperHandlerEntry $sleeperEntry,
		private ThreadSafeArray $in,
		private ThreadSafeArray $out
	){ }

	/**
	 * @return string
	 */
	public function getThreadId() : string{
		return $this->threadId;
	}

	/**
	 * @return SleeperHandlerEntry
	 */
	public function getSleeperEntry() : SleeperHandlerEntry{
		return $this->sleeperEntry;
	}

	/**
	 * @return ThreadSafeArray
	 */
	public function getIn() : ThreadSafeArray{
		return $this->in;
	}

	/**
	 * @return ThreadSafeArray
	 */
	public function getOut() : ThreadSafeArray{
		return $this->out;
	}

	public function start() : void{
		$this->thread = new ReaperThread($this->sleeperEntry, $this->in, $this->out);
		$this->thread->start();
	}

	public function stop() : void{
		$this->thread->quit();
	}

	public function addOperation(ThreadOperation $operation) : void{
		$this->out[] = igbinary_serialize($operation);
	}
}