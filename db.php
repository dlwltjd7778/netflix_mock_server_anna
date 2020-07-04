<?php
require './pdos/IndexPdo.php';
require './pdos/DatabasePdo.php';

for($i=1;$i<=1;$i++){

    $title = urlencode(getTitle($i));

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://imdb8.p.rapidapi.com/title/find?q=".$title."",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "x-rapidapi-host: imdb8.p.rapidapi.com",
            "x-rapidapi-key: 17b52a9242msh9cb76529088f20dp18a1b6jsn0f08a88867fb"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        echo $response;
    }
}
