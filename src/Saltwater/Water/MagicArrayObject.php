<?php

namespace Saltwater\Water;

/**
 * Magic Array Object
 *
 * @package Saltwater\Water
 *
 * Same as the above, but as methods for injecting a caller
 *
 * @method array change_key_case()   — Changes the case of all keys in an array
 * @method array chunk()             — Split an array into chunks
 * @method array column()            — Return the values from a single column in
 *                                     the input array
 * @method array combine()           — Creates an array by using one array for
 *                                     keys and another for its values
 * @method int   count_values()      — Counts all the values of an array
 * @method array diff_assoc()        — Computes the difference of arrays with
 *                                     additional index check
 * @method array diff_key()          — Computes the difference of arrays using
 *                                     keys for comparison
 * @method array diff_uassoc()       — Computes the difference of arrays with
 *                                     additional index check which is performed
 *                                     by a user supplied callback function
 * @method array diff_ukey()         — Computes the difference of arrays using a
 *                                     callback function on the keys for
 *                                     comparison
 * @method array diff()              — Computes the difference of arrays
 * @method array fill_keys()         — Fill an array with values, specifying
 *                                     keys
 * @method array fill()              — Fill an array with values
 * @method array filter()            — Filters elements of an array using a
 *                                     callback function
 * @method array flip()              — Exchanges all keys with their associated
 *                                     values in an array
 * @method array intersect_assoc()   — Computes the intersection of arrays with
 *                                     additional index check
 * @method array intersect_key()     — Computes the intersection of arrays using
 *                                     keys for comparison
 * @method array intersect_uassoc()  — Computes the intersection of arrays with
 *                                     additional index check, compares indexes
 *                                     by a callback function
 * @method array intersect_ukey()    — Computes the intersection of arrays using
 *                                     a callback function on the keys for
 *                                     comparison
 * @method array intersect()         — Computes the intersection of arrays
 * @method array key_exists()        — Checks if the given key or index exists
 *                                     in the array
 * @method array keys()              — Return all the keys or a subset of the
 *                                     keys of an array
 * @method array map()               — Applies the callback to the elements of
 *                                     the given arrays
 * @method array merge_recursive()   — Merge two or more arrays recursively
 * @method array merge()             — Merge one or more arrays
 * @method array multisort()         — Sort multiple or multi-dimensional arrays
 * @method array pad()               — Pad array to the specified length with a
 *                                     value
 * @method mixed pop()               — Pop the element off the end of the array
 * @method array product()           — Calculate the product of values in an
 *                                     array
 * @method array push()              — Push one or more elements onto the end of
 *                                     the array
 * @method mixed rand()              — Pick one or more random entries out of an
 *                                     array
 * @method mixed reduce()            — Iteratively reduce the array to a single
 *                                     value using a callback function
 * @method array replace_recursive() — Replaces elements from passed arrays into
 *                                     the first array recursively
 * @method array replace()           — Replaces elements from passed arrays into
 *                                     the first array
 * @method array reverse()           — Return an array with elements in reverse
 *                                     order
 * @method mixed search()            — Searches the array for a given value and
 *                                     returns the corresponding key if successful
 * @method mixed shift()             — Shift an element off the beginning of
 *                                     the array
 * @method mixed slice()             — Extract a slice of the array
 * @method array splice()            — Remove a portion of the array and replace
 *                                     it with something else
 * @method mixed sum()               — Calculate the sum of values in an array
 * @method array udiff_assoc()       — Computes the difference of arrays with
 *                                     additional index check, compares data by
 *                                     a callback function
 * @method array udiff_uassoc()      — Computes the difference of arrays with
 *                                     additional index check, compares data and
 *                                     indexes by a callback function
 * @method array udiff()             — Computes the difference of arrays by
 *                                     using a callback function for data
 *                                     comparison
 * @method array uintersect_assoc()  — Computes the intersection of arrays with
 *                                     additional index check, compares data by
 *                                     a callback function
 * @method array uintersect_uassoc() — Computes the intersection of arrays with
 *                                     additional index check, compares data and
 *                                     indexes by a callback functions
 * @method array uintersect()        — Computes the intersection of arrays,
 *                                     compares data by a callback function
 * @method array unique()            — Removes duplicate values from an array
 * @method array unshift()           — Prepend one or more elements to the
 *                                     beginning of an array
 * @method array values()            — Return all the values of an array
 * @method array walk_recursive()    — Apply a user function recursively to
 *                                     every member of an array
 * @method array walk()              — Apply a user function to every member of
 *                                     an array
 */
class MagicArrayObject extends \ArrayObject
{
	/**
	 * @param string $func
	 * @param array $argv
	 *
	 * @return mixed
	 *
	 * @throws \BadMethodCallException
	 */
	public function __call( $func, $argv )
	{
		$func = 'array_' . $func;

		if ( !is_callable($func) ) {
			throw new \BadMethodCallException(__CLASS__ . '->' . $func);
		}

		if ( $func == 'array_search' ) {
			return call_user_func_array(
				$func,
				array_merge( $argv, array( (array) $this ) )
			);
		} else {
			return call_user_func_array(
				$func,
				array_merge( array( (array) $this ), $argv )
			);
		}
	}

}
