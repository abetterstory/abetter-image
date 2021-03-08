<?php

use Illuminate\Support\Facades\Route;

Route::get('/_image/{style?}/{path}', 'ABetter\Image\ImageController@handle')->where('path','.*');

Route::get('/_cache/{path}', 'ABetter\Image\CacheController@handle')->where('path','.*');
