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
use Random\RandomException;

class SaveAsyncConfigOperation extends ThreadOperation{
	protected string $encryptionKey;

	public function __construct(
		protected string $path,
		protected string $content,
		\Closure $closure
	) {
		$this->encryptionKey = Utils::getMachineUniqueId()->toString();
		parent::__construct($closure);
	}

	public function run() : void{
		$compressedData = gzcompress($this->content);
		$encryptedData = $this->encrypt($compressedData);
		$encodedData = base64_encode($encryptedData);

		Filesystem::safeFilePutContents($this->path, $encodedData);
	}

	/**
	 * @param string $data
	 * @return string
	 * @throws RandomException
	 * @throws Exception
	 */
	private function encrypt(string $data): string
	{
		$key = hash('sha256', $this->encryptionKey, true);
		$iv = random_bytes(16);

		$encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
		if ($encrypted === false) {
			throw new Exception("Encryption failed.");
		}

		return $iv . $encrypted;
	}
}