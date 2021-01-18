<?php

namespace ABetter\Image;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider {

    public function boot() {

		$this->loadRoutesFrom(__DIR__.'/../routes/web.php');

		$this->loadViewsFrom(__DIR__.'/../views', 'abetter-image');

		$this->loadViewComponentsAs('', [
			ImageComponent::class,
	    ]);

    }

    public function register() {
		//
    }

}
