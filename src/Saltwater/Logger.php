<?php

namespace Saltwater;

use Saltwater\Server as S;
use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger
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
