<?php

namespace ABetter\Image;

use Illuminate\Routing\Controller as BaseController;

class ImageController extends BaseController {

	protected $image;

	public function handle($style,$path) {

		$this->image = [
			'expire' => '1 year',
			'path' => '/'.trim($path,'/'),
			'style' => $style,
		];

		/*

		// Parse
		$this->image['ext'] = pathinfo($this->image['path'],PATHINFO_EXTENSION);
		$this->image['name'] = pathinfo($this->image['path'],PATHINFO_FILENAME);
		if (($this->image['conv'] = strtolower(pathinfo($this->image['name'],PATHINFO_EXTENSION))) && in_array($this->image['conv'],['jpg','jpeg','png'])) {
			$this->image['ext'] = $this->image['conv'];
			$this->image['name'] = pathinfo($this->image['name'],PATHINFO_FILENAME);
		}

		// Location
		$this->image['source'] = $this->imageFileSearch($this->image);
		$this->image['target'] = preg_replace('/\.([^\.]+)$/',".{$this->image['style']}.$1",storage_path('cache').$this->image['path']);

		// Process
		if (!is_file($this->image['target'])) {
			if (!is_file($this->image['source'])) {
				return abort(404);
			} else {
				$this->image['processed'] = $this->imageProcess($this->image);
			}
		}

		// Response
		$this->image['modified'] = @filemtime($this->image['target']);
		$this->image['expire'] = (!is_numeric($this->image['expire'])) ? strtotime($this->image['expire'],0) : $this->image['expire'];
		$this->image['expires'] = @gmdate('D, d M Y H:i:s \G\M\T', $this->image['modified'] + $this->image['expire']);
		$this->image['headers'] = [
			"cache-control" => "public, max-age={$this->image['expire']}",
			"expires" => "{$this->image['expires']}",
		];

		return response()->file($this->image['target'], $this->image['headers']);
		*/

    }

	// ---

	/*

	public function imageFileSearch($image,$found=NULL) {
		$dir = dirname(public_path().$image['path']);
		if (!is_dir($dir)) return FALSE;
		foreach (scandir($dir) AS $f) {
			if ($found || in_array($f,['.','..'])) continue;
			if ($f == "{$image['name']}.{$image['ext']}") $found = $f;
			if (pathinfo($f,PATHINFO_FILENAME) == $image['name']) $found = $f;
		}
		return ($found) ? realpath($dir.DIRECTORY_SEPARATOR.$found) : FALSE;
	}

	// ---

	public function imageProcess($image) {
		return \_imageMagick($image['source'],$image['target'],$image['style']);
	}

	*/

}
