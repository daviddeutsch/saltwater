<?php

namespace Saltwater\Blog\Context;

use Saltwater\Salt\Context;

class Blog extends Context
{
	public $namespace = 'Saltwater\Blog';

	public $services = array(
		'article', 'comment'
	);
}
