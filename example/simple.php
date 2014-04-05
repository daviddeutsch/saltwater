<?php

// Include daviddeutsch/redbean-adaptive and ../saltwater
require 'path/rb.php';
require 'path/sw.php';

// Creating our root context
class Context_Example extends Saltwater_Context_Context
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

S::init(new Context_Example());

S::route();

/*
 * You can now:
 *
 * GET|POST|DELETE /article{/:id}
 * GET|POST|DELETE /comment{/:id}
 *
 * That's neat, but comments should be related to articles.
 *
 * To do that, let's create the concept of a 'discussion':
 */

// --- NOTICE: THE STUFF AFTER THIS LINE IS NOT YET FULLY IMPLEMENTED ----

/*
 * This service wraps a standard REST service to also create a discussion
 * entry when an article is saved
 */
class Example_Service_Article extends Saltwater_Service_Rest
{
	/*
	 * POST /article
	 */
	public function postArticle( $call, $data=null )
	{
		// Query the standard REST service
		$return = $this->restCall($call, $data);

		// Get the article we've just created
		$article = S::$r->_('article', $return->id);

		// Create a new discussion related to the article if there is none
		S::$r->x->one->discussion->related($article)->find(true);

		return $return;
	}
}

/*
 * Next up, we create a new context that can respond to the 'Info' Service
 *
 * (note that contexts always sit in the main saltwater namespace)
 */
class Saltwater_Context_Discussion extends Saltwater_Context_Context
{
	/*
	 * GET {...}/discussion/info
	 */
	public function getInfo() {
		// Get the article that was passed in
		$article = S::$r->_('article', $this->data->id);

		return S::$r->x->one->discussion->related($article->id);
	}
}

class Example_Service_Comment extends Saltwater_Service_Rest
{
	public function postComment()
	{

	}
}

/*
 * Now, you can also:
 *
 *                                               What's happening here?
 * GET /article/:id/discussion/info
 *         └───────────────────────   Take the article with the id :id
 *                      │       │
 *                      └───────────╴   Hand it into a discussion context
 *                              │
 *                              └────╴   What do we know about this?
 *
 * Which returns
 *
 * {id:#}
 *
 * So now we know the id of the discussion.
 *
 * Then, you can do:
 *
 * GET /discussion/:id/comment
 *         └───────────────────────   For the discussion with the id :id
 *                      │
 *                      └───────────╴   Give me a list of all the comments
 */


/*
 * But what if we want to listen on the changes in realtime?
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
 * Now that the server knows us, we can subscribe to the comment thread:
 *
 * POST /hook/subscribe {resource:'discussion/:id/comment'}
 *
 * No matter what we are subscribed to, we can use this to query the server for
 * all new updates that are available:
 *
 * GET /hook/updates
 */
