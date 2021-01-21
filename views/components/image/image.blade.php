@php

use ABetter\Image\Image;

$attr = &$attributes;

$Ximage = (object) Image::get(
	$attr['src'],
	$attr['size'],
	'object'
);

$Ximage->defaults = [
	'background-color' => $Ximage->color ?? '',
	'--color' => $Ximage->color ?? '',
	'--height' => $Ximage->dimensions['height_percent'] ?? '',
	'--x' => '50%',
	'--y' => '50%',
	'--lazy' => '0.5s',
	'--overlay' => '#220',
	'--shade' => '0.2',
	'--fade' => '0.3',
	'--vignette' => '1',
	'--x-0' => 'w1000',
	'--x-1000' => 'w1400',
	'--x-1400' => 'w2000',
];

$Ximage->id = (string) $attr['id'] ?? "";
$Ximage->class = (string) $attr['class'] ?? "";
$Ximage->style = (string) $attr['style'] ?? "";
$Ximage->props = trim($Ximage->class." ".$Ximage->style." ".($attr['responsive']??""));

$Ximage->cover = (isset($attr['cover'])) ? '--cover': '';
$Ximage->responsive = (isset($attr['unresponsive'])) ? '' : '--responsive';
$Ximage->prezise = (isset($attr['unpresize'])) ? '': '--presize';
$Ximage->lazy = (isset($attr['unlazy'])) ? '' : '--lazy';

$Ximage->ready = ($Ximage->lazy) ? '' : '--ready';

$Ximage->xattr = "";
$Ximage->xset = "";
$Ximage->xstyle= "";
$Ximage->xclass = trim("{$Ximage->responsive} {$Ximage->cover} {$Ximage->prezise} {$Ximage->lazy} {$Ximage->ready}");

// ---

$Ximage->propvars = [];
foreach (preg_split('/\s+/',$Ximage->props) AS $prop) {
	$prop = explode(':',trim($prop)); if (empty($prop[0])) continue;
	$Ximage->propvars[$prop[0]] = ($prop[1] ?? $Ximage->defaults[$prop[0]] ?? "");
};

$Ximage->vars = array_merge($Ximage->defaults,$Ximage->propvars);

uksort($Ximage->vars, function($a,$b){
	return (int) preg_replace('/[^\d]+/','',$a) - (int) preg_replace('/[^\d]+/','',$b);
});

foreach ($Ximage->vars AS $key => $val) {
	$Ximage->xstyle .= ($val = trim($val,';')) ? "{$key}:{$val}; " : "";
	if (strpos($Ximage->props,$key) === FALSE) continue;
	if (!in_array($key,['--fade','--shade','--vignette'])) continue;
	$Ximage->xclass .= " {$key}";
}

// ---

if ($Ximage->responsive && !empty($Ximage->service)) {
	foreach ($Ximage->vars AS $prop => $x) {
		if (!preg_match('/^--x-\d+/',$prop)) continue;
		$src = str_replace('/x','/'.$x,$Ximage->service);
		$Ximage->xset .= "{$prop}:{$src}; ";
	}
}

$Ximage->xattr = ($Ximage->xset && $Ximage->lazy) ? 'set="'.$Ximage->xset.'"' : 'src="'.$Ximage->src.'"';
$Ximage->xattr = ($Ximage->lazy) ? "x-{$Ximage->xattr}" : $Ximage->xattr;

if ($Ximage->cover && !$Ximage->lazy) {
	$Ximage->xattr = 'style="background-image:url('.$Ximage->src.');"';
}

//dump($Ximage);

@endphp

<div class="component--x-image {{ $Ximage->xclass }}" style="{!! $Ximage->xstyle !!}" @if($Ximage->id) id="{{ $Ximage->id }}" @endif>
	@if($Ximage->cover)
		<div x-image x-cover {!! $Ximage->xattr !!}></div>
	@else
		<img x-image {!! $Ximage->xattr !!} />
	@endif
	<div x-overlay><div x-shade></div></div>

