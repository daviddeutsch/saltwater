<?php

namespace Saltwater\Water;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Salt\Context;
use Saltwater\Salt\Module;
use Saltwater\Salt\Provider;

class ModuleFinder
{
	/**
	 * Return the master context for the current master module
	 *
	 * @param Context|null $parent inject a parent context
	 *
	 * @return Context
	 */
	public function masterContext( $parent=null )
	{
		$stack = S::$n->modules->getStack();

		foreach ( S::$n->modules as $name => $module ) {
			/** @var Module $module */
			if ( $module->lacksContext() ) continue;

			$parent = S::$n->context->get($module->masterContext(), $parent);

			if ( $stack->isMaster($name) ) break;
		}

		return $parent;
	}

	/**
	 * @param int    $bit
	 * @param string $caller
	 * @param string $type
	 *
	 * @return bool|Provider
	 */
	public function provider( $bit, $caller, $type )
	{
		$stack = S::$n->modules->getStack();

		// Depending on the caller, reset the module stack
		$previous_master = $stack->setMaster($caller);

		foreach ( S::$n->modules->precedenceList() as $module ) {
			$return = $this->providerFromModule($module, $bit, $caller, $type);

			if ( $return ) {
				$stack->setMaster($previous_master);

				return $return;
			}
		}

		$stack->setMaster($previous_master);

		return $this->tryModuleFallback($bit, $type);
	}

	/**
	 * @param Module $module
	 * @param string $name
	 * @param int    $bit
	 * @param string $caller
	 * @param string $type
	 *
	 * @return Provider|bool
	 */
	private function providerFromModule( $module, $bit, $caller, $type )
	{
		if ( !$module->has($bit) ) return false;

		return $module->provider($caller, $type);
	}

	/**
	 * @param integer $bit
	 * @param string $type
	 *
	 * @return Provider|bool
	 */
	private function tryModuleFallback( $bit, $type )
	{
		// As a last resort, step one module up within stack and try again
		if ( $caller = S::$n->modules->getStack()->advanceMaster() ) {
			return $this->provider($bit, $caller, $type);
		}

		return false;
	}

	/**
	 * Find the module of a caller class
	 *
	 * @param array  $caller
	 * @param string $provider
	 *
	 * @return string module name
	 */
	public function find( $caller, $provider )
	{
		list(, $salt, $namespace) = U::extractFromClass($caller);

		$is_provider = $salt == $provider;

		$bit = S::$n->registry->bit($salt);

		foreach ( S::$n->modules->reverse() as $name => $module ) {
			/** @var Module $module */
			if ( $this->check($module, $is_provider, $bit, $namespace) ) {
				return $name;
			}
		}

		return null;
	}

	/**
	 * @param Module $module
	 * @param bool   $is_provider
	 * @param int    $bit
	 * @param string $namespace
	 *
	 * @return bool
	 */
	private function check( $module, $is_provider, $bit, $namespace )
	{
		if ( $is_provider === ($module::getNamespace() == $namespace) ) {
			return false;
		}

		return $module->has($bit);
	}

	/**
	 * Return top candidate Module for providing a Salt
	 *
	 * @param string $salt
	 *
	 * @return bool|mixed
	 */
	public function moduleBySalt( $salt )
	{
		return $this->modulesBySalt($salt, true);
	}

	/**
	 * Return a list of Modules providing a Salt
	 *
	 * @param string $salt
	 * @param bool   $first      only return the first item on the list
	 *
	 * @return array|bool
	 */
	public function modulesBySalt( $salt, $first=false )
	{
		if ( !S::$n->registry->exists($salt) ) return false;

		$call = $first ? 'getSaltModule' : 'getSaltModules';

		return $this->$call(
			S::$n->registry->bit($salt),
			S::$n->modules->precedenceList()
		);
	}

	/**
	 * Find all the modules that provide a bit
	 *
	 * @param int  $bit
	 * @param null $modules
	 *
	 * @return array
	 */
	public function getSaltModules( $bit, $modules=null )
	{
		$return = array();
		foreach ( $modules ?: S::$n->modules as $module ) {
			/** @var Module $module */
			if ( $module->has($bit) ) $return[] = $module::getName();
		}

		return $return;
	}

	/**
	 * Find the most likely module to provide a bit
	 * @param int  $bit
	 * @param null $modules
	 *
	 * @return bool
	 */
	public function getSaltModule( $bit, $modules=null )
	{
		foreach ( $modules ?: S::$n->modules as $module ) {
			/** @var Module $module */
			if ( $module->has($bit) ) return $module::getName();
		}

		return false;
	}
}
