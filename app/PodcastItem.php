<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PodcastItem extends Model
{

	protected $fillable = ['user_id', 'podcast_id', 'title', 'description', 'url', 'audio_url','published_at', 'download_error', 'download_error_desc'];

	/**
	 * An item belongs to a podcast
	 */
	 public function podcast()
	 {
        return $this->belongsTo('App\Podcast');
     }

}