<x-script>
(function(){

	var self = this, $w = window, $d = document;

	self.items = [];

	self.xImgs = function() {
	    if (!self.items.length) {
			var q = $d.querySelectorAll('.component--x-image > [x-image]'); // IE breaks with '--';
			[].forEach.call(q,function(el,i){
				self.items[i] = {};
				self.items[i].el = el;
				self.items[i].opt = self.getOpt(el);
				self.items[i].src = el.getAttribute('src');
			});
		}
		[].forEach.call(self.items,function(item,i){
			self.xImg(item,i);
		});
	};

	self.xImg = function(item,i) {

		var item = (item) ? item : self.items[i],
			el = item.el,
			rect = el.getBoundingClientRect();

		el.style.setProperty('--w', Math.round(rect.width) + 'px');
		el.style.setProperty('--h', Math.round(rect.height) + 'px');

		if (rect.top > $w.innerHeight) return;

		// ---

		var opt = item.opt, res;

		if (el.hasAttribute('x-set') && opt.size) {
			res = opt.size[0].src;
			for (var s in opt.size) {
				if (opt.size[s].w <= rect.width) {
					res = opt.size[s].src;
				}
			}
			if (res != self.items[i].src) {
				el.setAttribute('x-src',res);
			};
		};

		if (!el.hasAttribute('x-src')) return;

		// ---

		var src = el.getAttribute('x-src');

		if (src != self.items[i].src) {
			if (el.tagName == 'IMG') {
				el.setAttribute('src', src);
				el.onload = function(e){
					el.parentNode.classList.add('--ready');
					el.removeAttribute('x-src');
				};
				self.items[i].src = src;
			} else {
				var img = new Image();
				img.src = src;
				img.onload = function(e){
					el.setAttribute('style', 'background-image:url('+src+');');
					el.parentNode.classList.add('--ready');
					el.removeAttribute('x-src');
				};
				self.items[i].src = src;
			};
		};

	};

	self.getOpt = function(el) {
		var opt = {};
		if (el.hasAttribute('x-set')) {
			var set = [],
				xset = el.getAttribute('x-set').replace(/\s/g,'').split(';'),
				style = getComputedStyle(el.parentNode);
			for (var i in xset) {
				var x = xset[i].split(':'),
					w = (x[0]||'').replace(/--x-(\d+)/,'$1'),
					s = (x[1]||'').trim();
				if (s) set.push({ w: w, src: s });
			}
			opt.size = set;
		}
		return opt;
	};

	$w.addEventListener('scroll', self.xImgs);
	$w.addEventListener('load', self.xImgs);
	$w.addEventListener('resize', self.xImgs);

})();
</x-script>

<x-style>
.component--x-image {
	display: block;
	display: flex;
	align-items: center;
	justify-content: center;
	position: relative;
	width: 100%;
	overflow: hidden;
	// ---
	[x-image] {
		opacity: 0;
		display: block;
		position: relative;
		margin: 0;
		padding: 0;
		left: 0;
		top: 0;
		width: 100%;
		background-size: cover;
		background-repeat: no-repeat;
		background-position: 50% 50%; //IE11
		background-position-x: var(--x);
		background-position-y: var(--y);
	}
	&.\--presize {
		[x-image] {
			position: absolute;
			height: 100%;
			@media all and (-ms-high-contrast:none) {
				position: relative; //IE11
			}
		}
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
	}
	&.\--cover {
		[x-image] {
			position: absolute;
			height: 100%;
		}
		&:after {
			content: none;
			padding-bottom: 0;
		}
	}
	// ---
	[x-overlay] {
		opacity: 0;
		display: block;
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		&:before,&:after {
			content: '';
			position: absolute;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			opacity: 0;
		}
		[x-shade] {
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
	&.\--shade [x-shade] {
		opacity: 0; //IE11
		opacity: var(--shade);
		background: var(--overlay);
	}
	&.\--vignette [x-overlay]:before {
		opacity: 0; //IE11
		opacity: var(--vignette);
    	background: radial-gradient(ellipse at center, transparent 0%, transparent 50%, var(--overlay) 125%);

	}
	&.\--fade [x-overlay]:after {
		opacity: 0; //IE11
		opacity: var(--fade);
		height: 120%;
		background: linear-gradient(var(--overlay), transparent 100px, transparent 20%, transparent 60%, var(--overlay));
	}
	&.\--multiply {
		[x-overlay],
		[x-overlay]:before,
		[x-overlay]:after,
		[x-shade] {
			mix-blend-mode: multiply;
		}
	}
	// ---
	&.\--ready {
		[x-image],
		[x-overlay] {
			opacity: 1;
			transition: opacity 0.5s linear; //IE11
			transition: opacity var(--lazy) linear;
		}
	}
}
</x-style>

</div>
