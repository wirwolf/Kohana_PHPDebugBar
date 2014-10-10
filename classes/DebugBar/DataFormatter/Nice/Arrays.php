<?php
/**
 * Created by Wir_Wolf.
 * Author: Andru Cherny
 * E-mail: wir_wolf@bk.ru
 * Date: 07.09.14
 * Time: 21:21
 */

namespace DebugBar\DataFormatter\Nice;


class Arrays {

	public static function get($node)
	{
		// empty array?
		if(empty($var))
		{

		}

		if(isset($var))
		{

		}

		// first recursion level detection;
		// this is optional (used to print consistent recursion info)
		foreach($var as $key => &$value)
		{
			if(!is_array($value))
			{
				continue;
			}

			// save current value in a temporary variable
			$buffer = $value;

			// assign new value
			$value = ($value !== 1) ? 1 : 2;

			// if they're still equal, then we have a reference
			if($value === $var)
			{
				$value = $buffer;
				$value[static::MARKER_KEY] = true;
				//$this->evaluate($value);
				return;
			}

			// restoring original value
			$value = $buffer;
		}

		$count = count($var);

		$max = max(array_map('static::strLen', array_keys($var)));
		$var[static::MARKER_KEY] = true;

		foreach($var as $key => &$value)
		{

			// ignore our temporary marker
			if($key === static::MARKER_KEY)
			{
				continue;
			}

			$keyInfo = gettype($key);

			if($keyInfo === 'string')
			{
				$encoding = mb_detect_encoding($key);
				$keyLen = $encoding && ($encoding !== 'ASCII') ? static::strLen($key) . '; ' . $encoding : static::strLen($key);
				$keyInfo = "{$keyInfo}({$keyLen})";
			}
			else
			{
				$keyLen = strlen($key);
			}
			//-----------------------------------------
		}

		return $node;
	}
}