<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	public $components = array('Session');
	// header menu service var
	public $headerMenu = array(
		'projects' 	=> '',
		'news' 		=> '',
		'links' 	=> '',
		'pics' 		=> '',
		'curators' 	=> '',
		'articles' 	=> '',
		'texts'		=> ''
	);

	// list of agents to match as open-graph / SEO?
	var $ogAgents = array( 
		// accepts regex patterns
		// '/^Mozilla/',	// this is for testing!
		'/^facebookexternalhit/',
		'/^Twitterbot/',
		'/^Pinterest/',
		'/^Googlebot/',
		'/WhatsApp/',
		);
	// for OG to work, you need to list which objects support it
	var $ogAllow = array(
		// 'object' => array('action1' => 1 (yes),'action2' => 0 (no) ...)
		'obs' 		=> array('view' => 1),
		'projects' 	=> array('view' => 1),
		'users' 	=> array('view' => 1),
		'news' 		=> array('view' => 1),
		);

	// iNat Server Connection vars
    var $iNat = array (
        "url" => "http://www.inaturalist.org",
        "app_id" => "509c092f4ff1a38bd098a90eb2541e81d5c4fe7ab8ad88e77280202f3ff4fd77",
        "app_secret" => "6d19a34cdcc3f9bb7eaa990c33fcca559d929a76790db35b6823641d19c8ec67",
        );

	function beforeFilter()
    {
    	// missing session voodoo, otherwise we loose the bearer token each reload
    	$b = $this->Session->read('bearer');
    	// pr($b);


		// JSON API
        if( isset($this->params['prefix']) )
        {
        	// /json/ is for API
        	if( $this->params['prefix'] == 'json' )
        	{
				$this->response->header("Access-Control-Allow-Headers", "Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

				$this->response->type('json');

	        	// basically, don't render view
	        	$this->autoRender = false;
	        	// and if you happen to, you should make it ajax layout
	        	$this->layout = 'ajax';

		        //  Allow Cross domain for development, will allow angular app to access class from localhost:3000
				/**/

				if (Configure::read('debug'))
				{

					if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == "3000")
					{

						$this->response->header( 'Access-Control-Allow-Origin', '*' );
						$this->Session->write('bearer', '93f32e978676fe232e7edc7d26483090b0b45a4cd0289b6aa9c30eb95430cbee');
					}
				}
				/**/


	        }

	        // OG is for open-graph output
	        if( $this->params['prefix'] == 'og' )
			{
				$this->layout = 'opengraph'; //TODO: make the OG layout!
				// TODO: can catch non-allowed URLS and remove the /og/ prefix! WOOT!
			}

	        // admin prefix = control
	        if( $this->params['prefix'] == 'control')
	        {
	        	$this->layout = 'admin';
	        	$this->set('title_for_layout', 'ממשק ניהול');

	        	// do HTTP basic auth for /control urls
	            // as we cannot do it on the Apache level
	            $reqAuth = 1;
	            // allowed user/md5(pass) combinations
	            $allowedUsers = array(
	                'dan'   => 'af0e955f2fad4a5a62e5494c50e66ae1',
	                'assi'  => '0ebb1e4ad15f8f1099bb38d01fa35bc5',
	                'ofer'  => '09479837fba17f6fc0086fe31923f22b',
	                'oarazy'  => '87449aba96d52649b37c656d268bf541',
	                'ariel' => 'b2dd880eb4fe67bb3969a5b00de2b1e0',
		            'carmel'=> '9acb44549b41563697bb490144ec6258'
	                );

	            if( !empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW']) )
	            {
	                // we have login info, and its good
	                if( !empty($allowedUsers[$_SERVER['PHP_AUTH_USER']]) &&
	                    $allowedUsers[$_SERVER['PHP_AUTH_USER']] == md5($_SERVER['PHP_AUTH_PW'])
	                    )
	                    $reqAuth = 0; // all good, can continue onward
	            }

	            // unless otherwise defined, request auth
	            if( $reqAuth )
	            {
	                header('WWW-Authenticate: Basic realm="Tatzpiteva"');
	                header('HTTP/1.0 401 Unauthorized');
	                echo 'Authentication Required!';
	                $this->layout = 'ajax';
	                exit;
	            }
	        }
			
			if( $this->params['prefix'] == 'stats') {
				$this->layout = 'stats';
			}
	    }
    	else
    	{ 
    		//  Regular Angular App Load

    		// Detect Social / SEO user agents

			//pr($this->params->controller);
			//pr($this->params->action);
	    	// try to match them
	    	foreach( $this->ogAgents as $OG )
	    	{
	    		// 	pr($OG);
	    		if( preg_match($OG, $_SERVER['HTTP_USER_AGENT']) )
	    		{
	    			// pr("Matched USER_AGENT: $OG");

	    			// check if we're on the ogAllow list
	    			if( !empty($this->ogAllow[$this->params->controller]) && // controller is supported
	    				!empty($this->ogAllow[$this->params->controller][$this->params->action]) // action is supported too
	    				)
	    			{
	    				// this controller/action is supported
	    				// redirect to the same URL, but wit OG prefix
	    				$this->redirect("/og/{$this->params->url}");
	    			}
	    			// first match, we're done
	    			break;
	    		}
	    	}

	        //  Get Init Data
	        $data = $this->appInitData();
	        $this->set('globalData', $data);
    	}
    }

    function beforeRender()
    {
        $this->set('headerMenu', $this->headerMenu);
    }

    // shortcut flash
	function _flash($msg = '',$type = 'success')
	{
		// type = success, info, warning, danger, (primary?, default?)
		$this->Session->setFlash($msg,'flash',array( 'class' => "alert-$type") );
	}

	//  Get necessary initiation data for angular app
	function appInitData(){
		App::uses('CakeTime', 'Utility');

		$backUrl = Router::url('/json', true);

		//  Load Project Controller
		App::import('Controller', 'Projects');
		$ProjectsCtrl = new ProjectsController;

		//  Load Curator
		App::import('Controller', 'Curators');
		$CuratorCtrl = new CuratorsController;

		$projects = $ProjectsCtrl->json_index(false);

		$data = array(
			'aToken'    => $this->Session->read('bearer'),
			'curators'  => $CuratorCtrl->json_index(false),
			'projects'  => $projects,
			'defaultProjects'   => array(4527), //  tatzpiteva
			'backUrl'   => $backUrl,
			'debug'     => Configure::read('debug'),
			'dates'     => array(
				'today' => CakeTime::format('today', '%Y-%m-%d'),
				'week'  => CakeTime::format('-7 days', '%Y-%m-%d'),
				'month' => CakeTime::format('-30 days', '%Y-%m-%d'),
				'year'  => CakeTime::format('-365 days', '%Y-%m-%d'),
			),
			'webroot'	=> Router::url('/', true)
		);
		return $data;
	}

	//  This Function Will Be used when app isnt fired by cake (DEVELOPMENT)
	function json_init(){
		$data = $this->appInitData();
		return json_encode($data);
	}

	function json_send_contact(){

		//  get post data, using this to match angular POST format
		$params = $this->request->data;

		//  Lets do some validation
		if(empty($params['name']) || empty($params['email']) || empty($params['content']))
			return 0;

		// Prepare email
		App::uses('CakeEmail', 'Network/Email');
		$em = new CakeEmail('mandrill');
		$em->template('contact');
		$em->emailFormat('text');
		$em->to('info@tatzpiteva.org.il');
		// 	$em->bcc('dan.amitai@gmail.com');
		//	$em->to('carmelneta@gmail.com');
		$em->subject('הודעה חדשה מאתר תצפיטבע');
		$em->viewVars( array('data'  => $params) );
		$em->send();

		return 1;
	}

	function json_about(){

		//  Load Project Controller
		App::import('Controller', 'Pics');
		$PicsCtrl = new PicsController;
		$pics = $PicsCtrl->json_index(false);

		//  Load Project Controller
		App::import('Controller', 'News');
		$NewsCtrl = new NewsController;
		$news = $NewsCtrl->json_index(false, 6);

		return json_encode(
			array(
				'pics'	=> $pics,
				'news'	=> $news
			)
		);
	}

	// curl wrapper to connect to iNat Server
    function _curlWrap($url, $json, $action, $decodeResults = true)
    {
        // Shamelessly taken 1:1 (almost) from ZD developer reference :-)
        // 2nd inheritance from CampGO project
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
        curl_setopt($ch, CURLOPT_URL, $this->iNat['url'] . $url );
        // curl_setopt($ch, CURLOPT_USERPWD, $this->iNat['ZDUSER'] . "/token:" . $this->iNat['ZDAPIKEY']);
        switch($action){
            case "POST":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                break;
            case "GET":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                break;
            case "PUT":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                break;
        }

        // headers
        $headers[] = 'Content-type: application/json';

	    if( $url != '/oauth/token' ) {
		    // bearer token only for non login
		    // if we have the bearer token in the session, try to add it to the headers
		    $bearer_token = $this->Session->read( 'bearer' );

		    if ( ! empty( $bearer_token ) ) // got something, add to headers
		    {
			    $headers[] = "Authorization: Bearer $bearer_token";
		    }
	    }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $output = curl_exec($ch);

		//	Use This In case curl failed
		//if($output == false) trigger_error(curl_error($ch));



        curl_close($ch);
		if($decodeResults)
			$output = json_decode($output);
        return $output;
    }

}
