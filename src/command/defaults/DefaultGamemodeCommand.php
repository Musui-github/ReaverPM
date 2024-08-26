<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\item\StringToItemParser;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandEnumConstraint;
use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\GameMode;
use pocketmine\ServerProperties;
use function count;

class DefaultGamemodeCommand extends VanillaCommand{

	public function __construct(){
		parent::__construct(
			"defaultgamemode",
			KnownTranslationFactory::pocketmine_command_defaultgamemode_description(),
			KnownTranslationFactory::commands_defaultgamemode_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_DEFAULTGAMEMODE);
	}

	/**
	 * @param CommandEnum[]           $hardcodedEnums
	 * @param CommandEnum[]           $softEnums
	 * @param CommandEnumConstraint[] $enumConstraints
	 * @return null|CommandOverload[]
	 */
	public function buildOverloads(array &$hardcodedEnums, array &$softEnums, array &$enumConstraints) : ?array{
		$gamemodeOptions = array_keys(GameMode::getAll());
		$gamemodeOptions = array_merge($gamemodeOptions, array_map(fn(string $gameModeString) => $gameModeString[0], $gamemodeOptions));
		$gamemodeOptions = array_map(fn(string $gameModeString) => mb_strtolower($gameModeString), $gamemodeOptions);
		$gamemodeEnum = new CommandEnum('GameMode', $gamemodeOptions, false);

		return [
			new CommandOverload(chaining: false, parameters: [
				CommandParameter::enum("Gamemode", $gamemodeEnum, 0, false),
			]),
			new CommandOverload(chaining: false, parameters: [
				CommandParameter::standard("Gamemode", AvailableCommandsPacket::ARG_TYPE_INT, 0, false),
			])
		];
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) === 0){
			throw new InvalidCommandSyntaxException();
		}

		$gameMode = GameMode::fromString($args[0]);
		if($gameMode === null){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_gamemode_unknown($args[0]));
			return true;
		}

		//TODO: this probably shouldn't use the enum name directly
		$sender->getServer()->getConfigGroup()->setConfigString(ServerProperties::GAME_MODE, $gameMode->name);
		$sender->sendMessage(KnownTranslationFactory::commands_defaultgamemode_success($gameMode->getTranslatableName()));
		return true;
	}
}
