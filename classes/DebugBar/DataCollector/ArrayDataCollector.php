<?php
/*
 * This file is part of the DebugBar package.
 *
 * (c) 2013 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DebugBar\DataCollector;

/**
 * Collects info about the current request
 */
class ArrayDataCollector extends DataCollector implements Renderable
{
	protected $name;
	private $data;

	/**
	 * @param string $name
	 */
	public function __construct($name = 'Array')
	{
		$this->name = $name;
	}

	public function collect()
	{
		return $this->data;
	}

	public function addData($key, $data)
	{
		$this->data[$key] = $this->getDataFormatter()->formatVar($data);
	}

	public function getName()
	{
		return $this->name;
	}

	public function getWidgets()
	{
		return array(
			$this->name."" => array(
				"icon" => "tags",
				"widget" => "PhpDebugBar.Widgets.VariableListWidget",
				"map" => $this->name,
				"default" => "{}"
			)
		);
	}
}
