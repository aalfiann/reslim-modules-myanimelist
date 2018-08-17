<?php

namespace modules\myanimelist;                      //Make sure namespace is same structure with parent directory

use \classes\Auth as Auth;                          //For authentication internal user
use \classes\JSON as JSON;                          //For handling JSON in better way
use \classes\UniversalCache as UniversalCache;      //For internal caching data
use \classes\CustomHandlers as CustomHandlers;      //To get default response message
use \modules\myanimelist\MAL as MAL;                //To access with MyAnimeList API 
use PDO;                                            //To connect with database

	/**
     * Unofficial MyAnimeList API
     *
     * @package    modules/myanimelist
     * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
     * @copyright  Copyright (c) 2018 M ABD AZIZ ALFIAN
     * @license    https://github.com/aalfiann/reSlim-modules-myanimelist/blob/master/LICENSE.md  MIT License
     */
    class MyAnimeList {

        // database var
        protected $db;

        //base var
        protected $basepath,$baseurl,$basemod;

        //master var
        var $username,$token;
        
        //data var
        var $id,$title,$pretty=false,$proxy=null,$proxyauth=null;

        //multilanguage var
        var $lang;
        
        //construct database object
        function __construct($db=null) {
			if (!empty($db)) $this->db = $db;
            $this->baseurl = (($this->isHttps())?'https://':'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
            $this->basepath = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']);
			$this->basemod = dirname(__FILE__);
        }

        //Detect scheme host
        function isHttps() {
            $whitelist = array(
                '127.0.0.1',
                '::1'
            );
            
            if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
                if (!empty($_SERVER['HTTP_CF_VISITOR'])){
                    return isset($_SERVER['HTTPS']) ||
                    ($visitor = json_decode($_SERVER['HTTP_CF_VISITOR'])) &&
                    $visitor->scheme == 'https';
                } else {
                    return isset($_SERVER['HTTPS']);
                }
            } else {
                return 0;
            }            
        }

        //Get modules information
        public function viewInfo(){
            return file_get_contents($this->basemod.'/package.json');
        }


        // MyAnimeList API

        private function getSearchAnime(){
            $key = 'search_anime_';
            if (UniversalCache::isCached($key.$this->title,86400)){
                $cache = json_decode(UniversalCache::loadCache($key.$this->title));
                $datajson = $cache->value;
            } else {
                $mal = new MAL;
                $mal->pretty = $this->pretty;
                $datajson = $mal->findAnime($this->title,true);
                $myjson = json_decode($datajson);
                if (!empty($myjson) && $myjson->status == 'success') UniversalCache::writeCache($key.$this->title,$datajson);
            }
            $json = json_decode($datajson);
            if (!empty($json) && $json->status == 'success'){
                $data = [
                    'results' => $json->results,
                    'status' => $json->status,
                    'code' => 'RS501',
                    'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
                ];
            } else {                        
                $data = [
                    'status' => 'error',
                    'code' => 'RS601',
                    'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
                ];
            }
            return $data;
        }

        private function getFindAnime(){
            $key = 'find_anime_';
            if (UniversalCache::isCached($key.$this->title,86400)){
                $cache = json_decode(UniversalCache::loadCache($key.$this->title));
                $datajson = $cache->value;
            } else {
                $mal = new MAL;
                $mal->pretty = $this->pretty;
                $datajson = $mal->findAnime($this->title,false);
                $myjson = json_decode($datajson);
                if (!empty($myjson) && $myjson->status == 'success') UniversalCache::writeCache($key.$this->title,$datajson);
            }
            $json = json_decode($datajson);
            if (!empty($json) && $json->status == 'success'){
                $data = [
                    'entry' => $json->entry,
                    'metadata' => $json->metadata,
                    'status' => $json->status,
                    'code' => 'RS501',
                    'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'code' => 'RS601',
                    'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
                ];
            }
            return $data;
        }

        private function getAnime(){
            $key = 'get_anime_';
            if (UniversalCache::isCached($key.$this->id,86400)){
                $cache = json_decode(UniversalCache::loadCache($key.$this->id));
                $datajson = $cache->value;
            } else {
                $mal = new MAL;
                $mal->pretty = $this->pretty;
                $datajson = $mal->grabAnime($this->id);
                $myjson = json_decode($datajson);
                if (!empty($myjson) && $myjson->status == 'success') UniversalCache::writeCache($key.$this->id,$datajson);
            }
            $json = json_decode($datajson);
            if(!empty($json) && $json->status == 'success'){
                $data = [
                    'entry' => $json->entry,
                    'metadata' => $json->metadata,
                    'status' => $json->status,
                    'code' => 'RS501',
                    'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'code' => 'RS601',
                    'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
                ];    
            }
            return $data;
        }

        private function getSearchManga(){
            $key = 'search_manga_';
            if (UniversalCache::isCached($key.$this->title,86400)){
                $cache = json_decode(UniversalCache::loadCache($key.$this->title));
                $datajson = $cache->value;
            } else {
                $mal = new MAL;
                $mal->pretty = $this->pretty;
                $datajson = $mal->findManga($this->title,true);
                $myjson = json_decode($datajson);
                if (!empty($myjson) && $myjson->status == 'success') UniversalCache::writeCache($key.$this->title,$datajson);
            }
            $json = json_decode($datajson);
            if (!empty($json) && $json->status == 'success'){
                $data = [
                    'results' => $json->results,
                    'status' => $json->status,
                    'code' => 'RS501',
                    'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'code' => 'RS601',
                    'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
                ];
            }
            return $data;
        }

        private function getFindManga(){
            $key = 'find_manga_';
            if (UniversalCache::isCached($key.$this->title,86400)){
                $cache = json_decode(UniversalCache::loadCache($key.$this->title));
                $datajson = $cache->value;
            } else {
                $mal = new MAL;
                $mal->pretty = $this->pretty;
                $datajson = $mal->findManga($this->title,false);
                $myjson = json_decode($datajson);
                if (!empty($myjson) && $myjson->status == 'success') UniversalCache::writeCache($key.$this->title,$datajson);
            }
            $json = json_decode($datajson);
            if (!empty($json) && $json->status == 'success'){
                $data = [
                    'entry' => $json->entry,
                    'metadata' => $json->metadata,
                    'status' => $json->status,
                    'code' => 'RS501',
                    'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'code' => 'RS601',
                    'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
                ];
            }
            return $data;
        }

        private function getManga(){
            $key = 'get_manga_';
            if (UniversalCache::isCached($key.$this->id,86400)){
                $cache = json_decode(UniversalCache::loadCache($key.$this->id));
                $datajson = $cache->value;
            } else {
                $mal = new MAL;
                $mal->pretty = $this->pretty;
                $datajson = $mal->grabManga($this->id);
                $myjson = json_decode($datajson);
                if (!empty($myjson) && $myjson->status == 'success') UniversalCache::writeCache($key.$this->id,$datajson);
            }
            $json = json_decode($datajson);
            if(!empty($json) && $json->status == 'success'){
                $data = [
                    'entry' => $json->entry,
                    'metadata' => $json->metadata,
                    'status' => $json->status,
                    'code' => 'RS501',
                    'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'code' => 'RS601',
                    'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
                ];    
            }
            return $data;
        }

        //For internal user with token

        // Search Anime
        public function searchAnime(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $data = $this->getSearchAnime();
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
                ];
            }
            return JSON::encode($data,true);
        }

        // Find Anime by Title
        public function findAnime(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $data = $this->getFindAnime();
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
                ];
            }
            return JSON::encode($data,true);
        }

        // Get Anime by ID
        public function getAnimeByID(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $data = $this->getAnime();
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
                ];
            }
            return JSON::encode($data,true);
        }

        // Search Manga
        public function searchManga(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $data = $this->getSearchManga();
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
                ];
            }
            return JSON::encode($data,true);
        }

        // Find Manga by Title
        public function findManga(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $data = $this->getFindManga();
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
                ];
            }
            return JSON::encode($data,true);
        }

        // Get Manga by ID
        public function getMangaByID(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $data = $this->getManga();
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
                ];
            }
            return JSON::encode($data,true);
        }


        // For public access with api key

        // Search Anime Public
        public function searchAnimePublic(){
            return JSON::encode($this->getSearchAnime(),true);
        }

        // Find Anime by Title Public
        public function findAnimePublic(){
            return JSON::encode($this->getFindAnime(),true);
        }

        // Get Anime by ID Public
        public function getAnimeByIDPublic(){
            return JSON::encode($this->getAnime(),true);
        }

        // Search Manga Public
        public function searchMangaPublic(){
            return JSON::encode($this->getSearchManga(),true);
        }

        // Find Manga by Title Public
        public function findMangaPublic(){
            return JSON::encode($this->getFindManga(),true);
        }

        // Get Manga by ID Public
        public function getMangaByIDPublic(){
            return JSON::encode($this->getManga(),true);
        }
    }