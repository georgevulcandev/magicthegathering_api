<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Services\CardsProvider;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;

class RetrieveCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:retrieve-cards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command retrieves all cards from https://api.magicthegathering.io';

    /**
     * Execute the console command.
     */
    public function handle(Client $httpClient): void
    {
        $apiUrl = env('MAGICTHEGATHERING_API_URL');
        $page = 815;
        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];

        $progressBar = $this->output->createProgressBar(7);
        $progressBar->start();
        do {
            try {
                $response = $httpClient->get(
                    $apiUrl.'/v1/cards?page='.$page,
                    ['headers' => $headers]
                );
            } catch (\Exception $e) {
                $this->line('Error retrieving data from page' . $page);
            }

            $page++;

            $body = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            foreach ($body['cards'] as $cardData) {
                $card = Card::create([
                    'id'   => $cardData['id'],
                    'name' => $cardData['name'],
                    'cmc'  => $cardData['cmc'],
                    'type' => $cardData['types'][0],
                ]);
            }
            $progressBar->advance();

        } while ($this->nextPageExists(Psr7\Header::parse($response->getHeader('Link'))));

        $progressBar->finish();
    }

    private function nextPageExists(array $linkHeaders): bool
    {

        foreach ($linkHeaders as $link) {
            if (isset($link['rel']) && $link['rel'] === 'next') {
                return true;
            }
        }

        return false;
    }
}
