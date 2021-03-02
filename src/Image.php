<?php

namespace ABetter\Image;

use Imagick;
use Tinify;
use ABetter\Mockup\Pixsum;
//use Illuminate\Database\Eloquent\Model AS BaseModel;

class Image {

	public static $Imagick;

	public static $default = [
		'return' => 'src',
		'storage' => 'cache/image',
		'service' => NULL,
		'src' => NULL,
		'file' => NULL,
		'pixsum' => NULL,
		'request' => NULL,
		'protocol' => NULL,
		'domain' => NULL,
		'path' => NULL,
		'filename' => NULL,
		'type' => NULL,
		'remote' => NULL,
		'style' => NULL,
		'source' => NULL,
		'target' => NULL,
		'process' => NULL,
		'styles' => NULL,
		'color' => NULL,
		'dimensions' => NULL,
		'vars' => NULL,
		'ref' => NULL,
	];

	// ---

	public static function get() {
		$opt = self::options(func_get_args());
		$opt['source'] = self::source($opt);
		$opt['target'] = self::target($opt);
		if (is_file($opt['source']) && !is_file($opt['target'])) {
			if (empty($opt['style']) || $opt['style'] == 'x') {
				$opt['target'] = $opt['source'];
			} else {
				$opt['styles'] = self::styles($opt['style']);
				$opt['process'] = self::imagick(
					$opt['source'],
					$opt['target'],
					$opt['type'],
					$opt['styles']
				);
			}
		}
		if ($opt['return'] == 'file') return $opt['target'];
		if ($opt['return'] == 'src') return self::public($opt);
		if ($opt['return'] == 'service') return self::service($opt);
		$opt['service'] = self::service($opt);
		$opt['src'] = self::public($opt);
		$opt['file'] = $opt['target'];
		$opt['color'] = self::color($opt);
		$opt['dimensions'] = self::dimensions($opt);
		return $opt;
	}

	public static function color($opt) {
		if (preg_match('/color\-([^.]{3,6})/',$opt['filename'],$match)) {
			return "#".$match[1];
		}
		return self::imagickColor($opt['source']);
	}

	public static function dimensions($opt) {
		if (!is_file($opt['target'])) return [];
		$get = @getimagesize($opt['target']);
		$dim = ['width' => ($get[0]??0), 'height' => ($get[1]??0)];
		$dim['width_ratio'] = round($dim['width'] / $dim['height'],3);
		$dim['width_percent'] = round(100 * $dim['width_ratio']).'%';
		$dim['height_ratio'] = round($dim['height'] / $dim['width'],3);
		$dim['height_percent'] = round(100 * $dim['height_ratio']).'%';
		return $dim;
	}

	// ---

	public static function source($opt=[]) {
		$source = (preg_match('/^\/cache\//',$opt['path'])) ? storage_path() : public_path();
		$source .= $opt['path'].$opt['filename'].'.'.$opt['type'];
		if ($opt['remote'] && !is_file($source)) {
			$source = $opt['storage'].'/'.$opt['cachekey'].'.'.$opt['type'];
			if ($content = @file_get_contents($opt['remote'])) {
				@file_put_contents($source,$content);
			}
		}
		return $source;
	}

	public static function target($opt=[]) {
		return $opt['storage'].'/'.$opt['cachekey'].'.'.$opt['style'].'.'.$opt['type'];
	}

	public static function public($opt=[]) {
		return str_replace(storage_path(),'',$opt['target']);
	}

	public static function service($opt=[]) {
		return '/_image/x'.$opt['path'].$opt['filename'].'.'.$opt['type'];
	}

	// ---

	public static function cachekey($opt=[]) {
		$cachekey = trim(str_replace(['/'],['.'],$opt['domain'].$opt['path'].$opt['filename']),'.');
		return preg_replace('/\.(jpeg|jpg|png|gif)$/','',$cachekey);
		// return preg_replace('/\.[^.\s]{3,4}$/','',$cachekey);
	}

	// ---

	public static function filetype($url,$reverse=FALSE) {
		return self::format(($headers = @get_headers($url,1)) ? $headers['Content-Type']??"" : "image/jpeg",TRUE);
	}

	public static function format($ext,$reverse=FALSE) {
		$formats = [
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'png' => 'image/png',
			'gif' => 'image/gif',
		];
		$formats = ($reverse) ? array_flip($formats) : $formats;
		return $formats[$ext] ?? reset($formats);
	}

	// ---

