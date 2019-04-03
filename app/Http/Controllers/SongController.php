<?php

namespace App\Http\Controllers;

use App\Song;
use App\Lyric;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use function GuzzleHttp\json_encode;

class SongController extends Controller
{
    public function index()
      {
        $song = Song::all();
        return $song->toJson();
      }

      public function store_from_api($album_name) {

        // GET ALBUM TRACKLIST FROM **** API

        //Call STANDS 4 API for SONG

        //Stands4 API Data
        $stands4_uid = env("STANDS4_UID", "");
        $stands4_token_id = env("STANDS4_TOKEN_ID", "");

        $url = "https://www.abbreviations.com/services/v2/lyrics.php?uid=" . 
        $stands4_uid . 
        "&tokenid=" . 
        $stands4_token_id . 
        "&term=" . 
        $album_name . 
        "&format=json";
      }

      public function store(Request $request)
      {
        $validatedData = $request->validate([
          'name' => 'required',
          'artist' => 'required',
          'lyrics' => 'required',
        ]);


        $song = song::create([
          'name' => $validatedData['name'],
          'artist' => $validatedData['artist'],
          'lyrics' => $validatedData['lyrics'],
        ]);

        $lyrics_array = preg_split('/\r\n|\r|\n/', $validatedData['lyrics']);

        $giphy_key = env("GIPHY_API_KEY", "");

        foreach ($lyrics_array as $key => $lyric) {

          $search_term = $lyric;
  
          $url = "http://api.giphy.com/v1/gifs/search?q=" . $search_term . "&api_key=" . $giphy_key . "&limit=1";
    
          $client = new Client();
          $body = $client->get($url)->getBody();
          $obj = json_decode($body);
          $image_url = $obj->data[0]->images->downsized_large->url ? $obj->data[0]->images->downsized_large->url : '';

          $new_lyric = Lyric::create([
            'lyric_text' => $lyric,
            'song_id' => $song->id,
            'image_url' => $image_url
          ]);

        }

        return $song->toJson();
      }

      public function show($id)
      {
        $song = Song::with(['lyrics'])->find($id);
 
        return $song->toJson();
      }

}
