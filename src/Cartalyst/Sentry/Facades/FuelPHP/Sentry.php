<?php namespace Cartalyst\Sentry\Facades\FuelPHP;
/**
 * Part of the Sentry Package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Sentry
 * @version    2.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Cartalyst\Sentry\Cookies\FuelPHPCookie;
use Cartalyst\Sentry\Groups\Eloquent\Provider as GroupProvider;
use Cartalyst\Sentry\Hashing\NativeHasher;
use Cartalyst\Sentry\Sessions\FuelPHPSession;
use Cartalyst\Sentry\Sentry as BaseSentry;
use Cartalyst\Sentry\Throttling\Eloquent\Provider as ThrottleProvider;
use Cartalyst\Sentry\Users\Eloquent\Provider as UserProvider;
use Database_Connection;
use Illuminate\Database\Eloquent\Model as Eloquent;
use PDO;

class Sentry {

	/**
	 * Sentry instance.
	 *
	 * @var Cartalyst\Sentry\Sentry
	 */
	protected static $instance;

	public static function instance()
	{
		if (static::$instance === null)
		{
			static::$instance = static::createSentry();
		}

		return static::$instance;
	}

	/**
	 * Creates an instance of Sentry.
	 *
	 * @return Cartalyst\Sentry\Sentry
	 */
	public static function createSentry()
	{
		$hasher           = new NativeHasher;
		$session          = new FuelPHPSession(\Session::instance());
		$cookie           = new FuelPHPCookie;
		$groupProvider    = new GroupProvider;
		$userProvider     = new UserProvider($hasher);
		$throttleProvider = new ThrottleProvider($userProvider);

		static::createDatabaseResolver();

		return new BaseSentry(
			$hasher,
			$session,
			$cookie,
			$groupProvider,
			$userProvider,
			$throttleProvider
		);
	}

	public static function createDatabaseResolver()
	{
		// Retrieve what we need for our resolver
		$database    = Database_Connection::instance();
		$pdo         = $database->connection();
		$driverName  = $database->driver_name();
		$tablePrefix = $database->table_prefix();

		// Make sure we're getting a PDO connection
		if ( ! $pdo instanceof PDO)
		{
			throw new \RuntimeException("Sentry will only work with PDO database connections.");
		}

		Eloquent::setConnectionResolver(new ConnectionResolver($pdo, $driverName, $tablePrefix));
	}

	/**
	 * Handle dynamic, static calls to the object.
	 *
	 * @param  string  $method
	 * @param  array   $args
	 * @return mixed
	 */
	public static function __callStatic($method, $args)
	{
		$instance = static::instance();

		switch (count($args))
		{
			case 0:
				return $instance->$method();

			case 1:
				return $instance->$method($args[0]);

			case 2:
				return $instance->$method($args[0], $args[1]);

			case 3:
				return $instance->$method($args[0], $args[1], $args[2]);

			case 4:
				return $instance->$method($args[0], $args[1], $args[2], $args[3]);

			default:
				return call_user_func_array(array($instance, $method), $args);
		}
	}

}