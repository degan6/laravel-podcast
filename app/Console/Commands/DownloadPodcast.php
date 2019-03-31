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
        $itemsCollect = PodcastItem::where('downloaded', 0)->where('download_error', '');

        if($this->option('auto') == false) {
            $items->podcast->find($this->option('podcastId'));
        }

        $items = $itemsCollect->get();

        if(count($items) == 0)
        {
            $this->output->text("All items downloaded");
            return true;
        }

        $bar = $this->output->createProgressBar(count($items));
        foreach ($items as $episode)
        {
            $downloadResult = $this->downloadEpisode($episode,'podcasts');

            if($downloadResult['error']){
                $episode->downloaded = 0;
                $episode->download_error = $downloadResult['e']->getMessage();

            } else {
                $episode->file_name = $downloadResult['filename'];;
                $episode->downloaded = 1;
            }

            $episode->download_date = date("Y-m-d H:i:s");
            $episode->save();

            $bar->advance();
        }
        $bar->finish();

        $this->output->newLine();
    }

    private function downloadEpisode(PodcastItem $podcast, $disk)
    {
        try{
            $response = $this->guzzle->request('GET', $podcast->audio_url);

            $fileName = $this->createFilename($podcast, $response);
            Storage::disk($disk)->put($fileName, $response->getBody()->getContents());

            $this->notifyOnDownload($podcast->podcast()->first()->name, true, $fileName, $podcast->title);
        }
        catch (\GuzzleHttp\Exception\GuzzleException $e)
        {
            $this->notifyOnDownload($podcast->title, false, '', '', $e->getMessage());
            return ['error' => true, 'e' => $e];
        }

        return ['error' => false, 'filename' => $fileName];
    }

    private function createFilename(PodcastItem $podcast, $response)
    {
        //TODO this is a horrible idea and should be fixed as soon as posible
        $title = preg_replace('/[^A-Za-z0-9_\-]/', '_', $podcast->title);

        return $podcast->podcast()->first()->machine_name . '/'
            . $title . '.' .
            $this->mimes->getExtension($response->getHeaderLine('Content-Type'));
    }

    /*
     * @param
     * @param
     */
    private function notifyOnDownload($podcastName, $downloadStatus, $fileName = '', $itemTitle = '', $downloadError = '')
    {
        try{
            $response = $this->guzzle->post(
                config('app.download_url'), [
                \GuzzleHttp\RequestOptions::JSON =>  [
                    'download_status' => $downloadStatus,
                    'download_error' => $downloadError,
                    'podcast_name' => $podcastName,
                    'file_name' => $fileName,
                    'item_name' => $itemTitle
                ]]
            );
        }
        catch (\GuzzleHttp\Exception\GuzzleException $e)
        {
            return ['error' => true, 'e' => $e];
        }
        return ['error' => false, 'filename' => $fileName];
    }
}
