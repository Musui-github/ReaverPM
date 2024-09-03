<?php

namespace pocketmine\reaper\decode;

use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\ServerboundPacket;

class PacketDecodingResponse{
	/**
	 * @var PacketResponse[]
	 */
	protected array $results = [];

	public function add(Packet $packet, string $buffer) : void{
		$this->results[] = new PacketResponse($packet, $buffer);
	}

	/**
	 * @return array
	 */
	public function getResults() : array{
		return $this->results;
	}
}