<?php
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

use Mockery as m;
use Cartalyst\Sentry\Groups\Eloquent\Provider;

class EloquentGroupProviderTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 * 
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testFindingById()
	{
		$provider = m::mock('Cartalyst\Sentry\Groups\Eloquent\Provider[createModel]');

		$query = m::mock('StdClass');
		$query->shouldReceive('newQuery')->andReturn($query);
		$query->shouldReceive('find')->with(1)->once()->andReturn('foo');

		$provider->shouldReceive('createModel')->once()->andReturn($query);

		$this->assertEquals('foo', $provider->findById(1));
	}

	/**
	 * @expectedException Cartalyst\Sentry\Groups\GroupNotFoundException
	 */
	public function testFailedFindingByIdThrowsExceptionIfNotFound()
	{
		$provider = m::mock('Cartalyst\Sentry\Groups\Eloquent\Provider[createModel]');

		$query = m::mock('StdClass');
		$query->shouldReceive('newQuery')->andReturn($query);
		$query->shouldReceive('find')->with(1)->once()->andReturn(null);

		$provider->shouldReceive('createModel')->once()->andReturn($query);

		$provider->findById(1);
	}

	public function testFindingByName()
	{
		$provider = m::mock('Cartalyst\Sentry\Groups\Eloquent\Provider[createModel]');

		$query = m::mock('StdClass');
		$query->shouldReceive('newQuery')->andReturn($query);
		$query->shouldReceive('where')->with('name', '=', 'foo')->once()->andReturn($query);
		$query->shouldReceive('first')->once()->andReturn('bar');

		$provider->shouldReceive('createModel')->once()->andReturn($query);

		$this->assertEquals('bar', $provider->findByName('foo'));
	}

	/**
	 * @expectedException Cartalyst\Sentry\Groups\GroupNotFoundException
	 */
	public function testFailedFindingByNameThrowsExceptionIfNotFound()
	{
		$provider = m::mock('Cartalyst\Sentry\Groups\Eloquent\Provider[createModel]');

		$query = m::mock('StdClass');
		$query->shouldReceive('newQuery')->andReturn($query);
		$query->shouldReceive('where')->with('name', '=', 'foo')->once()->andReturn($query);
		$query->shouldReceive('first')->andReturn(null);

		$provider->shouldReceive('createModel')->once()->andReturn($query);

		$provider->findByName('foo');
	}

	public function testCreatingGroup()
	{
		$attributes = array(
			'name' => 'foo',
		);

		$group = m::mock('Cartalyst\Sentry\Groups\EloquentGroup');
		$group->shouldReceive('fill')->with($attributes)->once();
		$group->shouldReceive('save')->once();

		$provider = m::mock('Cartalyst\Sentry\Groups\Eloquent\Provider[createModel]');
		$provider->shouldReceive('createModel')->once()->andReturn($group);

		$this->assertEquals($group, $provider->create($attributes));
	}

}