<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\OSRS_player_stats;
use App\RecentSearch;

class MainController extends Controller
{
    /**
     * Loads home page
     *
     * @return View Home
     */
    public function index($account = false)
    {
        return view('home', [
            'recent_searches' => RecentSearch::latest()->take(17)->get(),
            'onload_account' => $account,
        ]);
    }

    /**
     * Endpoint that returns player stats
     * Uses OSRS class to crawl OSRS site, and return stats as Object.
     *
     * @param String player username
     * @return View Home
     */
    public function get_player_stats($username)
    {
        // get player stats using OSRS player stats crawler
        $fetched_stats = OSRS_player_stats::get_player_stats($username);

        if(!$fetched_stats) {
            return array(
                'status' => 'error',
                'body' => [
                    'message' => 'Player ' . $username . ' not found'
                ]
            );
        } else {

            // Insert or Updates
            RecentSearch::insertOrUpdateRecentSearch($username);

            $fetched_stats = (array) $fetched_stats;
  
            // proccess spliced/overall structure for JS data objects
            $player_stats_A = array_splice($fetched_stats, 1, 12);
            $player_stats_B = array_splice($fetched_stats, 1);
            
            $overall = false;
            if(isset($fetched_stats['Overall'])) {
                $overall = $fetched_stats['Overall'];
            } 

            return array(
                'status' => 'success',
                'body' => [
                    'username' => $username,
                    'stats' => [
                        'splices' => [$player_stats_A, $player_stats_B],
                        'overall' => $overall
                    ],
                ]
            );
        }
    }
}
