<?php

namespace pocketmine\reaper\multithreading\threads;

use pmmp\thread\ThreadSafeArray;
use pocketmine\reaper\multithreading\MultiThreading;
use pocketmine\reaper\multithreading\operation\ThreadOperation;
use pocketmine\reaper\multithreading\storage\ClosureStorage;
use pocketmine\Server;
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
		$this->in = new ThreadSafeArray();
		$this->out = new ThreadSafeArray();

		$this->sleeperEntry = Server::getInstance()->getTickSleeper()->addNotifier(function (): void {
			while(($raw = $this->getIn()->shift())){
				$response = igbinary_unserialize($raw);
				$identifier = $response[0];
				$data = $response[1];

				ClosureStorage::executeClosure($identifier, $data);
			}
		});

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