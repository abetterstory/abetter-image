@php

use ABetter\Image\Image;

$Ximage = (object) Image::get(
	$attributes['src'],
	$attributes['size'],
	'object'
);

$Ximage->defaults = [
	'background-color' => $Ximage->color ?? '',
	'--color' => $Ximage->color ?? '',
	'--height' => $Ximage->dimensions['height_percent'] ?? '',
	'--x' => '50%',
	'--y' => '50%',
	'--fadein' => '0.75s',
	'--overlay' => '#220',
	'--shade' => '0.2',
	'--fade' => '0.3',
	'--vignette' => '1',
];

$Ximage->id = (string) $attributes['id'] ?? "";
$Ximage->style = (string) $attributes['style'] ?? "";
$Ximage->cover = (isset($attributes['cover'])) ? 'is-cover' : '';
$Ximage->lazy = (empty($attributes['lazy'])) ? 'is-lazy' : '';
$Ximage->class = "";
$Ximage->classes = (string) $attributes['class'] ?? "";
$Ximage->classvars = [];
foreach(explode(' ',$Ximage->classes) AS $prop) {
	$prop = explode(':',$prop); if (empty($prop[0])) continue;
	$Ximage->classvars[$prop[0]] = $prop[1] ?? $Ximage->defaults[$prop[0]] ?? "";
};
$Ximage->vars = array_merge($Ximage->defaults,$Ximage->classvars);
foreach ($Ximage->vars AS $key => $val) {
	$Ximage->style .= "{$key}:{$val};";
	$Ximage->class .= ((strpos($Ximage->classes,$key) !== false) ? " {$key}" : "");
}

@endphp

<div class="component--x-image {{ $Ximage->cover }} {{ $Ximage->class }}" style="{{ $Ximage->style }}" @if($Ximage->id) id="{{ $Ximage->id }}" @endif>
	@if($Ximage->cover)
		<div class="--x-image {{ $Ximage->lazy }}" {{ ($Ximage->lazy)?'data-':'' }}src="{{ $Ximage->src }}"></div>
	@else
		<img class="--x-image {{ $Ximage->lazy }}" {{ ($Ximage->lazy)?'data-':'' }}src="{{ $Ximage->src }}" />
	@endif
	<div class="--x-overlay"><div class="--x-shade"></div></div>

<x-script>
(function(){

	var $this = this,
		$w = window,
		$d = document;

	$this.ximglh = function() {

	    var $els = $d.querySelectorAll('.is-lazy[data-src]'); // IE breaks with '--';
		[].forEach.call($els,function($el){

	        var rect = $el.getBoundingClientRect();
			var src = $el.getAttribute('data-src'); //+'?'+new Date().getTime();
			var $img = new Image();

			$img.onload = function(e){
				if ($el.tagName == 'IMG') {
					$el.setAttribute('src', src);
				} else {
					$el.setAttribute('style', 'background-image:url('+src+');');
				}
				$el.parentNode.classList.add('--ready');
			};

	        if ($el.hasAttribute('data-src') && rect.top < $w.innerHeight) {
				$img.src = src;
				$el.removeAttribute('data-src');
	        };

	    });
	};

	$w.addEventListener('scroll', $this.ximglh);
	$w.addEventListener('load', $this.ximglh);
	$w.addEventListener('resize', $this.ximglh);

})();
</x-script>

<x-style>
.component--x-image {
	display: block;
	position: relative;
	width: 100%;
	overflow: hidden;
	&:after {
		content: '';
		display: block;
		position: relative;
		padding-bottom: var(--height);
		@media all and (-ms-high-contrast:none) {
			padding-bottom: 0; //IE11
			content: none; //IE11
		}
	}
	&.is-cover {
		&:after {
			content: none;
			padding-bottom: 0;
		}
	}
	.\--x-image {
		display: block;
		position: absolute;
		margin: 0;
		padding: 0;
    	left: 0;
    	top: 0;
    	width: 100%;
		height: 100%;
		opacity: 0;
		background-size: cover;
		background-repeat: no-repeat;
		background-position: var(--x) var(--y);
		@media all and (-ms-high-contrast:none) {
			position: relative; //IE11
			background-position: 50% 50%; //IE11
		}
	}
	// ---
	.\--x-overlay {
		display: block;
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		opacity: 0;
		&:before,&:after {
			content: '';
			position: absolute;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			opacity: 0;
		}
		.\--x-shade {
			display: block;
			position: absolute;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			opacity: 0;
		}
	}
	// ---
	&.\--shade .\--x-shade {
		opacity: 0; //IE11
		opacity: var(--shade);
		background: var(--overlay);
	}
	&.\--vignette .\--x-overlay:before {
		opacity: 0; //IE11
		opacity: var(--vignette);
		background: radial-gradient(circle, transparent 50%, var(--overlay) 150%);
	}
	&.\--fade .\--x-overlay:after {
		opacity: 0; //IE11
		opacity: var(--fade);
		height: 120%;
		background: linear-gradient(var(--overlay), transparent 100px, transparent 20%, transparent 60%, var(--overlay));
	}
	&.\--multiply {
		.\--x-overlay,
		.\--x-overlay:before,
		.\--x-overlay:after,
		.\--x-shade {
			mix-blend-mode: multiply;
		}
	}
	// ---
	&.\--ready {
		.\--x-image,
		.\--x-overlay {
			opacity: 1;
			transition: opacity 0.75s linear; //IE11
			transition: opacity var(--fadein) linear;
		}
	}
}
</x-style>

</div>
