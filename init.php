<?php
/**
 * Created by Wir_Wolf.
 * Author: Andru Cherny
 * E-mail: wir_wolf@bk.ru
 * Date: 04.09.14
 * Time: 17:32
 */
/**
 * @param $var
 * @param string $tab
 */
function var_echo($var, $tab = 'default')
{
	\DebugBar::instance()->messages->addMessage($var,$tab);
}