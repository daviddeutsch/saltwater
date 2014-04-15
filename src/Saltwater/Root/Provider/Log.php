<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Common\Log as AbstractLog;
use Psr\Log\AbstractLogger;

class Log extends AbstractLog
{
	private function __construct() {}

	public static function get( $input=null )
	{
		return new Log();
	}

	public function log( $level, $message, array $context=array() )
	{
		S::$n->db->_(
			'log',
			array_merge(
				array(
					'created' => S::$n->db->isoDateTime(),
					'level' => $level,
					'message' => $message
				),
				$context
			),
			true
		);
	}
}
