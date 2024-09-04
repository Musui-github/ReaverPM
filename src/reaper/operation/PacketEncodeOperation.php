<?php

namespace pocketmine\reaper\operation;

use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\reaper\multithreading\operation\ThreadOperation;
use pocketmine\utils\BinaryStream;

class PacketEncodeOperation extends ThreadOperation{
	/**
	 * @param string[] $packets
	 * @param \Closure            $closure
	 */
	public function __construct(
		protected array $packets,
		\Closure $closure
	){ parent::__construct($closure); }

	public function run() : BinaryStream{
		$stream = new BinaryStream();
		PacketBatch::encodeRaw($stream, $this->packets);
		return $stream;
	}
}