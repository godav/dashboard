<?php
App::uses('AppController', 'Controller');

class TaxonsController extends AppController {
	public $components = array('Session');

	//  Default project to add user after login
	var $defaultProjectId = 4527; // tatzpiteva

	// default route page
	function index() 
	{
	//	 $data = $this->appInitData();
	 //    $this->set('globalData', $data);
	}

	// sync users from iNat server
	function json_sync()
	{
               
		$this->autoRender = false;

		// force the new API URL
		$this->iNat['url'] = 'http://api.inaturalist.org/v1';
		$page = 1; // which page are we on?
		$per = 100; // how many to get per page?
		$got = 0; // how many records did we read?
		$total = $per; // total temp value = page size
		$startTime = time();

		// get observations that are in 'taz' project by static proj id
		// this is our userbase
		printf("[TAXONS-SYNC] Starting taxons sync ... %s\n", date('r') ,"<br>");

		// get observations while they last
		while( $got < $total )
		{
			echo "<br>[PAGE] Getting page $page | size: $per | got so far: $got | total: $total<br>";
                        
			$taxons = $this->_curlWrap(
				"/observations?project_id=4527&page=$page&per_page=$per&order=desc&order_by=created_at",
				null,
				'GET',
				true // decode results
				);  
                        
			// what's our total?
			if (!empty($taxons->total_results)) 
                             $total = $taxons->total_results;
                        
                        // this operation is breaking the json input from the api into fields acoording to DB table page by page.
			if( !empty($taxons->results) )
                           $got=$this->pageHandle($taxons,$got);
			
			// next page please
			$page++;
		}

		$delta = time() - $startTime;
		printf("\n[TAXONS-SYNC] Ended taxons sync ... %s | took %d seconds and %d API calls\n", date('r'), $delta, $page - 1 );
	}

