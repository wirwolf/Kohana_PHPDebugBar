<?php
/**
 * Created by Wir_Wolf.
 * Author: Andru Cherny
 * E-mail: wir_wolf@bk.ru
 * Date: 07.09.14
 * Time: 21:45
 */

namespace DebugBar\DataFormatter\Nice;


class Resource {

	public static function get($node)
	{
		$meta = [];
		$resType = get_resource_type($var);

		// @see: http://php.net/manual/en/resource.php
		// need to add more...
		switch($resType)
		{

			// curl extension resource
			case 'curl':
				$meta = curl_getinfo($var);
				break;

			case 'FTP Buffer':
				$meta = [
					'time_out'  => ftp_get_option($var, FTP_TIMEOUT_SEC),
					'auto_seek' => ftp_get_option($var, FTP_AUTOSEEK),
				];

				break;

			// gd image extension resource
			case 'gd':
				$meta = [
					'size'       => sprintf('%d x %d', imagesx($var), imagesy($var)),
					'true_color' => imageistruecolor($var),
				];

				break;

			case 'ldap link':
				$constants = get_defined_constants();

				array_walk($constants, function ($value, $key) use (&$constants)
				{
					if(strpos($key, 'LDAP_OPT_') !== 0)
					{
						unset($constants[$key]);
					}
				});

				// this seems to fail on my setup :(
				unset($constants['LDAP_OPT_NETWORK_TIMEOUT']);

				foreach(array_slice($constants, 3) as $key => $value)
				{
					if(ldap_get_option($var, (int)$value, $ret))
					{
						$meta[strtolower(substr($key, 9))] = $ret;
					}
				}

				break;

			// mysql connection (mysql extension is deprecated from php 5.4/5.5)
			case 'mysql link':
			case 'mysql link persistent':
				$dbs = [];
				$query = @mysql_query('SHOW DATABASES');
				while($row = @mysql_fetch_array($query))
				{
					$dbs[] = $row['Database'];
				}

				$meta = [
					'host'             => ltrim(@mysql_get_host_info($var), 'MySQL host info: '),
					'server_version'   => @mysql_get_server_info($var),
					'protocol_version' => @mysql_get_proto_info($var),
					'databases'        => $dbs,
				];

				break;

			// mysql result
			case 'mysql result':
				while($row = @mysql_fetch_object($var))
				{
					$meta[] = (array)$row;
				}

				break;

			// stream resource (fopen, fsockopen, popen, opendir etc)
			case 'stream':
				$meta = stream_get_meta_data($var);
				break;

		}

		if(!$meta)
		{

		}

		$max = max(array_map('static::strLen', array_keys($meta)));
		foreach($meta as $key => $value)
		{

		}
		return $node;
	}
}