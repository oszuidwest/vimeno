<?php
ini_set("memory_limit", "-1");
use GuzzleHttp\Psr7\Request;

require 'vendor/autoload.php';


$client = new GuzzleHttp\Client([
    'headers' => [
        'Authorization' => 'bearer xxx'
    ]
]);

$request = new Request('GET', 'https://api.vimeo.com/me/projects?per_page=100');
$res = $client->send($request);
$json = json_decode($res->getBody()->getContents());
$folders = $json->data;
file_put_contents('folders.json', json_encode($folders, JSON_PRETTY_PRINT));

foreach ($folders as $project) {
    $videos = [];

    printf("\n%s\n", $project->name);

    $url = $project->uri.'/videos?per_page=100';
    do {
        $str = 'https://api.vimeo.com'.$url;

        printf("\033[0;37m  - Fetching %s...", $str);
        $request = new Request('GET', $str);
        $res = $client->send($request);
        printf(" Done!\033[0m\n");
        $json = json_decode($res->getBody()->getContents());

        file_put_contents('data.json', json_encode($json, JSON_PRETTY_PRINT));

        $videos = array_merge($videos, $json->data);

        $url = $json->paging->next;
    } while ($url);

    $path = '.'.$project->uri;
    if (!file_exists($path)) {
        mkdir($path, recursive: true);
    }

    printf("  %d videos\n", count($videos));
    file_put_contents($path.'/videos.json', json_encode($videos, JSON_PRETTY_PRINT));
    file_put_contents($path.'/project.json', json_encode($project, JSON_PRETTY_PRINT));
}
