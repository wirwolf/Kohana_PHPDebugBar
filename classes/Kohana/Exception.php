<?php
namespace Kohana;

class Exception extends Kohana\Exception{

	/**
	 * @var  string  error rendering view
	 */
	public static $error_view = 'Error/Error';

	/**
	 * Get a Response object representing the exception
	 * @uses    \Kohana\Exception::text
	 * @param \Exception|\Kohana\Kohana\Exception $e
	 * @return  \Response
	 */
	public static function response(\Exception $e)
	{
		try
		{
			\Registry::instance()->DebugBar['exceptions']->addException($e);
			// Get the exception information
			$class   = get_class($e);
			$code    = $e->getCode();
			$message = $e->getMessage();
			$file    = $e->getFile();
			$line    = $e->getLine();
			$trace   = $e->getTrace();

			/**
			 * \Kohana\HTTP\Exceptions are constructed in the \Kohana\HTTP\Exception::factory()
			 * method. We need to remove that entry from the trace and overwrite
			 * the variables from above.
			 */
			if ($e instanceof \Kohana\HTTP\Exception AND $trace[0]['function'] == 'factory')
			{
				extract(array_shift($trace));
			}


			if ($e instanceof \ErrorException)
			{
				/**
				 * If XDebug is installed, and this is a fatal error,
				 * use XDebug to generate the stack trace
				 */
				if (function_exists('xdebug_get_function_stack') AND $code == E_ERROR)
				{
					$trace = array_slice(array_reverse(xdebug_get_function_stack()), 4);

					foreach ($trace as & $frame)
					{
						/**
						 * XDebug pre 2.1.1 doesn't currently set the call type key
						 * http://bugs.xdebug.org/view.php?id=695
						 */
						if ( ! isset($frame['type']))
						{
							$frame['type'] = '??';
						}

						// XDebug also has a different name for the parameters array
						if (isset($frame['params']) AND ! isset($frame['args']))
						{
							$frame['args'] = $frame['params'];
						}
					}
				}

				if (isset(\Kohana\Exception::$php_errors[$code]))
				{
					// Use the human-readable error name
					$code = \Kohana\Exception::$php_errors[$code];
				}
			}

			/**
			 * The stack trace becomes unmanageable inside PHPUnit.
			 *
			 * The error view ends up several GB in size, taking
			 * serveral minutes to render.
			 */
			if (defined('PHPUnit_MAIN_METHOD'))
			{
				$trace = array_slice($trace, 0, 2);
			}
			// Prepare the response object.
			$response = \Response::factory();


			// Set the response status
			$response->status(($e instanceof \Kohana\HTTP\Exception) ? $e->getCode() : 500);

			// Set the response headers
			$response->headers('Content-Type', \Kohana\Exception::$error_view_content_type.'; charset='.\Kohana::$charset);

			if (PHP_SAPI == 'cli' && class_exists('\Minion\Task',true))
			{
				echo ($e->getMessage());
				return $response;
			}
			else
			{
                #TODO fix bug if error 500 Request::current() is null
				if(\Request::current() and \Request::current()->is_ajax())
				{
					\Registry::instance()->DebugBar->sendDataInHeaders(true,'phpdebugbar',6121600);
				}
				else
				{
					// Instantiate the error view.
					$view = \View::factory(\Kohana\Exception::$error_view, get_defined_vars());

					// Set the response body
					$response->body($view->render());
				}
			}

		}
		catch (\Exception $e)
		{
			/**
			 * Things are going badly for us, Lets try to keep things under control by
			 * generating a simpler response object.
			 */
			$response = \Response::factory();
			$response->status(500);
			$response->headers('Content-Type', 'text/plain');
			\Registry::instance()->DebugBar['exceptions']->addException($e);
			#TODO fix bug if error 500 Request::current() is null
			if(\Request::current() and \Request::current()->is_ajax())
			{
				\Registry::instance()->DebugBar->sendDataInHeaders(true,'phpdebugbar',6121600);
			}
			else
			{
				$response->body(\Kohana\Exception::text($e));
			}

		}
		return $response;
	}
}
