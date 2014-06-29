<?php

namespace Saltwater\Water;

use Saltwater\Server as S;

class Backtrace extends \ArrayIterator
{
	/** @var string[] skipped classes during search for caller module */
	private static $skip = array(
		'Saltwater\Navigator',
		'Saltwater\Server'
	);

	public function __construct()
	{
		// Let me tell you about my boat
		if ( S::$gt['54'] ) {
			return parent::__construct( debug_backtrace(2, 22) );
		} else {
			return parent::__construct( debug_backtrace(false) );
		}
	}

	/**
	 * Shorthand for generating a Backtrace and returning the last caller
	 *
	 * @return array|null
	 */
	public static function lastCaller()
	{
		$backtrace = new Backtrace;

		return $backtrace->extractCaller();
	}

	/**
	 * Find and extract the last non-saltwater core class calling
	 *
	 * @return array|null
	 */
	public function extractCaller()
	{
		while ( $this->valid() ) {
			$current = $this->current();

			if (
				isset($current['class'])
				&& !$this->skipCaller($current['class'])
			) {
				return explode('\\', $current['class']);
			}
		}

		return S::$n->modules->getStack()->getMaster();
	}

	/**
	 * Check whether a caller class can be skipped
	 *
	 * @param string $class
	 *
	 * @return bool
	 */
	private function skipCaller( $class )
	{
		return (strpos($class, 'Saltwater\Root') !== false)
		|| (strpos($class, '\\') === false)
		|| in_array($class, self::$skip);
	}
}
