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

class Example_Service_Article extends Saltwater_Service_Rest
{
	public function call( $call, $data=null )
	{
		// Query the standard REST service
		$return = parent::call($call, $data);

		if ( $call->http == 'post' ) {
			// Get the article we've just created
			$article = S::$r->_('article', $return->id);

			// Create a new discussion related to the article if there is none
			S::$r->x->one->discussion->related($article)->find(true);
		}

		return $return;
	}
}

class Example_Context_Discussion extends Saltwater_Context_Context
{
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
 * POST /article/:id/discussion/comment
 *
 * GET /article/:id/discussion/info
 *
 * Which returns
 *
 * {id:#}
 *
 * Then, you can do
 *
 * GET /discussion/:id/comment
 *
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
 * which returns: {token:"randomtoken"}
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

class Example_Model_Comment extends \RedBean_PipelineModel
{
	protected function makePath( $bean )
	{
		return $this->makeType($bean). '/' . $bean->id;
	}

	protected function makeType( $bean )
	{
		return 'hotbox/' . $this->getPrefix() . $bean->getMeta('type');
	}

	protected function getPrefix()
	{
		if ( Hb::$r->writer->getPrefix() != 'hbx_' && !empty(Hb::$stream->id) ) {
			return 'stream/' . Hb::$stream->id . '/';
		} else {
			return '';
		}
	}
}
