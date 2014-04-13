<?php

namespace Saltwater\Root\Service;

use Saltwater\Server as S;
use Saltwater\Root\Service\Rest;
use Psr\Log\LoggerInterface;

class Log extends Rest implements LoggerInterface
{
	public function emergency($message, array $context = array())
	{
		$this->log('emergency', $message, $context);
	}

	public function alert($message, array $context = array())
	{
		$this->log('alert', $message, $context);
	}

	public function critical($message, array $context = array())
	{
		$this->log('critical', $message, $context);
	}

	public function error($message, array $context = array())
	{
		$this->log('error', $message, $context);
	}

	public function warning($message, array $context = array())
	{
		$this->log('warning', $message, $context);
	}

	public function notice($message, array $context = array())
	{
		$this->log('notice', $message, $context);
	}

	public function info($message, array $context = array())
	{
		$this->log('info', $message, $context);
	}

	public function debug($message, array $context = array())
	{
		$this->log('debug', $message, $context);
	}

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
