<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Common\Log as AbstractLog;

class Log extends AbstractLog
{
	protected function __construct() {}

	public static function get()
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
