<?php

namespace pocketmine\reaper\response\decode;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\ServerboundPacket;

class SerializerDecodeResponse{
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