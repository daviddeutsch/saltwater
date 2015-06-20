<?php

namespace Saltwater\Blog;

use Saltwater\Salt\Module;

/**
 * In a modules $require, you set up the modules that need to be loaded
 * in saltwater in order to have this module function properly.
 *
 * For this module to pass unit tests, we include the Test\Test Module,
 * usually, you would include Saltwater\App\App and Saltwater\RedBean\RedBean
 *
 * @require Saltwater\Test\Test
 *
 * Defining blank services tells the router that they can be accessed through
 * /article[/:id] and /comment[/:id]
 *
 * Defining something as an entity is a signal for the RedBean module to
 * track its lifecycle in an update stream
 *
 * @provide {'service': ['article', 'comment'], 'entity': 'comment'}
 */
class Blog extends Module {}
