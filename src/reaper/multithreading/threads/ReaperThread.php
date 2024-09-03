<?php

namespace pocketmine\reaper\multithreading\threads;

use pmmp\thread\ThreadSafeArray;
use pocketmine\reaper\multithreading\operation\ThreadOperation;
use pocketmine\snooze\SleeperHandlerEntry;
use pocketmine\thread\Thread;

class ReaperThread extends Thread{
	public function __construct(
		private SleeperHandlerEntry $sleeperEntry,
		private ThreadSafeArray $in,
		private ThreadSafeArray $out
	){ }

	protected function onRun() : void{
		$notifier = $this->sleeperEntry->createNotifier();
		while(!$this->isKilled){
			while(is_string(($raw = $this->in->shift()))){
				$operation = igbinary_unserialize($raw);

				if($operation instanceof ThreadOperation){
					$data = $operation->run();

					$this->out[] = igbinary_serialize([$operation->getId(), $data]);
					$notifier->wakeupSleeper();
				}
			}
		}
	}
}