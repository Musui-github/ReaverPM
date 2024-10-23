<?php

namespace pocketmine\reaper\operation;

use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\network\PacketHandlingException;
use pocketmine\reaper\multithreading\operation\ThreadOperation;

class SerializerDecodeOperation extends ThreadOperation{
	public function __construct(
		protected ServerboundPacket $packet,
		protected string $buffer,
		\Closure $closure
	){ parent::__construct($closure); }

	public function run() : SerializerResponse{
		$stream = PacketSerializer::decoder($this->buffer, 0);
		try{
			$this->packet->decode($stream);
		}catch(PacketDecodeException $e){
			throw PacketHandlingException::wrap($e);
		}
		if(!$stream->feof()){
			$remains = substr($stream->getBuffer(), $stream->getOffset());
		}
		return new SerializerResponse($this->packet, $stream);
	}
}