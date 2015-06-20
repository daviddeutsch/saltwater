<?php

namespace Saltwater\Test;

use Saltwater\Salt\Module;

/**
 * Class Test
 *
 * In your module require, you set up the modules that need to be loaded
 * in saltwater in order to have this module function properly.
 *
 * We load the RedBean Module first (for a simple REST layer and
 * other database stuff). The App Module gives us a router and a way to output
 * a response.
 *
 * @require ["Saltwater\RedBean\RedBean", "Saltwater\App\App"]
 *
 * This module also provides a number of things (Providers Config, Response,
 * Route) that are automatically associated since they live in the same
 * directory structure.
 *
 * The RedBean DbProvider requires a ConfigProvider to tell its the
 * database details, so we make a dummy one for this test.
 *
 * Furthermore, we don't want an actual HTTP output, so we fake our
 * router and the response a bit to make testing easier.
 */
class Test extends Module {}
