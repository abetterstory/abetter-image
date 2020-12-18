<?php

use Illuminate\Support\Facades\Route;

Route::get('/_image/{style?}/{path}', 'ABetter\Image\ImageController@handle')->where('path','.*');
