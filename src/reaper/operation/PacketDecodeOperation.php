<?php

namespace pocketmine\reaper\operation;

use pocketmine\network\mcpe\compression\Compressor;
use pocketmine\network\mcpe\compression\DecompressionException;
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\network\mcpe\protocol\types\CompressionAlgorithm;
use pocketmine\network\PacketHandlingException;
use pocketmine\reaper\multithreading\operation\ThreadOperation;
use pocketmine\utils\BinaryDataException;
use pocketmine\utils\BinaryStream;

class PacketDecodeOperation extends ThreadOperation{
	public function __construct(
		protected string $payload,
		protected bool $compressed,
		protected Compressor $compressor,
		\Closure $closure
	){ parent::__construct($closure); }

	public function run() : PacketDecodingResponse|PacketHandlingException{
		if(strlen($this->payload) < 1){
			return new PacketHandlingException("No bytes in payload");
		}
		if(strlen($this->payload) > 50000) {
			return new PacketHandlingException("Too many bytes in payload");
		}

		if($this->compressed) {
			$compressionType = ord($this->payload[0]);
			$compressed = substr($this->payload, 1);
			if($compressionType === CompressionAlgorithm::NONE) {
				return $this->decompress($compressed);
			} elseif($compressionType === $this->compressor->getNetworkId()) {
				return $this->decompress($compressed, true);
			} else return new PacketHandlingException("Packet compressed with unexpected compression type $compressionType");
		}

		return $this->decompress($this->payload);
	}

	public function decompress(string $decompressed, bool $requireAlgo = false) : PacketDecodingResponse|PacketHandlingException{
		if($requireAlgo) {
			$compressed = $decompressed;
			try {
				$decompressed = $this->compressor->decompress($compressed);
			} catch(DecompressionException $e) {
				return PacketHandlingException::wrap($e, "Compressed packet batch decode error");
			}
		}

		if(strlen($decompressed) > 1000000) {
			return new PacketHandlingException("Too many bytes in decompressed payload");
		}

		try{
			$stream = new BinaryStream($decompressed);
			$response = new PacketDecodingResponse();
			foreach(PacketBatch::decodeRaw($stream) as $buffer){
				$packet = PacketPool::getInstance()->getPacket($buffer);
				if(is_null($packet)){
					return new PacketHandlingException("Unknown packet received");
				}

				switch($packet->pid()) {
					case ProtocolInfo::LOGIN_PACKET:
					case ProtocolInfo::PLAYER_SKIN_PACKET:
						break;
					default:
						if(($c = strlen($buffer)) >= 15000) {
							if($c > 50000) {
								return new PacketHandlingException("Too many bytes in packet buffer");
							}
							continue 2;
						}
						break;
				}

				$response->add($packet, $buffer);
			}

			return $response;
		}catch(PacketDecodeException|BinaryDataException $e){
			return PacketHandlingException::wrap($e, "Packet batch decode error");
		}
	}
}