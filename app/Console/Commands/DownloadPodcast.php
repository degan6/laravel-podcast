<?php

namespace App\Console\Commands;

use App\Podcast;
use App\PodcastItem;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class DownloadPodcast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'podcast:download 
    {--auto=false : Download podcasts automatically}
     {--podcastId= : Download all episodes from Podcast}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download Selected podcast';

    private $guzzle;

    private $mimes;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->guzzle = new Client(
            ['allow_redirects' => true]
        );

        $this->mimes = new \Mimey\MimeTypes;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if($this->option('auto')) {
            $items = PodcastItem::where('downloaded', 0)->get();

            $bar = $this->output->createProgressBar(count($items));
            foreach ($items as $episode)
            {
                $fileName = $this->downloadEpisode($episode,'podcasts');
                if(!$fileName)
                {
                    continue;
                }

                $episode->file_name = $fileName;
                $episode->downloaded = 1;
                $episode->save();

                $bar->advance();
            }
            $bar->finish();

        } else {

            $p = Podcast::findOrFail($this->option('podcastId'));
            $toDownload = $p->items()->where('downloaded', 0)->get();

            $bar = $this->output->createProgressBar(count($toDownload));
            foreach ($toDownload as $episode)
            {
                $fileName = $this->downloadEpisode($episode,'podcasts');
                if(!$fileName)
                {
                    continue;
                }

                $episode->file_name = $fileName;
                $episode->downloaded = 1;
                $episode->save();

                $bar->advance();
            }
            $bar->finish();
        }

        $this->output->newLine();
    }

    private function downloadEpisode(PodcastItem $podcast, $disk)
    {
        try{
            $response = $this->guzzle->request('GET', $podcast->audio_url);

            if(!$response->getStatusCode() == '200')
            {
                return false;
            }

            $fileName = $this->createFilename($podcast, $response);
            Storage::disk($disk)->put($fileName, $response->getBody()->getContents());

        }
        catch (\GuzzleHttp\Exception\ClientException $e)
        {
            return false;
        }

        return $fileName;
    }

    private function createFilename(PodcastItem $podcast, $response)
    {
        //TODO this is a horrible idea and should be fixed as soon as posible
        $title = preg_replace('/[^A-Za-z0-9_\-]/', '_', $podcast->title);

        return $podcast->podcast()->first()->machine_name . '/'
            . $title . '.' .
            $this->mimes->getExtension($response->getHeaderLine('Content-Type'));
    }
}
