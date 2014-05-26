<?php

namespace Saltwater\Test\Provider;

use Saltwater\App\Common\Config as AbstractConfig;

class Config extends AbstractConfig
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
