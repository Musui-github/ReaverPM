<?php

namespace pocketmine\reaper\operation;

use Exception;
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\network\PacketHandlingException;
use pocketmine\reaper\multithreading\operation\ThreadOperation;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;

class LoadAsyncConfigOperation extends ThreadOperation{
	protected string $encryptionKey;

	public function __construct(
		protected string $path,
		\Closure $closure
	) {
		$this->encryptionKey = Utils::getMachineUniqueId()->toString();
		parent::__construct($closure);
	}

	public function run() : string|null{
		$content = Filesystem::fileGetContents($this->path);
		if(strlen($content) <= 1) return null;

		$decoded = base64_decode($content);
		$decrypted = $this->decrypt($decoded);

		return gzuncompress($decrypted);
	}

	/**
	 * @param string $data
	 * @return string
	 * @throws Exception
	 */
	private function decrypt(string $data): string
	{
		$key = hash('sha256', $this->encryptionKey, true);
		$iv = substr($data, 0, 16);
		$encryptedData = substr($data, 16);

		$decrypted = openssl_decrypt($encryptedData, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
		if ($decrypted === false) {
			throw new Exception("Decryption failed.");
		}

		return $decrypted;
	}
}