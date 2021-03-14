<?php

namespace ABetter\Image;

use ABetter\Image\Image;
use Illuminate\Routing\Controller as BaseController;

class CacheController extends BaseController {

	public $cache;

	public function handle($path) {

		$this->cache = [
			'expire' => '1 year',
			'path' => str_replace('/http','http',('/'.trim($path,'/'))),
			'style' => "",
			'file' => NULL,
			'src' => NULL,
		];

		$this->cache['file'] = Image::get(
			$this->cache['path'],
			$this->cache['style'],
			'file'
		);

		if (!is_file($this->cache['file'])) {
			return abort(404);
		}

		return response()->file($this->cache['file'],_cache_headers([
			'expire' => $this->cache['expire'],
			'modified' => @filemtime($this->cache['location']),
		]));

	}

}
