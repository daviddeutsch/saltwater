<?php

namespace Saltwater\Blog\Context;

use Saltwater\Thing\Context;

class Blog extends Context
{
	public $namespace = 'Saltwater\Blog';

	public $services = array(
		'article', 'comment'
	);
}
