<?php
/**
 * Created by Wir_Wolf.
 * Author: Andru Cherny
 * E-mail: wir_wolf@bk.ru
 * Date: 07.09.14
 * Time: 21:20
 */

namespace DebugBar\DataFormatter\Nice;


class String {

	public static function get($node)
	{
		//$length = static::strLen($node);
		$encoding = mb_detect_encoding($node);
		$info = $encoding && ($encoding !== 'ASCII') ? $length . '; ' . $encoding : $length;

		/*if($specialStr)
		{
			$this->fmt->sep('"');
			$this->fmt->text([
				'string',
				'special'
			], $var, "string({$info})");
			$this->fmt->sep('"');
			return;
		}*/

		//$this->fmt->text('string', $var, "string({$info})");

		// advanced checks only if there are 3 characteres or more
		if(($length > 2) && (trim($var) !== ''))
		{

			$isNumeric = is_numeric($var);

			// very simple check to determine if the string could match a file path
			// @note: this part of the code is very expensive
			$isFile = ($length < 2048) && (max(array_map('strlen', explode('/', str_replace('\\', '/', $var)))) < 128) && !preg_match('/[^\w\.\-\/\\\\:]|\..*\.|\.$|:(?!(?<=^[a-zA-Z]:)[\/\\\\])/', $var);

			if($isFile)
			{
				try
				{
					$file = new \SplFileInfo($var);
					$flags = [];
					$perms = $file->getPerms();

					if(($perms & 0xC000) === 0xC000) // socket
					{
						$flags[] = 's';
					}
					elseif(($perms & 0xA000) === 0xA000) // symlink
					{
						$flags[] = 'l';
					}
					elseif(($perms & 0x8000) === 0x8000) // regular
					{
						$flags[] = '-';
					}
					elseif(($perms & 0x6000) === 0x6000) // block special
					{
						$flags[] = 'b';
					}
					elseif(($perms & 0x4000) === 0x4000) // directory
					{
						$flags[] = 'd';
					}
					elseif(($perms & 0x2000) === 0x2000) // character special
					{
						$flags[] = 'c';
					}
					elseif(($perms & 0x1000) === 0x1000) // FIFO pipe
					{
						$flags[] = 'p';
					}
					else // unknown
					{
						$flags[] = 'u';
					}

					// owner
					$flags[] = (($perms & 0x0100) ? 'r' : '-');
					$flags[] = (($perms & 0x0080) ? 'w' : '-');
					$flags[] = (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));

					// group
					$flags[] = (($perms & 0x0020) ? 'r' : '-');
					$flags[] = (($perms & 0x0010) ? 'w' : '-');
					$flags[] = (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));

					// world
					$flags[] = (($perms & 0x0004) ? 'r' : '-');
					$flags[] = (($perms & 0x0002) ? 'w' : '-');
					$flags[] = (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));

					$size = is_dir($var) ? '' : sprintf(' %.2fK', $file->getSize() / 1024);

					/*$this->fmt->startContain('file', true);
					$this->fmt->text('file', implode('', $flags) . $size);
					$this->fmt->endContain();*/

				}
				catch(\Exception $e)
				{
					$isFile = false;
				}
			}

			// class/interface/function
			if(!preg_match('/[^\w+\\\\]/', $var) && ($length < 96))
			{
				$isClass = class_exists($var, false);
				if($isClass)
				{
					/*$this->fmt->startContain('class', true);
					$this->fromReflector(new \ReflectionClass($var));
					$this->fmt->endContain();*/
				}

				if(!$isClass && interface_exists($var, false))
				{
					/*$this->fmt->startContain('interface', true);
					$this->fromReflector(new \ReflectionClass($var));
					$this->fmt->endContain('interface');*/
				}

				if(function_exists($var))
				{
					/*$this->fmt->startContain('function', true);
					$this->fromReflector(new \ReflectionFunction($var));
					$this->fmt->endContain('function');*/
				}
			}


			// skip serialization/json/date checks if the string appears to be numeric,
			// or if it's shorter than 5 characters
			if(!$isNumeric && ($length > 4))
			{

				// url
				/*if(static::$config['showUrls'] && static::$env['curlActive'] && filter_var($var, FILTER_VALIDATE_URL))
				{
					$ch = curl_init($var);
					curl_setopt($ch, CURLOPT_NOBODY, true);
					curl_exec($ch);
					$nfo = curl_getinfo($ch);
					curl_close($ch);

					if($nfo['http_code'])
					{
						$this->fmt->startContain('url', true);
						$contentType = explode(';', $nfo['content_type']);
						$this->fmt->text('url', sprintf('%s:%d %s %.2fms (%d)', !empty($nfo['primary_ip']) ? $nfo['primary_ip'] : null, !empty($nfo['primary_port']) ? $nfo['primary_port'] : null, $contentType[0], $nfo['total_time'], $nfo['http_code']));
						$this->fmt->endContain();
					}

				}*/

				// date
				if(($length < 128) && !preg_match('/[^A-Za-z0-9.:+\s\-\/]/', $var))
				{
					try
					{
						$date = new \DateTime($var);
						$errors = \DateTime::getLastErrors();

						if(($errors['warning_count'] < 1) && ($errors['error_count'] < 1))
						{
							$now = new \Datetime('now');
							$nowUtc = new \Datetime('now', new \DateTimeZone('UTC'));
							$diff = $now->diff($date);

							$map = [
								'y' => 'yr',
								'm' => 'mo',
								'd' => 'da',
								'h' => 'hr',
								'i' => 'min',
								's' => 'sec',
							];

							$timeAgo = 'now';
							foreach($map as $k => $label)
							{
								if($diff->{$k} > 0)
								{
									$timeAgo = $diff->format("%R%{$k}{$label}");
									break;
								}
							}

							$tz = $date->getTimezone();
							$offs = round($tz->getOffset($nowUtc) / 3600);

							if($offs > 0)
							{
								$offs = "+{$offs}";
							}

							$timeAgo .= ((int)$offs !== 0) ? ' ' . sprintf('%s (UTC%s)', $tz->getName(), $offs) : ' UTC';
							/*$this->fmt->startContain('date', true);
							$this->fmt->text('date', $timeAgo);
							$this->fmt->endContain();*/

						}
					}
					catch(\Exception $e)
					{
						// not a date
					}

				}

				// attempt to detect if this is a serialized string
				static $unserializing = 0;
				$isSerialized = ($unserializing < 3) && (($var[$length - 1] === ';') || ($var[$length - 1] === '}')) && in_array($var[0], [
						's',
						'a',
						'O'
					], true) && ((($var[0] === 's') && ($var[$length - 2] !== '"')) || preg_match("/^{$var[0]}:[0-9]+:/s", $var)) && (($unserialized = @unserialize($var)) !== false);

				if($isSerialized)
				{
					$unserializing++;
					/*$this->fmt->startContain('serialized', true);
					$this->evaluate($unserialized);
					$this->fmt->endContain();
					$unserializing--;*/
				}

				// try to find out if it's a json-encoded string;
				// only do this for json-encoded arrays or objects, because other types have too generic formats
				static $decodingJson = 0;
				$isJson = !$isSerialized && ($decodingJson < 3) && in_array($var[0], [
						'{',
						'['
					], true);

				if($isJson)
				{
					$decodingJson++;
					$json = json_decode($var);

					if($isJson = (json_last_error() === JSON_ERROR_NONE))
					{
						/*$this->fmt->startContain('json', true);
						$this->evaluate($json);
						$this->fmt->endContain();*/
					}

					$decodingJson--;
				}

				// attempt to match a regex
				if($length < 768)
				{
					try
					{
						//$components = $this->splitRegex($var);
						if($components)
						{
							$regex = '';

							/*$this->fmt->startContain('regex', true);
								foreach($components as $component)
								{
									$this->fmt->text('regex-' . key($component), reset($component));
								}
								$this->fmt->endContain();*/
						}

					}
					catch(\Exception $e)
					{
						// not a regex
					}

				}
			}
		}
		return $node;
	}
}