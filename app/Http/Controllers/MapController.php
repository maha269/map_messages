<?php

namespace App\Http\Controllers;

use FarhanWazir\GoogleMaps\GMaps;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class MapController extends Controller
{
    protected $gmaps;
    public function __construct(GMaps $gmaps){
        $this->gmaps = $gmaps;
    }

    public function index(){
        $categories = ['Neutral', 'Positive','Negative'];
        //initialize google maps
        $gmaps = new GMaps();
        $config['center'] = 'Cairo';
        $config['zoom'] = 3;
        $config['map_height'] = '600px';
        $config['geocodeCaching'] = true;
        $config['scrollwheel'] = false;
        $gmaps->initialize($config);
        // add marker at Cairo
        $marker['position'] = 'cairo';
        $marker['infowindow_content'] = 'cairo';
        $marker['label'] = 'cairo';
        $marker['title'] = 'cairo';
        $marker['icon']='http://maps.google.com/mapfiles/ms/micons/yellow-dot.png';
        $gmaps->add_marker($marker);

        $map = $gmaps->create_map();
        return view('mapHome')->with( [
            'categories'=> $categories,
            'map'=>$map
        ]);
    }
    public function sortMessages (Request $request)
    {
        //get the feed from https://spreadsheets.google.com/feeds/list/0Ai2EnLApq68edEVRNU0xdW9QX1BqQXhHRl9sWDNfQXc/od6/public/basic?alt=json
        $client = new Client();
        $response = $client->request('GET', 'https://spreadsheets.google.com/feeds/list/0Ai2EnLApq68edEVRNU0xdW9QX1BqQXhHRl9sWDNfQXc/od6/public/basic?alt=json');
        $statusCode = $response->getStatusCode();
        $data = json_decode($response->getBody()->getContents());

        $categories = ['Neutral', 'Positive','Negative'];

        $entry = $data->feed->entry;
        $contentArray = [];
        foreach ($entry as $item ){
            array_push($contentArray , $item->content);
        }
        $arrayOfMessages = [] ;
        foreach ($contentArray as $value) {
            $arrayVal = json_decode(json_encode($value), true);
            array_push($arrayOfMessages ,  $arrayVal);
        }

        $messages = [];
        foreach($arrayOfMessages as $messageItem){
            $msg = $messageItem['$t'];
            $messageStartPos = strpos($msg, 'message:');
            $messageEndPos = strpos($msg, ', sentiment');
            $sentiment = substr($msg , $messageEndPos +13 , strlen($msg) - ($messageEndPos +12));
            if( $sentiment == $request->category){
                $messageBody = substr($msg ,$messageStartPos + 8 , $messageEndPos - ($messageStartPos +8 ));
                array_push($messages , $messageBody);
            }
        }

        $gmaps = new GMaps();
        $config['center'] = 'Cairo';
        $config['zoom'] = 3;
        $config['map_height'] = '600px';
        $config['geocodeCaching'] = true;
        $config['scrollwheel'] = false;
        $gmaps->initialize($config);
        //adding marker for eac message
        foreach ($messages as $message) {
            preg_match_all("/[A-Z][a-z]*/",$message,$op);
            $flattenedArray = array_flatten($op);
            $location = $flattenedArray[sizeof($flattenedArray) -1];

            $marker['position'] = $location;
            $marker['infowindow_content'] = $message;
            $marker['label'] =$message;
            $marker['title'] =$message;
            $marker['icon']='http://maps.google.com/mapfiles/ms/micons/red-dot.png';
            $gmaps->add_marker($marker);
        }

        $map = $gmaps->create_map();

        return view('sortedMessagesMap')->with( [
            'map'=>$map,
            'categories'=>$categories
        ]);
    }
}
