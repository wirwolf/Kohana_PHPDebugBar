<?php
/*
 * This file is part of the DebugBar package.
 *
 * (c) 2013 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DebugBar\DataFormatter;

class NiceDataFormatter implements DataFormatterInterface
{
    public function formatVar($data)
    {
        return $this->kintLite($data);
    }

    public function formatDuration($seconds)
    {
        if ($seconds < 0.001) {
            return round($seconds * 1000000) . 'μs';
        } else if ($seconds < 1) {
            return round($seconds * 1000, 2) . 'ms';
        }
        return round($seconds, 2) . 's';
    }

    public function formatBytes($size, $precision = 2)
    {
        if ($size === 0 || $size === null) {
            return "0B";
        }
        $base = log($size) / log(1024);
        $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

     /**
     * lightweight version of Kint::dump(). Uses whitespace for formatting instead of html
     * sadly not DRY yet
     *
     * Extracted from Kint.class.php in raveren/kint, https://github.com/raveren/kint
     * Copyright (c) 2013 Rokas Šleinius (raveren@gmail.com)
     *
     * @param mixed $var
     * @param int $level
     *
     * @return string
     */
    protected function kintLite(&$var, $level = 0)
    {


	    var_dump($var);
        // initialize function names into variables for prettier string output (html and implode are also DRY)
        $html     = "htmlspecialchars";
        $implode  = "implode";
        $strlen   = "strlen";
        $count    = "count";
        $getClass = "get_class";


        if ( $var === null ) {
            return 'NULL';
        } elseif ( is_bool( $var ) ) {
            return 'bool ' . ( $var ? 'TRUE' : 'FALSE' );
        } elseif ( is_float( $var ) ) {
            return 'float ' . $var;
        } elseif ( is_int( $var ) ) {
            return 'integer ' . $var;
        } elseif ( is_resource( $var ) ) {
            if ( ( $type = get_resource_type( $var ) ) === 'stream' AND $meta = stream_get_meta_data( $var ) ) {

                if ( isset( $meta['uri'] ) ) {
                    $file = $meta['uri'];

                    return "resource ({$type}) {$html( $file, 0 )}";
                } else {
                    return "resource ({$type})";
                }
            } else {
                return "resource ({$type})";
            }
        } elseif ( is_string( $var ) ) {
            return "string ({$strlen( $var )}) \"{$html( $var )}\"";
        } elseif ( is_array( $var ) ) {
            $output = array();
            $space  = str_repeat( $s = '    ', $level );

            static $marker;

            if ( $marker === null ) {
                // Make a unique marker
                $marker = uniqid( "\x00" );
            }

            if ( empty( $var ) ) {
                return "array()";
            } elseif ( isset( $var[$marker] ) ) {
                $output[] = "[\n$space$s*RECURSION*\n$space]";
            } elseif ( $level < 7 ) {
                $isSeq = array_keys( $var ) === range( 0, count( $var ) - 1 );

                $output[] = "[";

                $var[$marker] = true;


                foreach ( $var as $key => &$val ) {
                    if ( $key === $marker ) continue;

                    $key = $space . $s . ( $isSeq ? "" : "'{$html( $key, 0 )}' => " );

                    $dump     = $this->kintLite( $val, $level + 1 );
                    $output[] = "{$key}{$dump}";
                }

                unset( $var[$marker] );
                $output[] = "$space]";

            } else {
                $output[] = "[\n$space$s*depth too great*\n$space]";
            }
            return "array({$count( $var )}) {$implode( "\n", $output )}";
        } elseif ( is_object( $var ) ) {
            if ( $var instanceof SplFileInfo ) {
                return "object SplFileInfo " . $var->getRealPath();
            }

            // Copy the object as an array
            $array = (array)$var;

            $output = array();
            $space  = str_repeat( $s = '    ', $level );

            $hash = spl_object_hash( $var );

            // Objects that are being dumped
            static $objects = array();

            if ( empty( $array ) ) {
                return "object {$getClass( $var )} {}";
            } elseif ( isset( $objects[$hash] ) ) {
                $output[] = "{\n$space$s*RECURSION*\n$space}";
            } elseif ( $level < 7 ) {
                $output[]       = "{";
                $objects[$hash] = true;

                foreach ( $array as $key => & $val ) {
                    if ( $key[0] === "\x00" ) {

                        $access = $key[1] === "*" ? "protected" : "private";

                        // Remove the access level from the variable name
                        $key = substr( $key, strrpos( $key, "\x00" ) + 1 );
                    } else {
                        $access = "public";
                    }

                    $output[] = "$space$s$access $key -> " . $this->kintLite( $val, $level + 1 );
                }
                unset( $objects[$hash] );
                $output[] = "$space}";

            } else {
                $output[] = "{\n$space$s*depth too great*\n$space}";
            }

            return "object {$getClass( $var )} ({$count( $array )}) {$implode( "\n", $output )}";
        } else {
            return gettype( $var ) . htmlspecialchars( var_export( $var, true ), ENT_NOQUOTES );
        }
    }


	/**
	 * Calculates real string length
	 * @param   string $string
	 * @return  int
	 */
	protected static function strLen($string)
	{
		$encoding = function_exists('mb_detect_encoding') ? mb_detect_encoding($string) : false;
		return $encoding ? mb_strlen($string, $encoding) : strlen($string);
	}


	/**
	 * Split a regex into its components
	 * Based on "Regex Colorizer" by Steven Levithan (this is a translation from javascript)
	 * @link     https://github.com/slevithan/regex-colorizer
	 * @link     https://github.com/symfony/Finder/blob/master/Expression/Regex.php#L64-74
	 * @param    string $pattern
	 * @throws \Exception
	 * @return   array
	 */
	public static function splitRegex($pattern)
	{

		// detection attempt code from the Symfony Finder component
		$maybeValid = false;
		if(preg_match('/^(.{3,}?)([imsxuADU]*)$/', $pattern, $m))
		{
			$start = substr($m[1], 0, 1);
			$end = substr($m[1], -1);

			if(($start === $end && !preg_match('/[*?[:alnum:] \\\\]/', $start)) || ($start === '{' && $end === '}'))
			{
				$maybeValid = true;
			}
		}

		if(!$maybeValid)
		{
			throw new \Exception('Pattern does not appear to be a valid PHP regex');
		}

		$output = [];
		$capturingGroupCount = 0;
		$groupStyleDepth = 0;
		$openGroups = [];
		$lastIsQuant = false;
		$lastType = 1; // 1 = none; 2 = alternator
		$lastStyle = null;

		preg_match_all('/\[\^?]?(?:[^\\\\\]]+|\\\\[\S\s]?)*]?|\\\\(?:0(?:[0-3][0-7]{0,2}|[4-7][0-7]?)?|[1-9][0-9]*|x[0-9A-Fa-f]{2}|u[0-9A-Fa-f]{4}|c[A-Za-z]|[\S\s]?)|\((?:\?[:=!]?)?|(?:[?*+]|\{[0-9]+(?:,[0-9]*)?\})\??|[^.?*+^${[()|\\\\]+|./', $pattern, $matches);

		$matches = $matches[0];

		$getTokenCharCode = function ($token)
		{
			if(strlen($token) > 1 && $token[0] === '\\')
			{
				$t1 = substr($token, 1);

				if(preg_match('/^c[A-Za-z]$/', $t1))
				{
					return strpos("ABCDEFGHIJKLMNOPQRSTUVWXYZ", strtoupper($t1[1])) + 1;
				}

				if(preg_match('/^(?:x[0-9A-Fa-f]{2}|u[0-9A-Fa-f]{4})$/', $t1))
				{
					return intval(substr($t1, 1), 16);
				}

				if(preg_match('/^(?:[0-3][0-7]{0,2}|[4-7][0-7]?)$/', $t1))
				{
					return intval($t1, 8);
				}

				$len = strlen($t1);

				if($len === 1 && strpos('cuxDdSsWw', $t1) !== false)
				{
					return null;
				}

				if($len === 1)
				{
					switch($t1)
					{
						case 'b':
							return 8;
						case 'f':
							return 12;
						case 'n':
							return 10;
						case 'r':
							return 13;
						case 't':
							return 9;
						case 'v':
							return 11;
						default:
							return $t1[0];
					}
				}
			}

			return ($token !== '\\') ? $token[0] : null;
		};

		foreach($matches as $m)
		{

			if($m[0] === '[')
			{
				$lastCC = null;
				$cLastRangeable = false;
				$cLastType = 0; // 0 = none; 1 = range hyphen; 2 = short class

				preg_match('/^(\[\^?)(]?(?:[^\\\\\]]+|\\\\[\S\s]?)*)(]?)$/', $m, $parts);

				array_shift($parts);
				list($opening, $content, $closing) = $parts;

				if(!$closing)
				{
					throw new \Exception('Unclosed character class');
				}

				preg_match_all('/[^\\\\-]+|-|\\\\(?:[0-3][0-7]{0,2}|[4-7][0-7]?|x[0-9A-Fa-f]{2}|u[0-9A-Fa-f]{4}|c[A-Za-z]|[\S\s]?)/', $content, $ccTokens);
				$ccTokens = $ccTokens[0];
				$ccTokenCount = count($ccTokens);
				$output[] = ['chr' => $opening];

				foreach($ccTokens as $i => $cm)
				{

					if($cm[0] === '\\')
					{
						if(preg_match('/^\\\\[cux]$/', $cm))
						{
							throw new \Exception('Incomplete regex token');
						}

						if(preg_match('/^\\\\[dsw]$/i', $cm))
						{
							$output[] = ['chr-meta' => $cm];
							$cLastRangeable = ($cLastType !== 1);
							$cLastType = 2;

						}
						elseif($cm === '\\')
						{
							throw new \Exception('Incomplete regex token');

						}
						else
						{
							$output[] = ['chr-meta' => $cm];
							$cLastRangeable = $cLastType !== 1;
							$lastCC = $getTokenCharCode($cm);
						}

					}
					elseif($cm === '-')
					{
						if($cLastRangeable)
						{
							$nextToken = ($i + 1 < $ccTokenCount) ? $ccTokens[$i + 1] : false;

							if($nextToken)
							{
								$nextTokenCharCode = $getTokenCharCode($nextToken[0]);

								if((!is_null($nextTokenCharCode) && $lastCC > $nextTokenCharCode) || $cLastType === 2 || preg_match('/^\\\\[dsw]$/i', $nextToken[0]))
								{
									throw new \Exception('Reversed or invalid range');
								}

								$output[] = ['chr-range' => '-'];
								$cLastRangeable = false;
								$cLastType = 1;

							}
							else
							{
								$output[] = $closing ? ['chr' => '-'] : ['chr-range' => '-'];
							}

						}
						else
						{
							$output[] = ['chr' => '-'];
							$cLastRangeable = ($cLastType !== 1);
						}

					}
					else
					{
						$output[] = ['chr' => $cm];
						$cLastRangeable = strlen($cm) > 1 || ($cLastType !== 1);
						$lastCC = $cm[strlen($cm) - 1];
					}
				}

				$output[] = ['chr' => $closing];
				$lastIsQuant = true;

			}
			elseif($m[0] === '(')
			{
				if(strlen($m) === 2)
				{
					throw new \Exception('Invalid or unsupported group type');
				}

				if(strlen($m) === 1)
				{
					$capturingGroupCount++;
				}

				$groupStyleDepth = ($groupStyleDepth !== 5) ? $groupStyleDepth + 1 : 1;
				$openGroups[] = $m; // opening
				$lastIsQuant = false;
				$output[] = ["g{$groupStyleDepth}" => $m];

			}
			elseif($m[0] === ')')
			{
				if(!count($openGroups))
				{
					throw new \Exception('No matching opening parenthesis');
				}

				$output[] = ['g' . $groupStyleDepth => ')'];
				$prevGroup = $openGroups[count($openGroups) - 1];
				$prevGroup = isset($prevGroup[2]) ? $prevGroup[2] : '';
				$lastIsQuant = !preg_match('/^[=!]/', $prevGroup);
				$lastStyle = "g{$groupStyleDepth}";
				$lastType = 0;
				$groupStyleDepth = ($groupStyleDepth !== 1) ? $groupStyleDepth - 1 : 5;

				array_pop($openGroups);
				continue;

			}
			elseif($m[0] === '\\')
			{
				if(isset($m[1]) && preg_match('/^[1-9]/', $m[1]))
				{
					$nonBackrefDigits = '';
					$num = substr(+$m, 1);

					while($num > $capturingGroupCount)
					{
						preg_match('/[0-9]$/', $num, $digits);
						$nonBackrefDigits = $digits[0] . $nonBackrefDigits;
						$num = floor($num / 10);
					}

					if($num > 0)
					{
						$output[] = [
							'meta' => "\\{$num}",
							'text' => $nonBackrefDigits
						];

					}
					else
					{
						preg_match('/^\\\\([0-3][0-7]{0,2}|[4-7][0-7]?|[89])([0-9]*)/', $m, $pts);
						$output[] = [
							'meta' => '\\' . $pts[1],
							'text' => $pts[2]
						];
					}

					$lastIsQuant = true;

				}
				elseif(isset($m[1]) && preg_match('/^[0bBcdDfnrsStuvwWx]/', $m[1]))
				{

					if(preg_match('/^\\\\[cux]$/', $m))
					{
						throw new \Exception('Incomplete regex token');
					}

					$output[] = ['meta' => $m];
					$lastIsQuant = (strpos('bB', $m[1]) === false);

				}
				elseif($m === '\\')
				{
					throw new \Exception('Incomplete regex token');

				}
				else
				{
					$output[] = ['text' => $m];
					$lastIsQuant = true;
				}

			}
			elseif(preg_match('/^(?:[?*+]|\{[0-9]+(?:,[0-9]*)?\})\??$/', $m))
			{
				if(!$lastIsQuant)
				{
					throw new \Exception('Quantifiers must be preceded by a token that can be repeated');
				}

				preg_match('/^\{([0-9]+)(?:,([0-9]*))?/', $m, $interval);

				if($interval && (+$interval[1] > 65535 || (isset($interval[2]) && (+$interval[2] > 65535))))
				{
					throw new \Exception('Interval quantifier cannot use value over 65,535');
				}

				if($interval && isset($interval[2]) && (+$interval[1] > +$interval[2]))
				{
					throw new \Exception('Interval quantifier range is reversed');
				}

				$output[] = [$lastStyle ? $lastStyle : 'meta' => $m];
				$lastIsQuant = false;

			}
			elseif($m === '|')
			{
				if($lastType === 1 || ($lastType === 2 && !count($openGroups)))
				{
					throw new \Exception('Empty alternative effectively truncates the regex here');
				}

				$output[] = count($openGroups) ? ["g{$groupStyleDepth}" => '|'] : ['meta' => '|'];
				$lastIsQuant = false;
				$lastType = 2;
				$lastStyle = '';
				continue;

			}
			elseif($m === '^' || $m === '$')
			{
				$output[] = ['meta' => $m];
				$lastIsQuant = false;

			}
			elseif($m === '.')
			{
				$output[] = ['meta' => '.'];
				$lastIsQuant = true;

			}
			else
			{
				$output[] = ['text' => $m];
				$lastIsQuant = true;
			}

			$lastType = 0;
			$lastStyle = '';
		}

		if($openGroups)
		{
			throw new \Exception('Unclosed grouping');
		}

		return $output;
	}

	private $arr_data = [];
	private $recursionFlag = false;

	public function dump(&$var)
	{
		$node = [

		];
		switch($type = gettype($var))
		{

			// https://github.com/digitalnature/php-ref/issues/13
			case 'unknown type':
				$node['type'] = 'unknown';

			break;
			// null value
			case 'NULL':
				$node['type'] = 'NULL';
			break;

			// integer/double/float
			case 'integer':
			case 'double':
				$node = Nice\Integer::get($node);
			// boolean
			case 'boolean':
				$text = $var ? 'true' : 'false';
				$node['type'] = 'boolean';
				$node['value'] = $text;
			// arrays
			case 'array':
				$node = Nice\Arrays::get($node);
			break;
			// resource
			case 'resource':
				$node = Nice\Resource::get($node);
			break;
			// string
			case 'string':
				$node = Nice\String::get($node);
			//object
			case 'object':
				$node = Nice\Object::get($node);
			break;
		}


	}
}
