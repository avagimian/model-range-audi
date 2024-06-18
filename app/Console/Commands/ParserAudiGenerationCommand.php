<?php

namespace App\Console\Commands;

use App\Models\Generation;
use App\Models\Model;
use Goutte\Client;
use Illuminate\Console\Command;

class ParserAudiGenerationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parser-audi-generations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $client = new Client();
        $models = Model::query()->get();
        $data = [];

        foreach ($models as $model) {
            $crawler = $client->request('GET', 'https://www.drom.ru/catalog/audi/' . $model->name);

            $pictureUrls = $crawler->filter('.css-1xvlg42.e1e9ee560 img')->each(function ($node) {
                return $node->attr('src');
            });

            $technicalUrls = $crawler->filter('a.css-14o2pic.ezhoka60.e1ei9t6a1')->each(function ($node) {
                return $node->link()->getUri();
            });

            $generations = $crawler->filter('div[data-ftid="component_article_extended-info"] > div:nth-child(1)')->each(function ($node) {
                return explode(' ', $node->text())[0];
            });

            $periods = $crawler->filter('.css-1i4af64 span.css-1089mxj')->each(function ($node) {
                $text = $node->text();
                preg_match('/^(.*?)\s+(\d{2}\.\d{4})\s*-\s*(\d{2}\.\d{4}|н\.в\.)/', $text, $matches);
                return array_slice($matches, 1, 3);
            });

            $result = [];

            foreach ($technicalUrls as $index => $technicalUrl) {
                $markets = $client->request('GET', $technicalUrl);

                $market = $markets->filter('body > div.b-wrapper > div.b-content.b-media-cont.b-media-cont_margin_huge > div.b-left-side > div > div:nth-child(4) > div:nth-child(1)')->each(function ($node) {
                    $text = explode(' ', $node->text());
                    return $text[2];
                });

                $market = rtrim($market[0], '.');

                $result[] = [
                    'name' => $periods[$index][0],
                    'market' => $market,
                    'start_period' => $periods[$index][1],
                    'end_period' => $periods[$index][2],
                    'generation' => $generations[$index],
                    'picture_url' => $pictureUrls[$index],
                    'technical_url' => $technicalUrl,
                ];
            }

            $data = array_merge($data, $result);
        }

        Generation::query()->insert($data);
    }
}
