<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Common\Log as AbstractLog;
use Psr\Log\AbstractLogger;

class Log extends AbstractLog
{
	public function log( $level, $message, array $context=array() )
	{
		S::$r->_(
			'log',
			array_merge(
				array(
					'created' => S::$r->isoDateTime(),
					'level' => $level,
					'message' => $message
				),
				$context
			),
			true
		);
	}
}
