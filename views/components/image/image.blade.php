@php

//echo "image!";
//echo json_encode($attributes??"");
//echo json_encode($data??"");

//dump($data??"");
//dump($data['attributes']??"");
//dump($data['slot']??"");

@endphp
<p>Image</p>
SLOT: {{ $slot ?? "" }}
ATTRIBUTES {{ $attributes ?? "" }}
