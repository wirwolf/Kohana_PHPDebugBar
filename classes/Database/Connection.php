<?php
namespace Database;
/**
 * Class Connection
 * @package Database
 */
class Connection extends \Database\ForOverridden\Connection
{
	/**
	 * Creates the PDO instance.
	 * When some functionalities are missing in the pdo driver, we may use
	 * an adapter class to provide them.
	 * @throws \Database\Exception
	 * @return \PDO the pdo instance
	 */
	protected function createPdoInstance()
	{
		$pdoClass=$this->pdoClass;
		if(($pos=strpos($this->connectionString,':'))!==false)
		{
			$driver=strtolower(substr($this->connectionString,0,$pos));
			if($driver==='mssql' || $driver==='dblib')
				$pdoClass='CMssqlPdoAdapter';
			elseif($driver==='sqlsrv')
				$pdoClass='CMssqlSqlsrvPdoAdapter';
		}

		if(!class_exists($pdoClass))
			throw new \Database\Exception('\Database\Connection is unable to find PDO class "{className}". Make sure PDO is installed correctly.');

		@$instance=new $pdoClass($this->connectionString,$this->username,$this->password/*,$this->_attributes*/);

		if(!$instance)
			throw new \Database\Exception('\Database\Connection failed to open the DB connection.');

		$debugBar = \Registry::getDebugBar();
		$pdoRead  = new \DebugBar\DataCollector\PDO\TraceablePDO($instance);
		$pdoWrite = new \DebugBar\DataCollector\PDO\TraceablePDO($instance);

		$pdoCollector = new \DebugBar\DataCollector\PDO\PDOCollector();
		$pdoCollector->addConnection($pdoRead, 'read-db');
		$pdoCollector->addConnection($pdoWrite, 'write-db');
		$debugBar->addCollector($pdoCollector);
		\Registry::setDebugBar($debugBar);
		return $instance;
	}

}