	public static function options($args=[]) {
		$opt = self::$default;
		foreach ($args AS $arg) if (is_array($arg)) $opt = array_merge($opt,$arg);
		if (is_string($args[0]??NULL)) {
			$opt['request'] = $args[0];
		}
		if (is_string($args[1]??NULL)) {
			$opt['style'] = $args[1];
		}
		if (is_string($args[2]??NULL)) {
			$opt['return'] = $args[2];
		}
		if (preg_match('/^(pixsum|pexels|unsplash)/',$args[0]??"")) {
			$opt['pixsum'] = str_replace(['pixsum'],[''],$args[0]);
			$opt['request'] = Pixsum::get($opt['pixsum']);
		}
		$opt['path'] = rtrim(pathinfo($opt['request'],PATHINFO_DIRNAME),'/').'/';
		$opt['filename'] = pathinfo($opt['request'],PATHINFO_FILENAME);
		$opt['type'] = pathinfo($opt['request'],PATHINFO_EXTENSION);
		if (preg_match('/^https?\:\/\//',$opt['request'])) {
			$opt['domain'] = parse_url($opt['request'],PHP_URL_HOST);
			$opt['protocol'] = parse_url($opt['request'],PHP_URL_SCHEME).'://';
			$opt['path'] = rtrim(parse_url($opt['path'],PHP_URL_PATH),'/').'/';
			$opt['remote'] = ($opt['domain'] !== parse_url(env('APP_URL'),PHP_URL_HOST)) ? $opt['request'] : FALSE;
			if (!$opt['type']) $opt['type'] = self::format($opt['remote'],TRUE);
			if (!$opt['remote']) $opt['domain'] = "";
		}
		$opt['cachekey'] = self::cachekey($opt);
		$opt['storage'] = storage_path($opt['storage']);
		if (!is_dir($opt['storage'])) \File::makeDirectory($opt['storage'],0777,TRUE);
		return $opt;
	}

	// ---

	public static function imagick($source,$target,$type,$style) {
		if (!is_file($source) || preg_match('/\.error/',$source)) return NULL;
		try {
			$imagick = self::$Imagick ?? new Imagick($source);
			self::imagickResize($imagick,$style);
			self::imagickFilter($imagick,$style);
			if ($type == 'png' || $type == 'gif') {
				$imagick->setImageCompression(Imagick::COMPRESSION_UNDEFINED);
				$imagick->setImageCompressionQuality(0);
			} else if ($type == 'jpg' || $type == 'jpeg') {
				$imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
				$imagick->setImageCompressionQuality(70);
				$imagick->setImageBackgroundColor($style['background']);
				$imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
				$imagick = $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
			}
			$imagick->stripImage();
			$imagick->writeImage($target);
			$imagick->clear();
			self::compress($target);
			return TRUE;
		} catch(Exception $e) {
			return FALSE;
		}
	}

	public static function imagickColor($source,$color="") {
		if (!is_file($source) || preg_match('/\.error/',$source)) return NULL;
		try {
			$imagick = self::$Imagick ?? new Imagick($source);
			$imagick->scaleimage(1,1);
			if ($pixel = ($pixels = $imagick->getimagehistogram()) ? reset($pixels) : NULL) {
				$rgb = $pixel->getcolor();
				$color = sprintf('#%02X%02X%02X',$rgb['r'],$rgb['g'],$rgb['b']);
			}
			$imagick->clear();
			return $color;
		} catch(Exception $e) {
			return FALSE;
		}
	}

	public static function imagickResize($imagick,$style) {
		$source_w = $imagick->getImageWidth();
		$source_h = $imagick->getImageHeight();
		$target_w = floor($style['w']);
		$target_h = floor($style['h']);
		// ---
		if (empty($target_w) && empty($target_h)) {
			$target_w = $source_w;
			$target_h = $source_h;
		}
		// ---
		if ($target_w > $target_h) {
			$resize_w = $target_w;
			$resize_h = floor($source_h * ($target_w / $source_w));
		} else {
			$resize_w = floor($source_w * ($target_h / $source_h));
			$resize_h = $target_h;
		}
		$imagick->resizeImage($resize_w, $resize_h, Imagick::FILTER_LANCZOS, 0.9);
		// ---
		if ($target_w == 0 || $target_h == 0) return $imagick;
		// ---
		$crop_w = $target_w;
		$crop_h = $target_h;
		$crop_x = 0;
		$crop_y = 0;
		$crop_align = $style['align'];
		// ---
		if ($resize_w > $crop_w) {
			$crop_x = floor(($resize_w - $crop_w) / 2);
			if (preg_match('/left/',$crop_align)) $crop_x = 0;
			if (preg_match('/right/',$crop_align)) $crop_x = floor($resize_w - $crop_w);
			$imagick->cropImage($crop_w, $target_h, $crop_x, 0);
		}
		if ($resize_h > $crop_h) {
			$crop_y = floor(($resize_h - $crop_h) / 2);
			if (preg_match('/top/',$crop_align)) $crop_y = 0;
			if (preg_match('/bottom/',$crop_align)) $crop_y = floor($resize_h - $crop_h);
			$imagick->cropImage($target_w, $crop_h, 0, $crop_y);
		}
		$imagick->setImagePage(0, 0, 0, 0); // Reset canvas
		// ---
		return $imagick;
	}

