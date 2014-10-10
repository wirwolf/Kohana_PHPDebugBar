<?php
/**
 * Created by Wir_Wolf.
 * Author: Andru Cherny
 * E-mail: wir_wolf@bk.ru
 * Date: 04.09.14
 * Time: 17:32
 */
\Registry::setDebugBar(new DebugBar\StandardDebugBar());
\Registry::getDebugBar()->addCollector(new \DebugBar\DataCollector\DumpCollector());
//\Registry::getDebugBar()['dump']->setDataFormatter(new \DebugBar\DataFormatter\NiceDataFormatter());
/**
 * @param $var
 * @param string $tab
 */
function var_echo($var, $tab = 'default')
{
	\Registry::getDebugBar()['messages']->addMessage($var,$tab);
}