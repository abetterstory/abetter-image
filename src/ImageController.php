<?php

namespace ABetter\Image;

use ABetter\Image\Image;
use Illuminate\Routing\Controller as BaseController;

class ImageController extends BaseController {

	public $image;

	public function handle($style,$path) {

		$this->image = [
			'expire' => '1 year',
			'path' => '/'.trim($path,'/'),
			'style' => $style,
			'file' => NULL,
			'src' => NULL,
		];

		$this->image['file'] = Image::get(
			$this->image['path'],
			$this->image['style'],
			'file'
		);

		if (!is_file($this->image['file'])) {
			return abort(404);
		}

		$GLOBALS['HEADERS']['expire'] = $this->image['expire'];
		$GLOBALS['HEADERS']['modified'] = @filemtime($this->image['location']);

		return response()->file($this->image['file']);

	}

}
