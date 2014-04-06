<?php

// Include daviddeutsch/redbean-adaptive and ../saltwater
require 'path/rb.php';
require 'path/sw.php';

/*
 * Sorry, using the one-file library, also why class names are underspaced
 * instead of namespaced
 */

// Creating our root context
class Example_Context extends Saltwater_Context_Context
{
	public $service = array(
		'article', 'comment'
	);

	public $namespace = 'Example';

	public function __construct()
	{
		$this->config = new stdClass();

		// Load your database connection details into config->database
		$this->config->database = new stdClass();
	}
}

S::init( new Example_Context() );

S::route();

/*
 * You can now:
 *
 * GET|POST|DELETE /article{/:id}
 * GET|POST|DELETE /comment{/:id}
 */

/*
 * That's neat, but comments should be related to things, so let's make it
 * possible to relate them to stuff:
 */

class Example_Service_Comment extends Saltwater_Service_Rest
{
	/*
	 * GET /comment
	 * GET /article/:id/comment
	 */
	public function getComment( $call, $data=null )
	{
		if ( !empty($this->context->data) ) {
			$comments = S::$r->related($this->context->data, 'comment');

			if ( !is_array($comments) ) $comments = array($comments);

			foreach ( $comments as $i => $comment ) {
				$comments[$i] = $comment->export();
			}

			return $comments;
		} else {
			return $this->restCall($call, $data);
		}
	}

	/*
	 * POST /comment
	 * POST /article/:id/comment
	 */
	public function postComment( $call, $data=null )
	{
		$comment = $this->restCall($call, $data);

		if ( !empty($this->context->data) ) {
			S::$r->associate($this->context->data, $comment);
		}
	}
}

/*
 * Now that we have this in place, we can also declare further services, because
 * unless you limit the capabilities, everything in the system can now be
 * commented on:
 */

class Extended_Example_Context extends Saltwater_Context_Context
{
	public $service = array(
		'article', 'video', 'thread', 'comment' //...
	);
}

/*
 * POST /article/:id/comment
 * POST /video/:id/comment
 * POST /thread/:id/comment
 * etc.
 *
 * ...even comments:
 *
 * POST /comment/:id/comment
 */

// --- NOTICE: THE STUFF AFTER THIS LINE IS NOT YET FULLY IMPLEMENTED ----

/*
 * But what if we want to listen on the changes in realtime?
 *
 * For that, we have a basic Pub/Sub Hook system set up.
 *
 * Let's tell the server that want to hear if new comments have been added or
 * changed related to a certain article.
 *
 * First, we tell the server who we are:
 *
 * POST /session {}
 *
 * Which returns:
 *
 * {token:"randomtoken"}
 *
 * From now on, we will include this in our header like so:
 *
 * Authorization: Basic randomtoken
 *
 * To have the server track changes in the pipeline, we need to create
 * RedBean models for them:
 */

class Example_Model_Article extends Saltwater_Model_Model {}
class Example_Model_Comment extends Saltwater_Model_Model {}

/*
 * We also want models for the associations:
 */

class Example_Model_Article_Comment extends Saltwater_Model_AssociationModel {}
class Example_Model_Comment_Comment extends Saltwater_Model_AssociationModel {}

/*
 * Now that the server knows us and we have the models in place, we can
 * subscribe to the comment thread:
 *
 * POST /hook/subscribe {resource:'article/:id/comment'}
 *
 * No matter what we are subscribed to, we can use this to query the server for
 * all new updates that are available:
 *
 * GET /hook/updates
 *
 * and receive a list with individual objects for all our subscriptions
 */
