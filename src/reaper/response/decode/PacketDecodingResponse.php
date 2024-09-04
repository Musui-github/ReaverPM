<?php

namespace pocketmine\reaper\response\decode;

use pocketmine\network\mcpe\protocol\Packet;

class PacketDecodingResponse{
	/**
	 * @var PacketDecodeResponse[]
	 */
	protected array $results = [];

	public function add(Packet $packet, string $buffer) : void{
		$this->results[] = new PacketDecodeResponse($packet, $buffer);
	}

	/**
	 * @return array
	 */
	public function getResults() : array{
		return $this->results;
	}
}