<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class AudiParserService
{
    public function run(string $pattern, string $modelName = '')
    {
        $client = new Client();

        $request = new Request('GET', 'https://www.drom.ru/catalog/audi/' . $modelName);
        $res = $client->sendAsync($request)->wait();

        $html = $res->getBody()->getContents();
//        $pattern = '/<a class="g6gv8w4 g6gv8w8 _501ok20" href="([^"]+)"[^>]*data-ga-stats-va-payload="\{[^"]+&quot;model_name&quot;: &quot;([^"]+)&quot;[^"]*\}[^>]*>([^<]*)<svg[^>]*>/';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

        return $matches;
    }
}
