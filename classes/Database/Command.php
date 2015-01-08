<?php
namespace Database;
/**
 * Class Command
 * @package Database
 */

/** @noinspection PhpUndefinedClassInspection */
class Command extends \Database\ForOverridden\Command
{

	/**
	 * @param string $method method of PDOStatement to be called
	 * @param mixed $mode parameters to be passed to the method
	 * @param array $params input parameters (name=>value) for the SQL execution. This is an alternative
	 * to {@link bindParam} and {@link bindValue}. If you have multiple input parameters, passing
	 * them in this way can improve the performance. Note that if you pass parameters in this way,
	 * you cannot bind parameters or values using {@link bindParam} or {@link bindValue}, and vice versa.
	 * Please also note that all values are treated as strings in this case, if you need them to be handled as
	 * their real data types, you have to use {@link bindParam} or {@link bindValue} instead.
	 * @throws Exception if CDbCommand failed to execute the SQL statement
	 * @return mixed the method execution result
	 */
	protected function queryInternal($method,$mode,$params=[])
	{
		$params=array_merge($this->params,$params);

		if($this->_connection->enableParamLogging && ($pars=array_merge($this->_paramLog,$params))!==[])
		{
			$p=[];
			foreach($pars as $name=>$value)
				$p[$name]=$name.'='.var_export($value,true);
			$par='. Bound with '.implode(', ',$p);
		}
		else
			$par='';

		/*Cache system*/

		if($this->_connection->queryCachingCount>0 && $method!==''
			&& $this->_connection->queryCachingDuration>0
			&& $this->_connection->queryCacheID!==false
			&& ($cache=\CacheManager::get('sql'))!==null)
		{
			$this->_connection->queryCachingCount--;
			$cacheKey='dbquery'.$this->_connection->connectionString.':'.$this->_connection->username;
			$cacheKey.=':'.$this->getText().':'.serialize(array_merge($this->_paramLog,$params));
			if(($result=$cache->get($cacheKey))!==false)
			{
				\Kohana::$log->add(\Log::CRITICAL,'Query result found in cache');
				return $result[0];
			}
		}

		try
		{
			$this->prepare();
			if($params===[])
				$this->_statement->execute();
			else
				$this->_statement->execute($params);

			if($method==='')
				$result=new DataReader($this);
			else
			{
				$mode=(array)$mode;
				call_user_func_array([$this->_statement, 'setFetchMode'], $mode);
				$result=$this->_statement->$method();
				$this->_statement->closeCursor();
			}

			return $result;
		}
		catch(Exception $e)
		{
			$errorInfo=$e instanceof \PDOException ? $e->errorInfo : null;
			$message=$e->getMessage();

			\Kohana::$log->add(\Log::EMERGENCY,'\Database\Command::'.$method.' failed '.$message.'. The SQL statement executed was: '.$this->getText().$par);

			if(\Kohana::DEVELOPMENT)
			{
				$message.='. The SQL statement executed was: '.$this->getText().$par;
			}


			throw new Exception('\Database\Command failed to execute the SQL statement: '.$message,(int)$e->getCode(),$errorInfo);
		}
	}


	/**
	 * Executes the SQL statement.
	 * This method is meant only for executing non-query SQL statement.
	 * No result set will be returned.
	 * @param array $params input parameters (name=>value) for the SQL execution. This is an alternative
	 * to {@link bindParam} and {@link bindValue}. If you have multiple input parameters, passing
	 * them in this way can improve the performance. Note that if you pass parameters in this way,
	 * you cannot bind parameters or values using {@link bindParam} or {@link bindValue}, and vice versa.
	 * Please also note that all values are treated as strings in this case, if you need them to be handled as
	 * their real data types, you have to use {@link bindParam} or {@link bindValue} instead.
	 * @return integer number of rows affected by the execution.
	 * @throws Exception execution failed
	 */
	public function execute($params=[])
	{
		if($this->_connection->enableParamLogging && ($pars=array_merge($this->_paramLog,$params))!==[])
		{
			$p=[];
			foreach($pars as $name=>$value)
				$p[$name]=$name.'='.var_export($value,true);
			$par='. Bound with ' .implode(', ',$p);
		}
		else
			$par='';

		try
		{

			$this->prepare();
			if($params===[])
				$this->_statement->execute();
			else
				$this->_statement->execute($params);
			$n=$this->_statement->rowCount();

			return $n;
		}
		catch(Exception $e)
		{

			$errorInfo=$e instanceof \PDOException ? $e->errorInfo : null;
			$message=$e->getMessage();
			\Kohana::$log->add(\Log::EMERGENCY,'CDbCommand::execute() failed: {'.$message.'}. The SQL statement executed was: {'.$this->getText().$par.'}.');

			if(\Kohana::DEVELOPMENT)
			{
				$message.='. The SQL statement executed was: '.$this->getText().$par;
			}


			throw new Exception('CDbCommand failed to execute the SQL statement: {'.$message.'}',(int)$e->getCode(),$errorInfo);
		}
	}
}
