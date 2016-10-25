<?php
App::uses('AppController', 'Controller');

class ObservationsController extends AppController {
	
    public $components = array('Session');
  //  private $cTime = 'date(DATE_ATOM, mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y")))';
    private $cProject;
    //  Default project to add user after login
    var $defaultProjectId = 4527; // tatzpiteva
    
    // default route page
    function index() 
    {
	//	 $data = $this->appInitData();
	 //    $this->set('globalData', $data);
    }
    
    function _urlEncode($string) 
    {
        $entities = array('%3A', '%2B', '%20');
        $replacements = array(":",  "+", ' ');
        return str_replace($replacements,$entities , $string);
      //  return str_replace($replacements,$entities , urlencode($string));
    }
    
    // sync users from iNat server
    function json_sync()
    {
  
	$this->autoRender = false;

        $startTime = time();
        //retrived the last update from DB

        
        //make encode to the date like in inaturalist
    //    if (!empty($fullDateTime))
             

        $this->iNat['url'] = 'http://api.inaturalist.org/v1';
        $per = 100; // how many to get per page?
        $pageSum=0;
       
        $lastUpdate=$this->Observation->find('first', array(
                                            'fields' => array('Observation.updated_at'),
                                            'order' => array('Observation.updated_at DESC')
                                            )
        );
        
        $flag=true;
        if (empty($this->Observation->find('first')))
            $flag=false;
        else 
            $fullDateTime=$this->_urlEncode($lastUpdate['Observation']['updated_at']);
        
        $this->loadModel('Project');
        $Projects=$this->Project->find('all');
        echo "<pre>";   
        foreach( $Projects as $P )
        {
            // force the new API URL
            $page = 1; // which page are we on?
            $got = 0; // how many records did we read?
            $total = $per; // total temp value = page size
	
       //     pr($P);
            $this->cProject=$P['Project']['id'];
            echo "\nproject number : $this->cProject\n";

            printf("[OBSERVATION-SYNC] Starting user sync ... %s\n", date('r') );
            
            // get observations while they last
            while( $got < $total )
            {
                echo "\n[PAGE] Getting page $page | size: $per | got so far: $got | total: $total\n";
                
                // check first sync if yes retrive all else retrive only those update since last sync in DB ($fullDateTime)
                if (!$flag)
                    $strAPI="/observations?project_id=$this->cProject&locale=iw&page=$page&per_page=$per&order=desc&order_by=created_at";
                else 
                    $strAPI="/observations?project_id=$this->cProject&updated_since=$fullDateTime&locale=iw&page=$page&per_page=$per&order=desc&order_by=created_at";            
                
                $observations = $this->_curlWrap(
                    $strAPI,
                    null,'GET',true); // decode results  

                // what's our total?
                if (!empty($observations->total_results)) 
                    $total = $observations->total_results;
                else
                    break;
                // this operation is breaking the json input from the api into fields acoording to DB table page by page.
                if(!empty($observations->results))
                   $got=$this->_pageHandle($observations,$got);

                // next page please
                $page++;    
            }
            $pageSum=$pageSum+$page-1;
        }    
		$delta = time() - $startTime;
		printf("\n[OBSERVATION-SYNC] Ended observation sync ... %s | took %d seconds and %d API calls\n", date('r'), $delta, $pageSum  );
     echo "</pre>";
	}

