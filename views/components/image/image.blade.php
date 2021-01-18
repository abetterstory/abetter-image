@php

use ABetter\Image\Image;

$Ximage = (object) Image::get(
	$attributes['src'],
	$attributes['style'],
	'object'
);

$Ximage->lazy = (empty($attributes['lazy'])) ? '--lazy' : '';
$Ximage->class = $attributes['class'] ?? "";

$Ximage->vars_default = [
	'--color' => $Ximage->color,
	'--height' => $Ximage->dimensions['height_percent'],
	'--fadein' => '1.0s',
	'--overlay' => '#220',
	'--shade' => '0.0',
	'--vignette' => '0.0',
	'--fade' => '0.0',
];

$Ximage->vars = "";
foreach ($Ximage->vars_default AS $key => $val) {
	$Ximage->vars .= "{$key}:{$val};";
}

@endphp

<div class="component--x-image --container {{ $Ximage->class }}" style="{{ $Ximage->vars }}">
	<div class="--shade"></div>
	<div class="--overlay"></div>
	<img class="--image" {{ ($Ximage->lazy) ? 'data-' : '' }}src="{{ $Ximage->src }}" {{ $Ximage->lazy }} />

<x-script>
var $w = window, $d = document;
$w.ximglh = function(e) {
    var $e = $d.querySelectorAll('[data-src][--lazy]');
    for (var i = 0; i < $e.length; i++) {
        var boundingClientRect = $e[i].getBoundingClientRect();
        if ($e[i].hasAttribute('data-src') && boundingClientRect.top < $w.innerHeight) {
            $e[i].setAttribute('src', $e[i].getAttribute('data-src'));
			$e[i].removeAttribute('data-src');
        };
		$e[i].onload = function(e){
			e.target.parentNode.classList.add('--ready');
		};
    };
};
$w.addEventListener('scroll', $w.ximglh);
$w.addEventListener('load', $w.ximglh);
$w.addEventListener('resize', $w.ximglh);
</x-script>

<x-style>
.component--x-image {
	display: block;
	position: relative;
	width: 100%;
	background-color: var(--color);
	overflow: hidden;
	&:after {
    	content: '';
		display: block;
		position: relative;
    	padding-bottom: var(--height);
	}
	.\--image {
		display: block;
		position: absolute;
		z-index: 0;
		margin: 0;
		padding: 0;
    	left: 0;
    	top: 0;
    	width: 100%;
    	height: 100%;
		object-fit: cover;
		opacity: 0;
	}
	.\--shade,
	.\--overlay {
		display: block;
		position: absolute;
		z-index: 2;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		opacity: 0;
		mix-blend-mode: multiply;
		&:before,&:after {
			content: '';
			position: absolute;
			z-index: 2;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			mix-blend-mode: multiply;
		}
	}
	&.\--shade .\--shade:before {
		opacity: var(--shade);
		background-color: var(--overlay);
	}
	&.\--vignette .\--overlay:before {
		opacity: var(--vignette);
		background: radial-gradient(circle, transparent 50%, var(--overlay) 150%);
	}
	&.\--fade .\--overlay:after {
		opacity: var(--fade);
		background: linear-gradient(var(--overlay), transparent 80px, transparent 20%, transparent 80%, var(--overlay));
	}
	&.\--ready {
		.\--image,.\--shade,.\--overlay {
			opacity: 1;
			transition: opacity var(--fadein) linear;
		}
	}
}
</x-style>

</div>