        // This function handle all the observation in a page. by breaking the json data into table field.
         function pageHandle($taxons,$got) 
         {
           foreach( $taxons->results as $T )
            {
		$got++; 
           if (empty($T->taxon->id) || empty($T->taxon->name) ||  empty($T->taxon->iconic_taxon_id) ||  empty($T->taxon->iconic_taxon_name) )
                     continue;  
          
          
           $justTaxon = $this->Taxon->find('first', array(
                                'conditions' => array('Taxon.id' => $T->taxon->id),
                                'fields' => array('Taxon.id')
                                
                 ));
               
                if (!empty($justTaxon) )
                     continue;  

            
                    $tmpTaxon['name'] = $T->taxon->name;
                    
                  if (!empty($T->taxon->default_photo->square_url))
                    $tmpTaxon['small_photo'] = $T->taxon->default_photo->square_url;
                 
                  if (!empty($T->taxon->default_photo->medium_url))
                    $tmpTaxon['medium_photo'] = $T->taxon->default_photo->medium_url;
                  
                  if (!empty($T->taxon->preferred_common_name)) 
                    $tmpTaxon['preferred_common_name'] = $T->taxon->preferred_common_name;
                  
                    $tmpTaxon['id'] = $T->taxon->id;
                    $tmpTaxon['iconic_name'] = $T->taxon->iconic_taxon_name;
                    $tmpTaxon['iconic_id'] = $T->taxon->iconic_taxon_id;
      
                   
		/*		
                    echo "<br>[OBSERVATION $got] Saving $tmpObservation[id] - $tmpObservation[observed_on]"
                          . " - $tmpObservation[num_identification_agreements] - $tmpObservation[num_identification_disagreements]"
                          . " - $tmpObservation[latitude] - $tmpObservation[longitude]"
                          . "- $tmpObservation[created_at] - $tmpObservation[updated_at]"
                          . "- $tmpObservation[comments_count] - $tmpObservation[identifications_count]<br>";
                   */ 
                    $this->Taxon->save($tmpTaxon);
                    
                }
            return $got;
        }
    
   
    
}
        /*
	function json_search($term = null)
	{
		if( empty($term) ) return;
		$t = addslashes($term);
		$users = $this->User->query("SELECT * FROM users User WHERE login LIKE '%$t%' OR name LIKE '%$t%'");
		return json_encode($users);
	}
 

	// login static, get a bearer-token
	function json_login($user = null, $password = null)
	{

		if(!empty($user)){
			$params['user']     = $user;
			$params['password'] = $password;
		}else {
			$params = $this->request->data;
		}

		//	$params = array('user'	=> 'carmelneta@gmail.com', 'password'	=> 'status');

		if(empty($params['user']) || empty($params['password']))
			return json_encode(array(
				'success'   => false,
				'error'		=> 'No Username or password set'
			));

		$userAuthReq = array(
			'client_id' => $this->iNat['app_id'],
			'client_secret' => $this->iNat['app_secret'],
			'grant_type' => 'password',
			'username' => $params['user'],
			'password' => $params['password']
		);

		//pr($userAuthReq);
		$j = json_encode($userAuthReq);

		//  Clear old session
		$this->Session->delete('bearer');
		$res = $this->_curlWrap("/oauth/token",$j,'POST');

		if( !empty($res->access_token) )    // got a token value
		{
			$this->Session->write('bearer', $res->access_token);
			return json_encode(array(
				'success' => true,
				'access_token' => $res->access_token
			));
		}
		//	If No Access token, return false
		return json_encode(array(
			'success'   => false,
			'results'   => $res
		));

	}

	function json_logout(){

		$this->Session->delete('bearer');

		return true;
	}

	function json_register(){

//		$login = json_decode($this->json_login('carmelneta','carmel'));
//		$this->Session->write('bearer', "Bearer {$login->access_token}");
//		return $this->json_join_project($this->defaultProjectId);

		$params = $this->request->data;

		//  Fix Params
		$fixParam = array();
		foreach($params as $key => $param){
			$fixParam['user['. $key . ']'] = $param;
		}

		$queryParams = http_build_query($fixParam);

		$res = $this->_curlWrap("/users.json?{$queryParams}",null,'POST');
		//pr($res);
		if(property_exists($res, 'errors')){
			$transErrors = array();
			foreach($res->errors as $error){
				if(isset($this->errorTranslations[$error]))
					$transErrors[] = $this->errorTranslations[$error];
			}

			return json_encode(array(
				'success'   =>  false,
				'errors'    =>  $transErrors
			));

		}elseif(property_exists($res, 'id')){
			//  Registration success!

			//  Do Login
			$login = json_decode($this->json_login($res->login,$params['password']));
			$this->Session->write('bearer', "Bearer {$login->access_token}");
			//  Register User to default project
			$projectAsos = json_decode($this->json_join_project($this->defaultProjectId));

			return json_encode(array(
				'success'   => true,
				'login'     => $login,
				'project'   => $projectAsos,
			));

		}

		return json_encode(array(
			'success'   => false,
			'errors'    => 'unknow',
			'res'       => $res
		));
	}

	// send something to iNat server
	function json_test()
	{
		// write bearer token into session
		//$this->Session->write('bearer','97b5aa6ed1359ed1485dcc2657b7091c034e2b2f4d368403a93028eb77fe36f4');
		// read bearer token from session, this is used in _curlWrap
		$b = $this->Session->read('bearer');
		pr("Token: $b");

		 $res = $this->_curlWrap("/users/edit.json",null,'GET');
		 pr($res);
//		 $res = $this->_curlWrap("/projects/user/amitai.json",null,'GET');
//		 pr($res);
//		$res = $this->_curlWrap("/projects/user/carmelneta.json",null,'GET');
//		pr($res);
//		$res = $this->_curlWrap("/projects/golan-roadkill/members.json",null,'GET');
//		pr($res);
//		 $res = $this->_curlWrap("/users/new_updates.json",null,'GET');
//		 pr($res);
	}

	function json_join_project($projectId){
		$url = "/projects/". $projectId ."/join.json";
		$results = $this->_curlWrap($url, null, "POST", false);
		return $results;

	}

	function json_leave_project($projectId){
		$url = "/projects/". $projectId ."/leave.json";
		$results = $this->_curlWrap($url, null, "DELETE", false);
		return $results;
	}

	function json_edit($userLogin = null){
		if(empty($userLogin))
			return false;

		$url = "/users/{$userLogin}.json";
		$data = $this->request->data;

		// process Files - Upload files to our server for temporary use, Will be deleted after upload to INAT.
		if( !empty($_FILES) )
		{
			// allowed mime / ext
			$allowed = array(
				'image/png'     => 'png',
				'image/gif'     => 'gif',
				'image/jpeg'    => 'jpg',
			);

			if( !empty($allowed[$_FILES['file']['type']]) )
				$data['user']['icon'] = $_FILES['file'];
		}

		$results = $this->_curlWrap($url, json_encode($data), "PUT", false);
		return $results;
	}
 
 */

