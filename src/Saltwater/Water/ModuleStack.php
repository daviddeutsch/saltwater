<?php

namespace Saltwater\Water;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Salt\Module;
use Saltwater\Salt\Context;
use Saltwater\Salt\Provider;

class ModuleStack extends \ArrayObject
{
	/**
	 * @var TempStack
	 */
	private $stack;

	public function __construct()
	{
		$this->stack = new TempStack;
	}

	/**
	 * Add module to stack and register its Salts
	 *
	 * @param Module|string $class  Full Classname
	 * @param bool          $master true if this is also the master module
	 *
	 * @return bool|null
	 */
	public function appendModule( $class, $master=false )
	{
		if (
			!class_exists($class)
			|| isset($this[$class::getName()])
			|| !($module = $this->registeredModule($class))
		) return false;

		// Push to stack after registering - to preserve order of dependencies
		$this[$class::getName()] = $module;

		if ( $master ) $this->stack->setMaster($class::getName());

		return true;
	}

	/**
	 * Return a module class by its name
	 *
	 * @param string $name
	 *
	 * @return Module
	 */
	public function getModule( $name )
	{
		return $this[$name];
	}

	/**
	 * @param string|Module $class
	 *
	 * @return Module
	 */
	private function registeredModule( $class )
	{
		/** @var Module $module */
		$module = new $class;

		$module->register($class::getName());

		return $module;
	}

	/**
	 * Return the master context for the current master module
	 *
	 * @param Context|null $parent inject a parent context
	 *
	 * @return Context
	 */
	public function masterContext( $parent=null )
	{
		foreach ( $this->getList() as $name => $module ) {
			if ( $module->lacksContext() ) continue;

			$parent = S::$n->context->get($module->masterContext(), $parent);

			if ( $this->stack->isMaster($name) ) break;
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
		// Depending on the caller, reset the module stack
		$previous_master = $this->stack->setMaster($caller);

		foreach ( $this->precedenceList() as $module ) {
			$return = $this->providerFromModule($module, $bit, $caller, $type);

			if ( $return ) {
				$this->stack->setMaster($previous_master);

				return $return;
			}
		}

		$this->stack->setMaster($previous_master);

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
		if ( $caller = $this->stack->advanceMaster() ) {
			return $this->provider($bit, $caller, $type);
		}

		return false;
	}

	/**
	 * Find the module of a caller class
	 *
	 * @param array|null $caller
	 * @param string     $provider
	 *
	 * @return string module name
	 */
	public function findModule( $caller, $provider )
	{
		if ( empty($caller) ) return $this->stack->getMaster();

		$check = $this->moduleChecker($caller, $provider);

		/** @var Module $module */
		foreach ( $this->getReverseList() as $k => $module ) {
			if ( $check($module) ) return $k;
		}

		return null;
	}

	/**
	 * @param array  $caller
	 * @param string $provider
	 *
	 * @return callable
	 */
	private function moduleChecker( $caller, $provider )
	{
		list(, $salt, $namespace) = U::extractFromClass($caller);

		$is_provider = $salt == $provider;

		$bit = S::$n->bitSalt($salt);

		/**
		 * A provider calling itself always gets a lower level provider
		 *
		 * The if is a condensed and inverted version of:
		 *
		 * ($c->is_provider && $same_ns) || (!$c->is_provider && !$same_ns)
		 */
		return function($module) use ($is_provider, $bit, $namespace) {
			/** @var Module $module */
			if ( $is_provider === ($module::getNamespace() == $namespace) ) {
				return false;
			}

			return $module->has($bit);
		};
	}

	/**
	 * Return top candidate Module for providing a Salt
	 *
	 * @param string $salt
	 * @param bool   $precedence Use the current module precedence rules
	 *
	 * @return bool|mixed
	 */
	public function moduleBySalt( $salt, $precedence=true )
	{
		return $this->modulesBySalt($salt, $precedence, true);
	}

	/**
	 * Return a list of Modules providing a Salt
	 *
	 * @param string $salt
	 * @param bool   $precedence
	 * @param bool   $first      only return the first item on the list
	 *
	 * @return array|bool
	 */
	public function modulesBySalt( $salt, $precedence=true, $first=false )
	{
		if ( !S::$n->isSalt($salt) ) return false;

		$call = $first ? 'getSaltModule' : 'getSaltModules';

		return $this->$call(
			S::$n->bitSalt($salt),
			$precedence ? $this->precedenceList() : $this->getReverseList()
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
		foreach ( $modules ?: (array) $this as $module ) {
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
		foreach ( $modules ?: (array) $this as $module ) {
			/** @var Module $module */
			if ( $module->has($bit) ) return $module::getName();
		}

		return false;
	}

	/**
	 * @return Module[]
	 */
	private function getReverseList()
	{
		return array_reverse( (array) $this );
	}

	/**
	 * @return Module[]
	 */
	private function precedenceList()
	{
		$return = array();
		foreach ( $this->stack->modulePrecedence() as $name ) {
			$return[] = $this[$name];
		}

		return $return;
	}
}
