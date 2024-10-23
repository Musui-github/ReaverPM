<?php

namespace pocketmine\reaper\util;

use JsonException;
use pocketmine\errorhandler\ErrorToExceptionHandler;
use pocketmine\reaper\multithreading\MultiThreading;
use pocketmine\reaper\multithreading\threads\RThread;
use pocketmine\reaper\operation\LoadAsyncConfigOperation;
use pocketmine\reaper\operation\SaveAsyncConfigOperation;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Config;
use pocketmine\utils\ConfigLoadException;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;

class AsyncConfig extends Config{

	public static ?RThread $thread = null;

	/**
	 * @var mixed[]
	 * @phpstan-var array<string, mixed>
	 */
	private array $config = [];

	/**
	 * @var mixed[]
	 * @phpstan-var array<string, mixed>
	 */
	private array $nestedCache = [];

	private string $file;
	private int $type = Config::DETECT;
	private int $jsonOptions = JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING;

	private bool $changed = false;

	/** @var int[] */
	public static array $formats = [
		"properties" => Config::PROPERTIES,
		"cnf" => Config::CNF,
		"conf" => Config::CNF,
		"config" => Config::CNF,
		"json" => Config::JSON,
		"js" => Config::JSON,
		"yml" => Config::YAML,
		"yaml" => Config::YAML,
		//"export" => Config::EXPORT,
		//"xport" => Config::EXPORT,
		"sl" => Config::SERIALIZED,
		"serialize" => Config::SERIALIZED,
		"txt" => Config::ENUM,
		"list" => Config::ENUM,
		"enum" => Config::ENUM
	];

	public function __construct(string $file, int $type = Config::DETECT, array $default = [])
	{
		if(self::$thread == null) {
			MultiThreading::getInstance()->create($id = Utils::getMachineUniqueId()->toString());
			self::$thread = MultiThreading::getInstance()->get($id);
		}

		parent::__construct($file, $type, $default);
	}

	/**
	 * @param string $file
	 * @param int    $type
	 * @param array  $default
	 *
	 * @throws JsonException
	 */
	private function load(string $file, int $type = Config::DETECT, array $default = []) : void{
		$this->file = $file;

		$this->type = $type;
		if($this->type === Config::DETECT){
			$extension = strtolower(Path::getExtension($this->file));
			if(isset(Config::$formats[$extension])){
				$this->type = Config::$formats[$extension];
			}else{
				throw new \InvalidArgumentException("Cannot detect config type of " . $this->file);
			}
		}

		if(!file_exists($file)){
			$this->config = $default;
			$this->save();
		}else{
			self::$thread->addOperation(new LoadAsyncConfigOperation($file, function(?string $content) use($default){
				if(is_null($content)) {
					$this->config = $default;
					$this->save();
					return;
				}

				switch($this->type){
					case Config::PROPERTIES:
						$config = self::parseProperties($content);
						break;
					case Config::JSON:
						try{
							$config = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
						}catch(JsonException $e){
							throw ConfigLoadException::wrap($this->file, $e);
						}
						break;
					case Config::YAML:
						$content = self::fixYAMLIndexes($content);
						try{
							$config = ErrorToExceptionHandler::trap(fn() => yaml_parse($content));
						}catch(\ErrorException $e){
							throw ConfigLoadException::wrap($this->file, $e);
						}
						break;
					case Config::SERIALIZED:
						try{
							$config = ErrorToExceptionHandler::trap(fn() => unserialize($content));
						}catch(\ErrorException $e){
							throw ConfigLoadException::wrap($this->file, $e);
						}
						break;
					case Config::ENUM:
						$config = array_fill_keys(self::parseList($content), true);
						break;
					default:
						throw new \InvalidArgumentException("Invalid config type specified");
				}
				if(!is_array($config)){
					throw new ConfigLoadException("Failed to load config $this->file: Expected array for base type, but got " . get_debug_type($config));
				}
				$this->config = $config;
			}));
		}
	}

	/**
	 * Flushes the config to disk in the appropriate format.
	 */
	public function save() : void{
		$content = null;
		switch($this->type){
			case Config::PROPERTIES:
				$content = self::writeProperties($this->config);
				break;
			case Config::JSON:
				$content = json_encode($this->config, $this->jsonOptions | JSON_THROW_ON_ERROR);
				break;
			case Config::YAML:
				$content = yaml_emit($this->config, YAML_UTF8_ENCODING);
				break;
			case Config::SERIALIZED:
				$content = serialize($this->config);
				break;
			case Config::ENUM:
				$content = self::writeList(array_keys($this->config));
				break;
			default:
				throw new AssumptionFailedError("Config type is unknown, has not been set or not detected");
		}

		if($content == null) return;
		self::$thread->addOperation(new SaveAsyncConfigOperation($this->file, $content, fn() => $this->changed = false));
	}
}