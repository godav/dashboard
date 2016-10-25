<?php
App::uses('AppController', 'Controller');

class UsersController extends AppController {
	public $components = array('Session');
        
         private $cProject;
        
	//  Those errors return from INAT, We want to translate them
	var $errorTranslations = array(
		'Email has already been taken' => 'דוא"ל זה תפוס על ידי משתמש אחר',
		'Login has already been taken' => 'שם משתמש זה תפוס על ידי משתמש אחר',
		'Email can\'t be blank' => 'חובה להזין דוא"ל',
		'Password can\'t be blank'  => 'חובה להזין סיסמא',
		'Login can\'t be blank' => 'חובה להזין שם משתמש',
		'Login is too short (minimum is 3 characters)'  => 'שם משתמש חייב להיות לפחות 3 תווים',
		'Login use only letters, numbers, and -_ please.'   => 'יש להשתמש רק באותיות לעוזיות ומספרים'
	);

	//  Default project to add user after login
	var $defaultProjectId = 4527; // tatzpiteva

	// default route page
	function index() 
	{
		// $data = $this->appInitData();
	 //    $this->set('globalData', $data);
	}
 
    // sync users from iNat server
    function json_sync()
    {
  
	$this->autoRender = false;

        $startTime = time();

        $this->iNat['url'] = 'http://api.inaturalist.org/v1';
        $per = 100; // how many to get per page?
        $pageSum=0;
        
        $Projects=$this->User->Project->find('all');
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

            printf("[User-SYNC] Starting user sync ... %s\n", date('r') );
            
            // get observations while they last
            while( $got < $total )
            {
                echo "\n[PAGE] Getting page $page | size: $per | got so far: $got | total: $total\n";
                
                // check first sync if yes retrive all else retrive only those update since last sync in DB ($fullDateTime)
            //    if (!$flag)
                $strAPI="/projects/$this->cProject/members?page=$page&per_page=$per";
                       //     "/observations?project_id=$this->cProject&locale=iw&page=$page&per_page=$per&order=desc&order_by=created_at";
            //    else 
            //        $strAPI="/observations?project_id=$this->cProject&updated_since=$fullDateTime&locale=iw&page=$page&per_page=$per&order=desc&order_by=created_at";            
                
                $users = $this->_curlWrap(
                    $strAPI,
                    null,'GET',true); // decode results  

                // what's our total?
                if (!empty($users->total_results)) 
                    $total = $users->total_results;
                else
                    break;
                // this operation is breaking the json input from the api into fields acoording to DB table page by page.
                if(!empty($users->results))
                   $got=$this->_pageHandle($users,$got);

                // next page please
                $page++;    
            }
            $pageSum=$pageSum+$page-1;
        }    
		$delta = time() - $startTime;
		printf("\n[USERS-SYNC] Ended users sync ... %s | took %d seconds and %d API calls\n", date('r'), $delta, $pageSum  );
     echo "</pre>"; 
	}

    // This function handle all the observation in a page. by breaking the json data into table field.
    function _pageHandle($users,$got) 
    {
        foreach($users->results as $U )
        {
            $got++; 
            $justUserUpdate = $this->User->find('first', array(
                            'conditions' => array('User.id' => $U->user->id),
                            'fields' => array('User.updated_at')
            ));
         
            if (!empty($justUserUpdate))
            {   
                $timeDB = strtotime($justUserUpdate['User']['updated_at']);
                $timeJSON = strtotime($U->updated_at);
                // check if the timestamp from api record is newer from what stored in DB
                if (($timeJSON-$timeDB<=0))    
                    continue;
            }
               
            $tmpUser = array(
                            'id' => $U->user->id,
                            'login' => $U->user->login,
                            'name' => $U->user->name,
                            );
                                    
                            // icon url is optional
                            if( !empty($U->user->icon_url) )
                                $tmpUser['icon'] = $U->user->icon_url;
                                    
                            // role is optional
                            if( !empty($U->role) )
                                $tmpUser['role'] = $U->role;
                    
             $tmpUser['updated_at']=date(DATE_ATOM, mktime(date("H") , date("i"), date("s"), date("m")  , date("d"), date("Y")));
            // make formation to save relation between project and user                
            $dataFormat=array(
                            'User' => $tmpUser,
                            'Project' => array('Project' => array(
                                                                (int) 0 => $this->cProject)
                                              )               
                             );               

            echo "[USER $got] Saving $tmpUser[id]\n";
            /*               . " - $tmpObservation[observed_on]"
                          . " - $tmpObservation[num_identification_agreements] - $tmpObservation[num_identification_disagreements]"
                          . " - $tmpObservation[latitude] - $tmpObservation[longitude]"
                          . "- $tmpObservation[created_at] - $tmpObservation[updated_at]"
                          . "- $tmpObservation[comments_count] - $tmpObservation[identifications_count]";
             */       
           
            
            $this->User->save($dataFormat);

        }         
            return $got;
    }
        
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
			$this->Session->write('bearer', $login->access_token);
			
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

	function og_view($u_id){

		$meta = array();

		$r = $this->_curlWrap("/users/{$u_id}.json", null , 'GET');

		//	pr($r);

		//  Set Item Title
		$meta['og:title'] = "משתמש: ";
		if (isset($r->name))
			$meta['og:title'] .= $r->name;
		elseif (isset($r->login))
			$meta['og:title'] .= $r->login;


		//  Images
		if(isset($r->user_icon_url)) $meta['og:image'] = $r->user_icon_url;

		//  Description
		$meta['og:description'] = '';
		if(isset($r->description))  $meta['og:description'] = addslashes($r->description) . " | ";

		if(isset($r->observations_count))
			$meta['og:description'] .= "מספר תצפיות: " . $r->observations_count;
		if(isset($r->identifications_count))
			$meta['og:description'] .= " | מספר זיהויים: " . $r->identifications_count;

		$meta['og:description'] .= "\n";
		$meta['og:description'] .= 'תצפיטבע - קהילה מנטרת טבע בגולן.';

		$this->set('meta', $meta);
	}
}
