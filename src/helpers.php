<?php

// ---

if (!function_exists('_image')) {

	function _image() {
		return \ABetter\Image\Image::get(...func_get_args());
	}

}
