<?php

namespace ABetter\Image;

use Illuminate\View\Component;

class BackgroundComponent extends Component {

	public $view = 'abetter-image::components.background.background';

	// ---

    public function render() {
		return function(array $data) {
			return view($this->view)->with([
				'data' => $data,
				'slot' => $data['slot'],
				'attributes' => $data['attributes'],
			])->render();
    	};
    }

}
