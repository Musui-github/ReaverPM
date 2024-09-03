<?php

namespace pocketmine\reaper\multithreading;

use pmmp\thread\ThreadSafeArray;
use pocketmine\reaper\multithreading\storage\ClosureStorage;
use pocketmine\reaper\multithreading\threads\RThread;
use pocketmine\Server;
use pocketmine\snooze\SleeperHandlerEntry;
use pocketmine\utils\SingletonTrait;

class MultiThreading{
	use SingletonTrait;

	/**
	 * @var RThread[]
	 */
	protected array $threads = [];

	public function __construct(){ self::setInstance($this); }

	/**
	 * @param string $threadId
	 *
	 * @return void
	 */
	public function create(string $threadId) : void{
		$out = new ThreadSafeArray();
		$in = new ThreadSafeArray();

		$sleeperEntry = Server::getInstance()->getTickSleeper()->addNotifier(function () use($threadId) {
			if(!isset($this->threads[$threadId])) return;
			$thread = $this->threads[$threadId];
			while(($raw = $thread->getIn()->shift())){
				$response = igbinary_unserialize($raw);
				$identifier = $response[0];
				$data = $response[1];

				ClosureStorage::executeClosure($identifier, $data);
			}
		});

		$thread = new RThread($threadId, $sleeperEntry, $in, $out);
		$thread->start();
		$this->threads[$thread->getThreadId()] = $thread;
	}

	/**
	 * @param string $threadId
	 *
	 * @return void
	 */
	public function exit(string $threadId) : void{
		if(!isset($this->threads[$threadId])) return;
		$thread = $this->threads[$threadId];
		$sleeperId = $thread->getSleeperEntry()->getNotifierId();
		Server::getInstance()->getTickSleeper()->removeNotifier($sleeperId);
	}

	/**
	 * @param string $threadId
	 *
	 * @return RThread|null
	 */
	public function get(string $threadId) : ?RThread{
		return $this->threads[$threadId] ?? null;
	}
}