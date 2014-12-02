<?php
/**
 * Created by Wir_Wolf.
 * Author: Andru Cherny
 * E-mail: wir_wolf@bk.ru
 * Date: 04.09.14
 * Time: 17:32
 */

/**
 * Class DebugBarToRegistry
 */
class DebugBarToRegistry extends \Registry
{
	/**
	 * @param string $name
	 * @param string $value
	 */
	public static function setProperty($name, $value)
	{
		parent::setProperty($name, $value);
	}
}



\DebugBarToRegistry::setProperty('DebugBar',new DebugBar\StandardDebugBar());
//\Registry::instance()->DebugBar->addCollector(new \DebugBar\DataCollector\DumpCollector());

/**
 * @param $var
 * @param string $tab
 */
function var_echo($var, $tab = 'default')
{
	\DebugBar::instance()->messages->addMessage($var,$tab);
}