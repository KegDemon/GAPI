<?php

namespace Core\Controllers;

use Core\Controllers\ControllerUtils;

class ControllerCore
{
    private $access_type = "offline_access";
    private $appName = APP_NAME;
    private $batch = false;
    private $client;
    private $client_id = CLIENT_ID;
    private $dataBatch;
    private $data;
    private $dates = DATES;
    private $email = CLIENT_EMAIL;
    private $key;
    private $key_file = KEY_FILE;
    private $metrics = METRICS;
    private $dimensions = DIMENSIONS;
    private $options = REQ_OPTS;
    private $service;
    private $profileBatch;
    private $profiles;
    private $secret = "notsosecret";
    
    private $unfiltered;

    /**
     * 
     */
    public function __construct()
    {
        $this->client = new \Google_Client();
        
        #$this->dimensions = explode(',', $this->dimensions);
        
        $this->client->setApplicationName($this->appName);
        $this->client->setClientId($this->client_id);
        $this->client->setClientSecret($this->secret);
        $this->key = $this->assignKey();
        $this->client->setAssertionCredentials($this->setCreds());
        $this->client->setAccessType($this->access_type);
        
        $this->options = unserialize($this->options);
        
        $this->service = new \Google_Service_Analytics($this->client);
        $this->batch = true;
        $this->client->setUseBatch($this->batch);
        
        $this->profileBatch = new \Google_Http_Batch($this->client);
        $this->profiles = $this->getProfiles();
        
        $this->dataBatch = new \Google_Http_Batch($this->client);
        $this->data = $this->fetchData();
        
    }
    
    /**
     * getData returns reduced data that is formatted
     * @return Object
     */
    public function getData() 
    {
        return $this->data;
    }
    
    /**
     * unfilteredData returns all data in raw form
     * @return type
     */
    public function unfilteredData()
    {
        return $this->unfiltered;
    }
    
    /**
     * getTitles processes the Metrics 
     * and converts them to human readable format
     * @return array
     */
    public function getTitles()
    {
        $utils = new ControllerUtils();
        $tmp = array();
        $metrics = explode(',', $this->metrics);
        foreach ( $metrics as $t ) {
            $tmp[] = ucwords(implode(' ', $utils->splitCamelCase(str_replace('ga:', '', $t))));
        }
        
        return $tmp;
    }
    
    /**
     * setCreds sets the credentials for Auth Access
     * to Google services
     * @return \Google_Auth_AssertionCredentials
     */
    private function setCreds()
    {
        return new \Google_Auth_AssertionCredentials (
                $this->email,
                array('https://www.googleapis.com/auth/analytics.readonly'),
                $this->key
            );
    }
    
    /**
     * assignKey gets the key file and processes it
     * @return File
     */
    private function assignKey()
    {
        $file = file_get_contents(realpath(__DIR__ . '/../../') . '/' .  $this->key_file);
        return $file;
    }
    
    /**
     * getProfiles gets all profiles affiliated with 
     * dev email account
     * @return Array of Objects
     */
    private function getProfiles()
    {
        $this->profileBatch->add( $this->service->management_profiles->listManagementProfiles('~all', '~all'), "ListAllProfileViews" . rand(0,10000000) );
        
        $p = $this->profileBatch->execute();
        
        #$this->unfiltered = $p; 
                
        $profiles = array();
        foreach ($p as $profile ) {
            if ( isset($profile->items) ) {
                $profiles[] = $profile->items;
            }
        }
        
        return $profiles;
    }
    
