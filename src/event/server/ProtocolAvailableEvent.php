<?php

namespace pocketmine\event\server;

use pocketmine\event\Event;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

class ProtocolAvailableEvent extends ServerEvent
{
	public function __construct(
		protected int $protocol,
		protected array $availableProtocols = [ProtocolInfo::CURRENT_PROTOCOL]
	){ }

	/**
	 * @return int
	 */
	public function getProtocol() : int{
		return $this->protocol;
	}

	/**
	 * @return array
	 */
	public function getAvailableProtocols() : array{
		return $this->availableProtocols;
	}

	/**
	 * @param array $availableProtocols
	 */
	public function setAvailableProtocols(array $availableProtocols) : void{
		$this->availableProtocols = $availableProtocols;
	}
}