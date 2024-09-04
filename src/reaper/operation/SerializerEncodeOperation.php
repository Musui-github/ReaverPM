<?php

namespace pocketmine\reaper\operation;

use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\reaper\multithreading\operation\ThreadOperation;
use pocketmine\reaper\response\encode\SerializerResponse;

class SerializerEncodeOperation extends ThreadOperation{
	/**
	 * @param ClientboundPacket[] $packets
	 * @param \Closure            $closure
	 */
	public function __construct(
		protected array $packets,
		\Closure $closure
	){ parent::__construct($closure); }

	public function run() : array{
		$result = [];
		foreach($this->packets as $packet) {
			$serializer = PacketSerializer::encoder();
			$packet->encode($serializer);
			$result[] = new SerializerResponse($serializer, $packet);
		}

		return $result;
	}
}