<?php

namespace pocketmine\reaper\response\encode;

use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

class SerializerResponse{
	public function __construct(
		protected PacketSerializer $serializer,
		protected ClientboundPacket $packet,
	){ }

	/**
	 * @return PacketSerializer
	 */
	public function getSerializer() : PacketSerializer{
		return $this->serializer;
	}

	/**
	 * @return ClientboundPacket
	 */
	public function getPacket() : ClientboundPacket{
		return $this->packet;
	}
}