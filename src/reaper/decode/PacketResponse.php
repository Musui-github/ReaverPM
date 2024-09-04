<?php

namespace pocketmine\reaper\decode;

use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\ServerboundPacket;

class PacketResponse{
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