<?php

namespace App\Console\Commands;

use App\AudiParserService;
use App\Models\Model;
use Illuminate\Console\Command;

class ParserAudiModelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parser-audi-models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parser audi models';

    /**
     * Execute the console command.
     */
    public function handle(AudiParserService $audiParserService): void
    {
        $data = [];
        $pattern = '/<a class="g6gv8w4 g6gv8w8 _501ok20" href="([^"]+)"[^>]*data-ga-stats-va-payload="\{[^"]+&quot;model_name&quot;: &quot;([^"]+)&quot;[^"]*\}[^>]*>([^<]*)<svg[^>]*>/';
        $matches = $audiParserService->run($pattern);

        foreach ($matches as $match) {
            $data[] = [
                'name' => $match[2],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Model::query()->insert($data);
    }
}
