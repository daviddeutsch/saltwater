<?php

namespace Saltwater\RedBean\Provider;

use Saltwater\Server as S;
use Saltwater\App\Common\Log as AbstractLog;

class Log extends AbstractLog
{
	public static function getProvider() { return new Log; }

	/**
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return array|int|null|\RedBean_OODBBean
	 */
	public function log( $level, $message, array $context=array() )
	{
		$db = S::$n->db;

		return $db->_(
			'log',
			array_merge(
				array(
					'created' => $db->isoDateTime(),
					'level' => $level,
					'message' => $message
				),
				$context
			),
			true
		);
	}
}