    /**
     * fetchData Gets and parses all raw data that's returned per account
     * loops through and rests on a 10 Request/Second account limitations
     * @return Object
     */
    private function fetchData()
    {
        sleep(1);
        $date = unserialize($this->dates);
        $urls = array();
        $temp = array();
        $count = count($this->profiles[0]);
        $explodedMetrics = array_chunk(explode(',', $this->metrics), 9);
        $explodedDimensions = array_chunk(explode(',', $this->dimensions), 1);
        
        /*
         * Profile Loop to itterate over each one
         */
        foreach ( $this->profiles[0] as $b => $p ) {
            $reqCounter = 0;
            
            /*
             * Loop through Dimensions
             */
            foreach ( $explodedDimensions as $k => $dimension ) {
                $this->dataBatch = new \Google_Http_Batch($this->client);
                $imp = implode(',', $dimension);
                $mergeMe = array();
                /*
                 * Loop through Metrics
                 */
                foreach ( $explodedMetrics as $int => $metric ) {
                    
                    if ( !in_array('ga:users', $metric) ) {
                        $metric[] = 'ga:users';
                    }
                    
                    // Add the correct data to request for each batch
                    $this->dataBatch->add( $this->service->data_ga->get(
                            "ga:" . $p->id,
                            $date['start'], 
                            $date['end'], 
                            implode(',', $metric), 
                            array(
                                'dimensions' => $imp, 
                                'max-results' => 10,
                                'sort' => '-ga:users'
                                )
                            ),
                            $p->id);
                    
                    // Trigger an execute after each batch
                    $data = $this->dataBatch->execute();
                    
                    foreach ( $data as $row ) {
                        $mergeMe[$int] = @$row->rows;
                    }
                    
                } // End Metrics Loop
                
                /*
                 * Reset keys to correct metric name
                 * for easy identification
                 */
                $test = array();
                
                foreach ( $mergeMe as $i => $m ) {
                    $arrayKeys = array($imp);
                    
                    foreach ( $explodedMetrics[$i] as $met ) {
                        $arrayKeys[] = $met;
                    }
                    
                    if ( $i > 0 ) {
                        $arrayKeys[] = 'ga:users';
                    }
                    if ( is_array($m) ) {
                        foreach ( $m as $me ) {
                            $test[] = array_combine($arrayKeys, $me);
                        }
                    }
                }
                
                /*
                 * Lets do some comparative logic
                 * Wooooo boy
                 */
                $inc = array();
                
                foreach ( $test as $in => $merge ) {
                    $c = count($test);
                    
                    /*
                     * Start the search from the
                     * currently running loop + 1
                     * to check for additional data
                     * that's missing
                     */
                    for ($i = $in + 1; $i < $c; ++$i ) {
                        if ( $merge[$imp] == $test[$i][$imp] ) {
                            $inc[] = array_merge($merge, $test[$i]);
                        }
                    }
                }
                
                $temp[$p->websiteUrl][$imp] = $inc;
                
            } // End Dimensions Loop
            
            // Tell PHP to sleep after each Dimension
            // to prevent request timeouts
            sleep(1);
           
        } // End Profiles Loop
        
        $this->unfiltered = $temp;
        
        $return = array();
        
        return $return;
    }
    
    private function fetchData_old()
    {
        sleep(1);
        $date = unserialize($this->dates);
        $urls = array();
        $temp = array();
        $count = count($this->profiles[0]);
        /*
         * Profile Loop to itterate over each one
         */
        foreach ( $this->profiles[0] as $b => $p ) {
            
            /*
             * Every Tenth profile fire an execution and start
             * a new Batch Request after sleeping for one
             * second to prevent request overflow failure
             */
            if ( $b % 10 === 0 ) {
                $temp[] = $this->dataBatch->execute();
                sleep(1);
                $this->dataBatch = new \Google_Http_Batch($this->client);
            }

            $urls[] = $p->websiteUrl;
            $this->dataBatch->add( $this->service->data_ga->get("ga:" . $p->id, $date['start'], $date['end'], $this->metrics, $this->options), $p->id );
            
            /*
             * Make sure to trigger a final execution if
             * we have an odd number of profiles counted
             */
            if ( $b + 1 === $count ) {
                $temp[] = $this->dataBatch->execute();
            }
            
        }
        
        $data = array();
        
        foreach ( $temp as $profile ) {
            foreach ( $profile as $d ) {
                $data[] = $d->rows;
            }
        }
        
        $t = array();
        
        foreach ( $data as $dat ) {
            if ( $dat !== null ) {
                $t[] = $dat;
            }
        }
        
        $int = 0;
        $return = array();
        $exp = explode(',', $this->metrics);
        
        foreach ( $data as $req) {
            $tmp = array(
                'pid' => $int,
                'id' => $urls[$int]
            );
            
            ++$int;
            
            if ( @$req->totalResults > 0 ) {
                foreach ($exp as $i => $metric) {
                    $tmp[$metric] = $req['rows'][0][$i];
                }
            } else {
                foreach ($exp as $i => $metric) {
                    $tmp[$metric] = 0;
                }
            }
            
            $return[] = $tmp;
        }
        
        sort($return);
        
        return $return;
    }
    
}

