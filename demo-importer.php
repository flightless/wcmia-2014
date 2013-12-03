<?php
/*
Plugin Name: WordCamp Miami 2014 Demo - User importer
Plugin URI: https://github.com/flightless/wcmia-2014
Description: Import users from a CSV
Author: Flightless
Author URI: http://flightless.us/
Version: 0.1
*/
/*
Copyright (c) 2014 Flightless, Inc. http://flightless.us/

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace WCMIA_2014;

function userimporter_initialize() {
	spl_autoload_register(__NAMESPACE__.'\userimporter_autoload');
	UserImporter::instance();
}

function userimporter_autoload( $class ) {
	if (substr($class, 0, strlen(__NAMESPACE__)) != __NAMESPACE__) {
		//Only autoload libraries from this package
		return;
	}
	$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
	$path = __DIR__ . DIRECTORY_SEPARATOR . $path . '.php';
	if (file_exists($path)) {
		require $path;
	}
}

add_action( 'plugins_loaded', __NAMESPACE__.'\userimporter_initialize', 10, 0 );