	public static function imagickFilter($imagick,$style) {
		if ($style['filter'] == 'grayscale') {
			$imagick->transformimagecolorspace(Imagick::COLORSPACE_GRAY);
		}
		if ($style['filter'] == 'blur') {
			$val = ($style['value']) ? (int) $style['value'] : 10;
			$imagick->blurImage($val,$val);
		}
		if ($style['filter'] == 'lighter') {
			$val = ($style['value']) ? (int) $style['value'] : 25;
			$imagick->brightnessContrastImage($val, 0);
		}
		if ($style['filter'] == 'darker') {
			$val = ($style['value']) ? (int) $style['value'] : 25;
			$imagick->brightnessContrastImage(0 - $val, 0);
		}
		return $imagick;
	}

	public static function styles($string,$style=[]) {
		$style['args'] = explode('-',$string);
		$style['dimension'] = (isset($style['args'][0])) ? $style['args'][0] : NULL;
		$style['align'] = (isset($style['args'][1])) ? $style['args'][1] : NULL;
		$style['filter'] = (isset($style['args'][2])) ? $style['args'][2] : NULL;
		$style['value'] = (isset($style['args'][3])) ? $style['args'][3] : NULL;
		$style['background'] = NULL; //'#000';
		// ---
		$style['type'] = '';
		$style['w'] = 0;
		$style['h'] = 0;
		if (preg_match('/x/',$style['dimension'])) {
			$style['type'] = 'x';
			list($style['w'],$style['h']) = explode('x',$style['dimension']);
			$style['w'] = (int) $style['w'];
			$style['h'] = (int) $style['h'];
		} elseif (preg_match('/^w/',$style['dimension'])) {
			$style['type'] = 'w';
			$style['w'] = (int) ltrim($style['dimension'],'w');
		} elseif (preg_match('/^h/',$style['dimension'])) {
			$style['type'] = 'h';
			$style['h'] = (int) ltrim($style['dimension'],'h');
		} elseif (is_numeric($style['dimension'])) {
			$style['type'] = 's';
			$style['w'] = (int) $style['dimension'];
			$style['h'] = (int) $style['dimension'];
		}
		// ---
		$aligns = array(
			'c' => 'center', 'm' => 'center', 'middle' => 'center', 'center' => 'center',
			't' => 'top', 'top' => 'top',
			'b' => 'bottom', 'bottom' => 'bottom',
			'l' => 'left', 'left' => 'left',
			'r' => 'right', 'right' => 'right',
			'lt' => 'lefttop', 'tl' => 'lefttop', 'topleft' => 'lefttop', 'lefttop' => 'lefttop',
			'lb' => 'leftbottom', 'bl' => 'leftbottom', 'bottomleft' => 'leftbottom', 'leftbottom' => 'leftbottom',
			'rt' => 'righttop', 'tr' => 'righttop', 'topright' => 'righttop', 'righttop' => 'righttop',
			'rb' => 'rightbottom', 'br' => 'rightbottom', 'bottomright' => 'rightbottom', 'rightbottom' => 'rightbottom'
		);
		$style['align'] = (isset($aligns[$style['align']])) ? $aligns[$style['align']] : 'center';
		if ($style['align'] == 'box' && $style['filter']) $style['background'] = $style['filter'];
		// ---
		$filters = array(
			'g' => 'grayscale', 'gray' => 'grayscale', 'grayscale' => 'grayscale',
			'b' => 'blur', 'blur' => 'blur',
			'l' => 'lighter', 'light' => 'lighter', 'lighter' => 'lighter',
			'd' => 'darker', 'dark' => 'darker', 'darker' => 'darker'
		);
		$style['filter'] = (isset($filters[$style['filter']])) ? $filters[$style['filter']] : NULL;
		// ---
		$style['background'] = $style['background'] ?? '#FFFFFF';
		// ---
		return $style;
	}

	// ---

	public static function compress($target) {
		if (!$key = env('TINIFY_KEY')) return NULL;
		Tinify\setKey($key);
		$source = Tinify\fromFile($target);
		$source->toFile($target);
		return TRUE;
	}

	// ---

}
