<?php

namespace ABetter\Image;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class BladeServiceProvider extends ServiceProvider {

    public function boot() {

		// Image
        Blade::directive('image', function($expression){
			return "<?php echo _image($expression); ?>";
        });

		// Component
		Blade::component('image', ImageComponent::class);
		Blade::component('background', BackgroundComponent::class);

    }

    public function register() {
        //
    }

}
