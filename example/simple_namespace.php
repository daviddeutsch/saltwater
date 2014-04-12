<?php

// -- /src/Example/Context/Example.php

namespace ExampleModule;

use Saltwater\Context as Context;

// Creating our root context
class Example extends Context
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

/*
 * The following five lines would be your index.php with the usual rewrite
 *
 * RewriteEngine On
 * RewriteCond %{REQUEST_FILENAME} !-f
 * RewriteRule ^ index.php [QSA,L]
 */

// Including daviddeutsch/redbean-adaptive and ../saltwater via composer
require 'path/autoload.php';

use Saltwater\Server as S;

S::init( new Example() );

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

// -- /src/Example/Service/Comment.php

namespace Example\Service;

use Saltwater\Server as S;
use Saltwater\Root\Service\Rest as Rest;

class Comment extends Rest
{
	/*
	 * (docblocks only for showing the URLs, there's no magic comment parsing)
	 *
	 * GET /comment
	 * GET /article/:id/comment
	 * GET /comment/:id/comment <- sure, why not?
	 */
	public function getComment( $call, $data=null )
	{
		if ( !empty($this->context->data) ) {
			return S::$r->related($this->context->data, 'comment');
		} else {
			return $this->restCall($call, $data);
		}
	}

	/*
	 * POST /comment
	 * POST /article/:id/comment
	 * POST /comment/:id/comment
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

// -- /src/Example/Context/Example.php

namespace Saltwater\Context;

class Example extends Context
{
	public $service = array(
		'article', 'video', 'thread', 'comment' //...
	);

	//...
}

/*
 * POST /article/:id/comment
 * POST /video/:id/comment
 * POST /thread/:id/comment
 * etc.
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

// -- /src/Example/Model/Article.php, Comment.php,
//                       ArticleComment.php, CommentComment.php

namespace Example\Model;

use Saltwater\Model\Model as Model;
use Saltwater\Model\AssociationModel as AssociationModel;

class Article extends Model {}
class Comment extends Model {}

/*
 * We also want models for the associations:
 */

class ArticleComment extends AssociationModel {}
class CommentComment extends AssociationModel {}

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
