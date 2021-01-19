@php

use ABetter\Image\Image;

$Ximage = (object) Image::get(
	$attributes['src'],
	$attributes['style'],
	'object'
);

$Ximage->defaults = [
	'background-color' => $Ximage->color ?? '',
	'--color' => $Ximage->color ?? '',
	'--height' => $Ximage->dimensions['height_percent'] ?? '',
	'--fadein' => '0.75s',
	'--overlay' => '#220',
	'--shade' => '0.2',
	'--fade' => '0.4',
	'--vignette' => '1',
];

$Ximage->style = "";
$Ximage->class = "";
$Ximage->lazy = (empty($attributes['lazy'])) ? 'lazy' : '';
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

<div class="component--x-image --x-container {{ $Ximage->class }}" style="{{ $Ximage->style }}">
	<img class="--x-image" {{ ($Ximage->lazy) ? 'data-' : '' }}src="{{ $Ximage->src }}" {{ $Ximage->lazy }} />
	<div class="--x-overlay"><div class="--x-shade"></div></div>

<x-script>
var $w = window, $d = document;
$w.ximglh = function(e) {
    var $e = $d.querySelectorAll('[data-src][lazy]'); // IE breaks with '--';
    for (var i = 0; i < $e.length; i++) {
        var rect = $e[i].getBoundingClientRect();
        if ($e[i].hasAttribute('data-src') && rect.top < $w.innerHeight) {
            $e[i].setAttribute('src', $e[i].getAttribute('data-src')+'?'+new Date().getTime());
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
	overflow: hidden;
	&:after {
    	content: '';
		display: block;
		position: relative;
		padding-bottom: 56%; //IE
    	padding-bottom: var(--height);
	}
	.\--x-image {
		display: block;
		position: absolute;
		margin: 0;
		padding: 0;
    	left: 0;
    	top: 0;
    	width: 100%;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		opacity: 0;
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
		opacity: 0; //IE
		opacity: var(--shade);
		background: var(--overlay);
	}
	&.\--vignette .\--x-overlay:before {
		opacity: 0; //IE
		opacity: var(--vignette);
		background: radial-gradient(circle, transparent 50%, var(--overlay) 150%);
	}
	&.\--fade .\--x-overlay:after {
		opacity: 0; //IE
		opacity: var(--fade);
		background: linear-gradient(var(--overlay), transparent 80px, transparent 20%, transparent 80%, var(--overlay));
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
			transition: opacity 0.75s linear;
			transition: opacity var(--fadein) linear;
		}
	}
}
</x-style>

</div>
