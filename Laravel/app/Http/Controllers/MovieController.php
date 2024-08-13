<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MovieController extends Controller
{
    public function index()
    {
        $response = Http::timeout(500)->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',])->get('https://www.imdb.com/chart/top/');
        $htmlContent = $response->body();
        $pattern = '/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s';
        preg_match($pattern, $response, $matches);
        if ($matches[1]) {
            $jsonData = $matches[1];
            $data = json_decode($jsonData, true);
            $moviesData = $data['props']['pageProps']['pageData']['chartTitles']['edges'];
            if (isset($moviesData)) {
                $movies = [];
                foreach ($moviesData as $key => $movie) {
                    $movieInfo = $movie['node'];
                    $movies[] = [
                        'name' => $movieInfo['titleText']['text'],
                        'release' => $movieInfo['releaseYear']['year'],
                        'time' => $this->convertSeconds($movieInfo['runtime']['seconds']),
                        'rating' => $movieInfo['ratingsSummary']['aggregateRating'],
                    ];
                }
            }
        }
        dd($movies);
        // foreach ($movies as $item) {
        //     Movie::create([
        //         'name' => $item['name'],
        //         'release' => $item['release'],
        //         'time' => $item['time'],
        //         'rating' => $item['rating'],
        //     ]);
        // }
    }

    function convertSeconds($totalSeconds)
    {
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $formattedDuration = '';
        if ($hours > 0) {
            $formattedDuration .= $hours . 'H ';
        }
        if ($minutes > 0) {
            $formattedDuration .= $minutes . 'M';
        }
        return trim($formattedDuration);
    }
}
