<?php

namespace pocketmine\reaper\operation;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\ServerboundPacket;

class SerializerResponse{
	public function __construct(
		protected ServerboundPacket $packet,
		protected PacketSerializer $stream
	){ }

	/**
	 * @return ServerboundPacket
	 */
	public function getPacket() : ServerboundPacket{
		return $this->packet;
	}

	/**
	 * @return PacketSerializer
	 */
	public function getStream() : PacketSerializer{
		return $this->stream;
	}
}