    // This function handle all the observation in a page. by breaking the json data into table field.
    function _pageHandle($observations,$got) 
    {
        foreach($observations->results as $O )
        {
               $got++; 
               
               $justObservationUpdate = $this->Observation->find('first', array(
                                'conditions' => array('Observation.id' => $O->id),
                                'fields' => array('Observation.updated_at')
                 )); 

                if (!empty($justObservationUpdate))
                {   
                    $timeDB = strtotime($justObservationUpdate['Observation']['updated_at']);
                    $timeJSON = strtotime($O->updated_at);
        
                    // check if the timestamp from api record is newer from what stored in DB
                    if ($timeJSON-$timeDB<=0)
                    {   // used to save all relation between project in case observation already in DB
                        
                        $beforeUpdate = $this->Observation->ObservationsProject->find('first', array(
                                'conditions' => array('observation_id' => $O->id, 'project_id' => $this->cProject),
                                'fields' => array('observation_id','project_id')
                        ));
                        
                        if (empty($beforeUpdate))
                                $this->Observation->ObservationsProject->save(array('observation_id' => $O->id,
                                          'project_id' => $this->cProject)); 
                        
                        $beforeUpdate = $this->Observation->User->ProjectsUser->find('first', array(
                                'conditions' => array('user_id' => $O->user->id, 'project_id' => $this->cProject),
                                'fields' => array('user_id','project_id')
                        ));
                        
                        if (empty($beforeUpdate))
                                $this->Observation->User->ProjectsUser->save(array('user_id' => $O->user->id,
                                          'project_id' => $this->cProject)); 
                        
                        if (!empty($O->taxon))
                        {
                            $beforeUpdate = $this->Observation->Taxon->ProjectsTaxon->find('first', array(
                                'conditions' => array('taxon_id' => $O->taxon->id, 'project_id' => $this->cProject),
                                'fields' => array('taxon_id','project_id')
                            ));
                            if (empty($beforeUpdate))
                                $this->Observation->Taxon->ProjectsTaxon->save(array('taxon_id' => $O->taxon->id,
                                          'project_id' => $this->cProject));  
                        }
                        continue;
                    }
                }
               
                if(!empty($O->observed_on))
                    $tmpObservation['observed_on'] = $O->observed_on;
                if(!empty($O->geojson->coordinates['0']))
                    $tmpObservation['longitude'] = $O->geojson->coordinates['0']; 
                if(!empty($O->geojson->coordinates['1']))
                     $tmpObservation['latitude'] = $O->geojson->coordinates['1'];
                
                $tmpObservation['num_identification_agreements'] = $O->num_identification_agreements;
                $tmpObservation['num_identification_disagreements'] = $O->num_identification_disagreements;
                $tmpObservation['created_at'] = $O->created_at;
                $tmpObservation['comments_count'] = $O->comments_count;
                $tmpObservation['identifications_count'] = $O->identifications_count;
                $tmpObservation['id'] = $O->id;
                
                // in case user not exists in DB create it to maintain relationship of foreign key
                if (empty($this->Observation->User->find('first', array(
                                'conditions' => array('User.id' => $O->user->id),
                    )))) 
                {   
                    $tmpUser = array(
                                    'id' => $O->user->id,
                                    'login' => $O->user->login,
                                    'name' => $O->user->name,
                                    );
                                    
                                    // icon url is optional
                                    if( !empty($O->user->icon_url) )
                                        $tmpUser['icon'] = $O->user->icon_url;
                                    
                                    // role is optional
                                    if( !empty($O->user->role) )
                                        $tmpUser['role'] = $O->user->role;
                    

                    $this->Observation->User->save($tmpUser);
                    $this->Observation->User->ProjectsUser->save(array('user_id' => $O->user->id,
                                          'project_id' => $this->cProject)); 
                    
                } else
                {
                        $beforeUpdate = $this->Observation->User->ProjectsUser->find('first', array(
                                'conditions' => array('user_id' => $O->user->id, 'project_id' => $this->cProject),
                                'fields' => array('user_id','project_id')
                        ));
                        
                        if (empty($beforeUpdate))
                                $this->Observation->User->ProjectsUser->save(array('user_id' => $O->user->id,
                                          'project_id' => $this->cProject));  
                }
                
                
                
                
                // in case taxon not exists in DB create it to maintain relationship of foreign key
                if (!empty($O->taxon))
                {   /*
                    if (empty($this->Observation->Taxon->find('first', array(
                                    'conditions' => array('Taxon.id' => $O->taxon->id),
                        )))) 
                    {   
                   */
                        $tmpTaxon['name'] = $O->taxon->name;
                        $tmpTaxon['id'] = $O->taxon->id;
                        $tmpTaxon['iconic_id'] = $O->taxon->iconic_taxon_id;
                        
                        if (!empty($O->taxon->default_photo->square_url))
                            $tmpTaxon['small_photo'] = $O->taxon->default_photo->square_url;

                        if (!empty($O->taxon->default_photo->medium_url))
                            $tmpTaxon['medium_photo'] = $O->taxon->default_photo->medium_url;

                        if (!empty($O->taxon->preferred_common_name)) 
                            $tmpTaxon['preferred_common_name'] = $O->taxon->preferred_common_name;

                        if (!empty($O->taxon->iconic_taxon_name)) 
                            $tmpTaxon['iconic_name'] = $O->taxon->iconic_taxon_name;                     
                       
                        
                        $this->Observation->Taxon->save($tmpTaxon);
                        
                        $beforeUpdate = $this->Observation->Taxon->ProjectsTaxon->find('first', array(
                                'conditions' => array('taxon_id' => $O->taxon->id, 'project_id' => $this->cProject),
                                'fields' => array('taxon_id','project_id')
                        ));
                        
                        if (empty($beforeUpdate))
                                $this->Observation->Taxon->ProjectsTaxon->save(array('taxon_id' => $O->taxon->id,
                                          'project_id' => $this->cProject));  
                            
                }
        
                $tmpObservation['user_id'] = $O->user->id;
                if( !empty($O->taxon->id) )
                    $tmpObservation['taxon_id'] = $O->taxon->id;
                   
                $tmpObservation['updated_at']=date(DATE_ATOM, mktime(date("H") , date("i"), date("s"), date("m")  , date("d"), date("Y")));
 
                $this->Observation->save($tmpObservation);
                
//                $beforeUpdate = $this->Observation->ObservationsProject->find('first', array(
//                        'conditions' => array('observation_id' => $O->id, 'project_id' => $this->cProject),
//                        'fields' => array('observation_id','project_id')
//                ));
//
//                if (empty($beforeUpdate))
                        $this->Observation->ObservationsProject->save(array('observation_id' => $O->id,
                                  'project_id' => $this->cProject)); 
//                
//                 $this->Project->Observation->save($tmpObservation);
                
                echo "[OBSERVATION $got] Saving $tmpObservation[id]\n";
        }         
            return $got;
    }
        
}
 

