<?php

// Load tree of files
// Iterate through files, files before folders
// Take note of local namespaces and 'use's
// Erase them
// Go through the code, search and replace with prefixed version
// Write everything into a target file

$convert = new Canonizer(__DIR__.'/../src');

$convert->convert(__DIR__.'/../dist/sw.php');

class Canonizer
{
	public function __construct( $source )
	{

	}
}
