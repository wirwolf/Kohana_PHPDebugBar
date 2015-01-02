<?php
namespace Kohana;
use DebugBar\StandardDebugBar;

/**
 * Created by Wir_Wolf.
 * Author: Andru Cherny
 * E-mail: wir_wolf@bk.ru
 * Date: 02.12.14
 * Time: 15:43
 *
 * @property \DebugBar\DataCollector\PhpInfoCollector $php
 * @property \DebugBar\DataCollector\MessagesCollector $messages
 * @property \DebugBar\DataCollector\RequestDataCollector $request
 * @property \DebugBar\DataCollector\MemoryCollector $memory
 * @property \DebugBar\DataCollector\ExceptionsCollector $exceptions
 * @property \DebugBar\DataCollector\TimeDataCollector $time
 * @property \DebugBar\Bridge\CacheCacheCollector $cache
 */

class DebugBar extends StandardDebugBar{

	/**
	 * @var \DebugBar singleton
	 */
	private static $obj;

	/**
	 * @return \DebugBar
	 */
	public static function instance()
	{
		if(!is_object(self::$obj))
		{
			self::$obj = new self();
		}
		return self::$obj;
	}

	/**
	 * @param string $name
	 */
	public function __get($name)
	{
		return $this->collectors[$name];
	}


} 