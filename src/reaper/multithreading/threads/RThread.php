<?php

namespace pocketmine\reaper\multithreading\threads;

use pmmp\thread\ThreadSafeArray;
use pocketmine\reaper\multithreading\operation\ThreadOperation;
use pocketmine\reaper\multithreading\storage\ClosureStorage;
use pocketmine\Server;

class RThread{
	protected ReaperThread $thread;
	protected int $sleeperId;
	protected ThreadSafeArray $in;
	protected ThreadSafeArray $out;

	public function __construct(
		private readonly string $threadId,
	){ }

	/**
	 * @return string
	 */
	public function getThreadId() : string{
		return $this->threadId;
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

	/**
	 * @return int
	 */
	public function getSleeperId() : int{
		return $this->sleeperId;
	}

	public function start() : void{
		$this->in = new ThreadSafeArray();
		$this->out = new ThreadSafeArray();

		$sleeperEntry = Server::getInstance()->getTickSleeper()->addNotifier($this->onMessageFromThread(...));
		$this->sleeperId = $sleeperEntry->getNotifierId();

		$thread = new ReaperThread($sleeperEntry, $this->out, $this->in);
		$thread->start();
		$this->thread = $thread;
	}

	public function stop() : void{
		$this->thread->quit();
	}

	public function onMessageFromThread(): void {
		while(($raw = $this->in->shift())){
			$response = igbinary_unserialize($raw);
			$identifier = $response[0];
			$data = $response[1];

			ClosureStorage::executeClosure($identifier, $data);
		}
	}

	public function addOperation(ThreadOperation $operation) : void{
		$this->out[] = igbinary_serialize($operation);
	}
}