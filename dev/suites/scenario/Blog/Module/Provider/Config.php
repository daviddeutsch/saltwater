<?php
namespace Saltwater\Blog\Provider;

use Saltwater\Server as S;
use Saltwater\Thing\Provider;

class Config extends Provider
{
	public static function getProvider()
	{
		return (object) array(
			'database' => (object) array(
				'name' => 'default',
				'dsn' => 'sqlite:/tmp/oodb.db',
				'user' => null,
				'password' => null
			)
		);
	}
}
