<?php

namespace pocketmine\reaper\response\decode;

use pocketmine\network\mcpe\protocol\Packet;

class PacketDecodeResponse{
	public function __construct(
		protected Packet $packet,
		protected string $buffer
	){ }

	/**
	 * @return Packet
	 */
	public function getPacket() : Packet{
		return $this->packet;
	}

	/**
	 * @return string
	 */
	public function getBuffer() : string{
		return $this->buffer;
	}
}