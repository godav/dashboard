<?php
App::uses('AppController', 'Controller');

class ObsController extends AppController {

    //  public $uses = null;

    function json_comment(){

        $params = $this->request->data;

        $res = $this->_curlWrap('/comments.json', json_encode($params), 'POST');

        return json_encode($res);
    }

    function json_identification(){

        $params = $this->request->data;

        $res = $this->_curlWrap('/identifications.json', json_encode($params), 'POST');

        return json_encode($res);

    }
    
    function json_del_identification($id = null){

        $res = $this->_curlWrap('/identifications/'. $id .'.json', null, 'DELETE');

        return json_encode($res);
    }

    //  Delete Observation
    function json_delete($id = null){

        $res = $this->_curlWrap('/observations/'. $id .'.json', null, 'DELETE');

        return json_encode($res);

    }


    function json_add(){

        /**
         * Todo:
         *  - Different call for Images
         *  - Different call for Projects
         */
        $results = array(
            'success'           => true,
            'data'              => null,    //  This Will Store data to send
            'initResponse'      => null,    //  Response from the first call, The creation of the ob
            'projectsResponse'  => null,    //  Response from the add projects call
            'imagesResponse'    => null,    //  Response from the add Images call
        );

        $defaults = array(
           'time_zone' => 'Jerusalem'
        );
        
        $requestData = $this->request->data;

        $results['data'] = array_merge($defaults, $requestData);

        $results['initResponse'] = $this->_curlWrap('/observations.json', json_encode($results['data']), 'POST');

        //print_r($res);



        // process Files - Upload files to our server for temporary use, Will be deleted after upload to INAT.
        if( !empty($_FILES) )
        {
            // allowed mime / ext
            $allowed = array(
                'image/png'     => 'png',
                'image/gif'     => 'gif',
                'image/jpeg'    => 'jpg',
            );
            foreach($_FILES['file']['name'] as $key => $file){
                //pr($_FILES['file']['name'][$key]);

                if( !empty($allowed[$_FILES['file']['type'][$key]]) )
                {   // Good MIME
                    $name = $_FILES['file']['name'][$key];
                    move_uploaded_file($_FILES['file']['tmp_name'][$key], WWW_ROOT . 'files'. DS .'temp' . DS . $name);

                }   //ToDo: Handle un allowed error

            }
        }

        return json_encode($results);
    }

    // OG = opengraph 
    function og_view($o_id = null)
    {
        //  pr($o_id);
        $meta = array();

        $r = $this->_curlWrap("/observations/{$o_id}.json?locale=iw", null , 'GET');

        //  Set Item Title
        $meta['og:title'] = "תצפית: ";
        if(isset($r->taxon)) $meta['og:title'] .= addslashes($r->taxon->default_name->name);

        $meta['og:title'] .= " | " . $r->species_guess;

        //  Images
        if($r->observation_photos_count > 0) $meta['og:image'] = $r->observation_photos[0]->photo->large_url;

        //  Description
        $meta['og:description'] = '';
        if(isset($r->description))  $meta['og:description'] = addcslashes($r->description) . " | ";
        $meta['og:description'] .= 'תצפיטבע - קהילה מנטרת טבע בגולן.';

        $this->set('meta', $meta);

    }


    function stats_index()
    {

         // count user in project
         $usersp = $this->Ob->query(
                 "SELECT DISTINCT project_id as id, COUNT(user_id) as users_count "
                 ."FROM projects_users as projects "		
                 ."GROUP BY project_id");
         
          // count taxon in project
          $taxonsp = $this->Ob->query(
                 "SELECT DISTINCT project_id as id, COUNT(taxon_id) as taxons_count "
                 ."FROM projects_taxons as projects "		
                 ."GROUP BY project_id");
          
         // count observations in project
          $observationsp = $this->Ob->query(
                 "SELECT projects.id, projects.slug, projects.icon_url, COUNT(observations_projects.observation_id) as observations_count "
                 ."FROM projects left join observations_projects on projects.id=observations_projects.project_id "		
                 ."GROUP BY projects.id "
                 ."ORDER BY observations_count");
         
         $projects = array();
          
         foreach ($observationsp as $pro ) {
            $projects[$pro['projects']['id']] = array(
                                    'id' => $pro['projects']['id'],
                                    'slug' => $pro['projects']['slug'],
                                    'icon_url' => $pro['projects']['icon_url'],
                                    'observations_count' => $pro['0']['observations_count']
                          );        
                }
                
         foreach ($taxonsp as $pro ) {
             if (!empty($pro['0']['taxons_count']))
                $projects[$pro['projects']['id']]['taxons_count'] = $pro['0']['taxons_count'];
             else 
                  $projects[$pro['projects']['id']]['taxons_count'] = 0;
         }  
         
         foreach ($usersp as $pro ) {
             
            $projects[$pro['projects']['id']]['users_count'] = $pro['0']['users_count'];  
         }
         
         $pro = array();
         foreach ($projects as $p) {
             $pro[] = $p;
         }
             
         
        $this->set('projects',$pro);
        
        
//        
//        $project = 4527;
//        //$mainTaxa = $this->request->data('pill');
//
//         $results = $this->Ob->query("select iconic_name as taxon, taxons.id as taxon_id , taxons.preferred_common_name as name , taxons.small_photo as photo , "
//        ."observations.id as observation_id, observations.observed_on,observations.latitude,observations.longitude "
//        ."from taxons left join observations on observations.taxon_id=taxons.id left join observations_projects on observations.id=observations_projects.observation_id "
//        ."where observations_projects.project_id={$project} "
//        ."group by taxon, taxons.id, observations.id, observations.observed_on "     
//        ."order by taxon, taxons.id, observations.id, observations.observed_on ");
//               
//        // arrange the results in the formation as : iconic_name -> taxon -> observation
//        $dataObs = array();
//        $dataObs['observations'] = [];
//        $data=Array();
//        foreach ($results as $result) { 
//   
//            if (!isset($data[$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['Observations']))
//              $data[$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['Observations']=[];
//
//            $obs=array(
//               'id' => $result['observations']['observation_id'],
//               'observed_on' => $result['observations']['observed_on'], 
//               'latitude' => $result['observations']['latitude'], 
//               'longitude' => $result['observations']['longitude']
//            );
//            
//            array_push($dataObs['observations'],$obs);
//            
//            array_push($data[$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['Observations'],$obs['id']);
//
//            $data[$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['id'] = $result['taxons']['taxon_id'];
//            $data[$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['name'] = $result['taxons']['name'];
//            $data[$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['photo'] = $result['taxons']['photo'];         
//        }
//         
//        $this->set('observations', $dataObs['observations']);
//        
//         //build all the observations of the project by iconic and order and group by date  
//        $resultsOT = $this->Ob->query("select iconic_name as taxon, "
//        ."observations.observed_on, count(observations.id) as obsCount "
//        ."from taxons left join observations on observations.taxon_id=taxons.id left join observations_projects on observations.id=observations_projects.observation_id "
//        ."where observations_projects.project_id={$project} "
//        ."group by taxon, observations.observed_on "     
//        ."order by taxon, observations.observed_on "); 
//        
//         $dataOT = []; 
//         foreach ($resultsOT as $resultOT) { 
//             if (!isset($dataOT[$resultOT['taxons']['taxon']]))
//               $dataOT[$resultOT['taxons']['taxon']]=[];  
//
//           array_push($dataOT[$resultOT['taxons']['taxon']],array('date' => $resultOT['observations']['observed_on'],'obsCount' => $resultOT[0]['obsCount']));     
//          }
//              
//        $this->set('ObsByDateAndTaxon', $dataOT); 
//          
//        //build all the observations in project order and group by date  
//        $resultsPU = $this->Ob->query("select observations.observed_on, count(observations.id) as obsCount "
//        ."from taxons left join observations on observations.taxon_id=taxons.id left join observations_projects on observations.id=observations_projects.observation_id "
//        ."where observations_projects.project_id={$project} "
//        ."group by observations.observed_on "     
//        ."order by observations.observed_on "); 
//        
//         $dataOP = []; 
//         foreach ($resultsPU as $resultOU) { 
//           array_push($dataOP,array('date' => $resultOU['observations']['observed_on'],'obsCount' => $resultOU[0]['obsCount']));   
//          } 
//                        
//         $this->set('ObsByDate', $dataOP);  
//         
//        // create counts for user and taxons
//
//            foreach ($data as $iconicKey=>$iconic) 
//            {
//                $iconicObs = 0;
//                $arr = [];
//                foreach ($iconic['Taxons'] as $taxKey=>$tax)
//                {
//                    $count = count( $data[$iconicKey]['Taxons'][$taxKey]['Observations']);
//                    $iconicObs += $count;
//                    $data[$iconicKey]['Taxons'][$taxKey]['taxObs'] = $count;
//                    array_push($arr,$data[$iconicKey]['Taxons'][$taxKey]);
//                        
//                } 
//                
//                $data[$iconicKey]['Taxons'] = [];
//                $data[$iconicKey]['Taxons'] = $arr;
//                $count = count( $data[$iconicKey]['Taxons']);
//                $data[$iconicKey]['iconicTax'] = $count;
//                $data[$iconicKey]['iconicObs'] = $iconicObs;
//            }
//
//       
//       $this->set('taxas', $data);
//       
//       $dataObs['users'] = $this->Ob->query("SELECT users.id, users.login, users.name, users.icon, users.role, COUNT(observations.id) as sumObs, COUNT(distinct observations.taxon_id) as sumTaxon"
//                ." FROM users as users inner join projects_users on users.id=projects_users.user_id"
//                                    . " left join observations on observations.user_id=users.id"
//                ." WHERE projects_users.project_id={$project}"
//                ." GROUP BY users.id, users.login, users.name, users.icon, users.role"
//                ." ORDER BY sumObs DESC");
// 
//        $this->set('users',  $dataObs['users']);        
                       
    }

//    function json_obsdata(){
//
//        $users = $this->request->data('users');
//        $taxas = $this->request->data('taxas');
//        $dates = $this->request->data('date');
//        $projects = $this->request->data('projects');
//        $mainTaxa = $this->request->data('pill');
//
//        
//        $usersObs = array();
//
//        if(!empty($users) && is_array($users)){
//            
//            foreach ($users as $user){
//                $usersObs[$user] = $this->Ob->query("SELECT * FROM observations WHERE user_id = {$user}");
//            }
//            
//        }
//        pr($usersObs);
//        return json_encode($usersObs);
//    }
    
    function json_byproject(){

        $project = $this->request->data('project');
         $userFilter = $this->request->data('searchUser');
         $taxonFilter = $this->request->data('searchTaxon');
         $filter= "";
         if (!empty($userFilter))
         {
             $filter = "AND users.login LIKE '%$userFilter%' "; 
         }
         
         if (!empty($taxonFilter))
         {
             $filter = "AND taxons.preferred_common_name LIKE '%$taxonFilter%' "; 
         }         
         
         
         $results = $this->Ob->query("select iconic_name as taxon, taxons.id as taxon_id , taxons.preferred_common_name as name , taxons.small_photo as photo , "
        ."observations.id as observation_id, observations.observed_on,observations.latitude,observations.longitude ,users.icon, users.login "       
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations_projects.project_id={$project} {$filter}"
        ."group by taxon, taxons.id, observations.id, observations.observed_on "     
        ."order by taxon, taxons.id, observations.id, observations.observed_on ");
               
        // arrange the results in the formation as : iconic_name -> taxon -> observation
        $dataObs = array();
        $dataObs['observations'] = [];
        $data=Array();
        foreach ($results as $result) { 
   
            if (!isset($data[$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['Observations']))
              $data[$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['Observations']=[];

            $obs=array(
               'id' => $result['observations']['observation_id'],
               'observed_on' => $result['observations']['observed_on'], 
               'latitude' => $result['observations']['latitude'], 
               'longitude' => $result['observations']['longitude'],
               'user_photo' =>  $result['users']['icon'],
               'user_name' =>  $result['users']['login'],
               'taxon_photo' => $result['taxons']['photo'],
               'taxon_name' => $result['taxons']['name'],
               'icon_name' => $result['taxons']['taxon']
            );
            
            array_push($dataObs['observations'],$obs);
            
            array_push($data[$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['Observations'],$obs['id']);

            $data[$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['id'] = $result['taxons']['taxon_id'];
            $data[$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['name'] = $result['taxons']['name'];
            $data[$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['photo'] = $result['taxons']['photo'];         
        }
         
         //build all the observations of the project by iconic and order and group by date  
        $resultsOT = $this->Ob->query("select iconic_name as taxon, "
        ."observations.observed_on, count(observations.id) as obsCount "
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations.observed_on IS NOT NULL and observations_projects.project_id={$project} {$filter}"
        ."group by taxon, observations.observed_on "     
        ."order by taxon, observations.observed_on "); 
        
         $dataOT = []; 
         foreach ($resultsOT as $resultOT) { 
             if (!isset($dataOT[$resultOT['taxons']['taxon']]))
               $dataOT[$resultOT['taxons']['taxon']]=[];  

           array_push($dataOT[$resultOT['taxons']['taxon']],array('date' => $resultOT['observations']['observed_on'],'obsCount' => $resultOT[0]['obsCount']));     
          }
       
         $dataObs['ObsByDateAndTaxon'] = $dataOT;                   
          
        //build all the observations in project order and group by date  
        $resultsPU = $this->Ob->query("select observations.observed_on, count(observations.id) as obsCount "
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations.observed_on IS NOT NULL and observations_projects.project_id={$project} {$filter}"
        ."group by observations.observed_on "     
        ."order by observations.observed_on "); 
        
         $dataOP = []; 
         foreach ($resultsPU as $resultOU) { 
           array_push($dataOP,array('date' => $resultOU['observations']['observed_on'],'obsCount' => $resultOU[0]['obsCount']));   
          } 
                  
         $dataObs['ObsByDate'] = $dataOP;       
        
        // create counts for user and taxons

            foreach ($data as $iconicKey=>$iconic) 
            {
                $iconicObs = 0;
                $arr = [];
                foreach ($iconic['Taxons'] as $taxKey=>$tax)
                {
                    $count = count( $data[$iconicKey]['Taxons'][$taxKey]['Observations']);
                    $iconicObs += $count;
                    $data[$iconicKey]['Taxons'][$taxKey]['taxObs'] = $count;
                    array_push($arr,$data[$iconicKey]['Taxons'][$taxKey]);
                        
                } 
                
                $data[$iconicKey]['Taxons'] = [];
                $data[$iconicKey]['Taxons'] = $arr;
                $count = count( $data[$iconicKey]['Taxons']);
                $data[$iconicKey]['iconicTax'] = $count;
                $data[$iconicKey]['iconicObs'] = $iconicObs;
            }

       $dataObs['groups'] = $data;

       $dataObs['users'] = $this->Ob->query("SELECT users.id, users.login, users.name, users.icon, users.role, COUNT(observations.id) as sumObs, COUNT(distinct observations.taxon_id) as sumTaxon"
                ." from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id"
                ." where observations_projects.project_id={$project} {$filter}"
                ."GROUP BY users.id, users.login, users.name, users.icon, users.role"
                ." ORDER BY sumObs DESC");
                
       $dataObs['taxons'] = $this->Ob->query("SELECT taxons.id as id , taxons.preferred_common_name as name , taxons.small_photo as photo, COUNT(observations.id) as sumObs"
        ." from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id"
        ." where observations_projects.project_id={$project} {$filter}"
        ."GROUP BY id"
        ." ORDER BY sumObs DESC");
                                 
        return json_encode($dataObs);

    }

    function json_byprojectIcon(){

         $project = $this->request->data('project');
         $pill = $this->request->data('pill');
         $userFilter = $this->request->data('searchUser');
         $taxonFilter = $this->request->data('searchTaxon');
         $filter= "";
         if (!empty($userFilter))
         {
             $filter = "AND users.login LIKE '%$userFilter%' "; 
         }
         
         if (!empty($taxonFilter))
         {
             $filter = "AND taxons.preferred_common_name LIKE '%$taxonFilter%' "; 
         }         
         
         $pillStr = "taxons.iconic_name='$pill' AND";
         
         $results = $this->Ob->query("select iconic_name as taxon, taxons.id as taxon_id , taxons.preferred_common_name as name , taxons.small_photo as photo , "
        ."observations.id as observation_id, observations.observed_on,observations.latitude,observations.longitude ,users.icon, users.login "       
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where {$pillStr} observations_projects.project_id={$project} {$filter}"
        ."group by taxons.id, observations.id, observations.observed_on "     
        ."order by taxons.id, observations.id, observations.observed_on ");
               
        // arrange the results in the formation as : iconic_name -> taxon -> observation
        $dataObs = array();
        $dataObs['observations'] = [];
        $data=Array();
        foreach ($results as $result) { 
   
            if (!isset($data[$result['taxons']['taxon_id']]['Observations']))
              $data[$result['taxons']['taxon_id']]['Observations']=[];

            $obs=array(
               'id' => $result['observations']['observation_id'],
               'observed_on' => $result['observations']['observed_on'], 
               'latitude' => $result['observations']['latitude'], 
               'longitude' => $result['observations']['longitude'],
               'user_photo' =>  $result['users']['icon'],
               'user_name' =>  $result['users']['login'],
               'taxon_photo' => $result['taxons']['photo'],
               'taxon_name' => $result['taxons']['name'],
               'icon_name' => $result['taxons']['taxon']
            );
            
            array_push($dataObs['observations'],$obs);
            
            array_push($data[$result['taxons']['taxon_id']]['Observations'],$obs['id']);

            $data[$result['taxons']['taxon_id']]['id'] = $result['taxons']['taxon_id'];
            $data[$result['taxons']['taxon_id']]['name'] = $result['taxons']['name'];
            $data[$result['taxons']['taxon_id']]['photo'] = $result['taxons']['photo'];         
        }                
          
        //build all the observations in project order and group by date  
        $resultsPU = $this->Ob->query("select observations.observed_on, count(observations.id) as obsCount "
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations.observed_on IS NOT NULL and {$pillStr} observations_projects.project_id={$project} {$filter}"
        ."group by observations.observed_on "     
        ."order by observations.observed_on "); 
        
         $dataOP = []; 
         foreach ($resultsPU as $resultOU) { 
           array_push($dataOP,array('date' => $resultOU['observations']['observed_on'],'obsCount' => $resultOU[0]['obsCount']));   
          } 
                  
         $dataObs['ObsByDate'] = $dataOP;       
        
        // create counts for user and taxons
 
        foreach ($data as $taxKey=>$tax)
        {
            $count = count( $data[$taxKey]['Observations']);              
            $data[$taxKey]['taxObs'] = $count;                                     
        } 

       $dataObs['obsByTaxons'] = $data;

       $dataObs['users'] = $this->Ob->query("SELECT users.id, users.login, users.name, users.icon, users.role, COUNT(observations.id) as sumObs, COUNT(distinct observations.taxon_id) as sumTaxon"
                ." from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id"
                ." where {$pillStr} observations_projects.project_id={$project} {$filter}"
                ."GROUP BY users.id, users.login, users.name, users.icon, users.role"
                ." ORDER BY sumObs DESC");
        
            
                
        $dataObs['taxons'] = $this->Ob->query("SELECT taxons.id as id , taxons.preferred_common_name as name , taxons.small_photo as photo, COUNT(observations.id) as sumObs"
                ." from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id"
                ." where {$pillStr} observations_projects.project_id={$project} {$filter}"
                ."GROUP BY id"
                ." ORDER BY sumObs DESC");
                                 
        return json_encode($dataObs);

    }

    function json_byuserIcon()
    {
         $users = $this->request->data('users');
         $project = $this->request->data('project');
         $pill = $this->request->data('pill');
              
         $pillStr = "taxons.iconic_name='$pill' AND";
         
         $results = $this->Ob->query("select iconic_name as taxon, taxons.id as taxon_id , taxons.preferred_common_name as name , taxons.small_photo as photo , "
        ."observations.id as observation_id, observations.observed_on,observations.latitude,observations.longitude ,users.icon, users.login "       
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where {$pillStr} observations_projects.project_id={$project} AND users.id={$users['0']} "
        ."group by taxons.id, observations.id, observations.observed_on "     
        ."order by taxons.id, observations.id, observations.observed_on ");
               
        // arrange the results in the formation as : iconic_name -> taxon -> observation
        $dataObs = array();
        $dataObs['observations'] = [];
        $data=Array();
        foreach ($results as $result) { 
   
            if (!isset($data[$result['taxons']['taxon_id']]['Observations']))
              $data[$result['taxons']['taxon_id']]['Observations']=[];

            $obs=array(
               'id' => $result['observations']['observation_id'],
               'observed_on' => $result['observations']['observed_on'], 
               'latitude' => $result['observations']['latitude'], 
               'longitude' => $result['observations']['longitude'],
               'user_photo' =>  $result['users']['icon'],
               'user_name' =>  $result['users']['login'],
               'taxon_photo' => $result['taxons']['photo'],
               'taxon_name' => $result['taxons']['name'],
               'icon_name' => $result['taxons']['taxon']
            );
            
            array_push($dataObs['observations'],$obs);
            
            array_push($data[$result['taxons']['taxon_id']]['Observations'],$obs['id']);

            $data[$result['taxons']['taxon_id']]['id'] = $result['taxons']['taxon_id'];
            $data[$result['taxons']['taxon_id']]['name'] = $result['taxons']['name'];
            $data[$result['taxons']['taxon_id']]['photo'] = $result['taxons']['photo'];         
        }                
          
        //build all the observations in project order and group by date  
        $resultsPU = $this->Ob->query("select observations.observed_on, count(observations.id) as obsCount "
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations.observed_on IS NOT NULL and {$pillStr} observations_projects.project_id={$project} AND users.id={$users['0']} "
        ."group by observations.observed_on "     
        ."order by observations.observed_on "); 
        
         $dataOP = []; 
         foreach ($resultsPU as $resultOU) { 
           array_push($dataOP,array('date' => $resultOU['observations']['observed_on'],'obsCount' => $resultOU[0]['obsCount']));   
          } 
                  
         $dataObs['ObsByDate'] = $dataOP;       
        
        // create counts for user and taxons
 
        foreach ($data as $taxKey=>$tax)
        {
            $count = count( $data[$taxKey]['Observations']);              
            $data[$taxKey]['taxObs'] = $count;                                     
        } 

       $dataObs['obsByTaxons'] = $data;

       $dataObs['users'] = $this->Ob->query("SELECT users.id, users.login, users.name, users.icon, users.role, COUNT(observations.id) as sumObs, COUNT(distinct observations.taxon_id) as sumTaxon"
                ." from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id"
                ." where {$pillStr} observations_projects.project_id={$project} "
                ."GROUP BY users.id, users.login, users.name, users.icon, users.role"
                ." ORDER BY sumObs DESC");
        
            
                
        $dataObs['taxons'] = $this->Ob->query("SELECT taxons.id as id , taxons.preferred_common_name as name , taxons.small_photo as photo, COUNT(observations.id) as sumObs"
                ." from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id"
                ." where {$pillStr} observations_projects.project_id={$project} AND users.id={$users['0']} "
                ."GROUP BY id"
                ." ORDER BY sumObs DESC");
                                 
        return json_encode($dataObs);
       
    }
    
     function json_byuserAll()
    {
         
        $project = $this->request->data('project'); 
        $users = $this->request->data('users');
        
      //  echo $users;
         $results = $this->Ob->query("select users.id as user_id,users.login,users.icon,iconic_name as taxon, taxons.id as taxon_id , taxons.preferred_common_name as name , taxons.small_photo as photo , "
        ."observations.id as observation_id, observations.observed_on,observations.latitude,observations.longitude "
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations_projects.project_id={$project} AND users.id={$users['0']} "
        ."group by users.id,taxon, taxons.id, observations.id "     
        ."order by users.id,taxon, taxons.id, observations.id ");
        
        // arrange the results in the formation as : user -> iconic_name -> taxon -> observation
        $data=Array();

         $dataObs = [];
        foreach ($results as $result) {   
            if (!isset($data[$result['users']['user_id']]['Iconics'][$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['Observations']))
               $data[$result['users']['user_id']]['Iconics'][$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['Observations']=[];

            $obs=array(
               'id' => $result['observations']['observation_id'],
               'observed_on' => $result['observations']['observed_on'], 
               'latitude' => $result['observations']['latitude'], 
               'longitude' => $result['observations']['longitude'],
               'user_photo' =>  $result['users']['icon'],
               'user_name' =>  $result['users']['login'],
               'taxon_photo' => $result['taxons']['photo'],
               'taxon_name' => $result['taxons']['name'],
               'icon_name' => $result['taxons']['taxon']
            );

            array_push($dataObs,$obs);
            
            array_push($data[$result['users']['user_id']]['Iconics'][$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['Observations'],$obs['id']);

            $data[$result['users']['user_id']]['id']= $result['users']['user_id'];
            $data[$result['users']['user_id']]['name'] = $result['users']['login']; 

            $data[$result['users']['user_id']]['Iconics'][$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['id'] = $result['taxons']['taxon_id'];
            $data[$result['users']['user_id']]['Iconics'][$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['name'] = $result['taxons']['name'];
            $data[$result['users']['user_id']]['Iconics'][$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['photo'] = $result['taxons']['photo'];         
        }
        
        //build all the observations of user by taxons and order and group by date  
        $resultsOT = $this->Ob->query("select users.id as user_id,iconic_name as taxon, "
        ."observations.observed_on, count(observations.id) as obsCount "
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations.observed_on IS NOT NULL and observations_projects.project_id={$project} AND users.id={$users['0']} "
        ."group by users.id,taxon, observations.observed_on "     
        ."order by users.id,taxon, observations.observed_on "); 
        
         $dataOT = []; 
         foreach ($resultsOT as $resultOT) { 
             if (!isset($dataOT[$resultOT['taxons']['taxon']]))
               $dataOT[$resultOT['taxons']['taxon']]=[];  

           array_push($dataOT[$resultOT['taxons']['taxon']],array('date' => $resultOT['observations']['observed_on'],'obsCount' => $resultOT[0]['obsCount']));     
          }
        
        //build all the observations of user order and group by by date  
        $resultsOU = $this->Ob->query("select users.id as user_id, "
        ."observations.observed_on, count(observations.id) as obsCount "
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations.observed_on IS NOT NULL and observations_projects.project_id={$project} AND users.id={$users['0']} "
        ."group by users.id, observations.observed_on "     
        ."order by users.id, observations.observed_on "); 
        
         $dataOU = []; 
         foreach ($resultsOU as $resultOU) { 
           array_push($dataOU,array('date' => $resultOU['observations']['observed_on'],'obsCount' => $resultOU[0]['obsCount']));   
          } 
          
          
        // create counts for user and taxons
        foreach ($data as $userKey=>$user)
        {
            $userObs = 0;
            $userTax = 0;
            
            foreach ($user['Iconics'] as $iconicKey=>$iconic) 
            {
                $iconicObs = 0;
                $arr = [];
                foreach ($iconic['Taxons'] as $taxKey=>$tax)
                {
                    $count = count( $data[$userKey]['Iconics'][$iconicKey]['Taxons'][$taxKey]['Observations']);
                    $iconicObs += $count;
                    $data[$userKey]['Iconics'][$iconicKey]['Taxons'][$taxKey]['taxObs'] = $count;
                    array_push($arr,$data[$userKey]['Iconics'][$iconicKey]['Taxons'][$taxKey]);
//                    $fruit = array_shift($data[$userKey]['Iconics'][$iconicKey][$taxKey]);
//                    array_push($data[$userKey]['Iconics'][$iconicKey]['Taxons'],$fruit);                          
                } 
                
                $data[$userKey]['Iconics'][$iconicKey]['Taxons'] = [];
                $data[$userKey]['Iconics'][$iconicKey]['Taxons'] = $arr;

                $count = count( $data[$userKey]['Iconics'][$iconicKey]['Taxons']);
                $data[$userKey]['Iconics'][$iconicKey]['iconicTax'] = $count;
                $data[$userKey]['Iconics'][$iconicKey]['iconicObs'] = $iconicObs;
                $userObs += $iconicObs;
                $userTax += $count;
            }
            $data[$userKey]['userObs'] = $userObs;
            $data[$userKey]['userTax'] = $userTax;
            $data[$userKey]['observations'] = $dataObs;
            $data[$userKey]['ObsByDate'] = $dataOU;
            $data[$userKey]['ObsByDateAndTaxon'] = $dataOT;
        }
        
           $data[$users['0']]['users'] = $this->Ob->query("SELECT users.id, users.login, users.name, users.icon, users.role, COUNT(observations.id) as sumObs, COUNT(distinct observations.taxon_id) as sumTaxon"
                ." from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id"
                ." WHERE observations_projects.project_id={$project}"
                ." GROUP BY users.id, users.login, users.name, users.icon, users.role"
                ." ORDER BY sumObs DESC");
                
            $data[$users['0']]['taxons'] = $this->Ob->query("SELECT taxons.id as id , taxons.preferred_common_name as name , taxons.small_photo as photo, COUNT(observations.id) as sumObs"
                ." from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id"
                ." where observations_projects.project_id={$project} AND users.id={$users['0']} "
                ."GROUP BY id"
                ." ORDER BY sumObs DESC");    
                
      
        return json_encode($data[$users['0']]);
    }
    
     function json_bytaxon()
    {
         
        $project = $this->request->data('project');
       
        $taxons = $this->request->data('taxas');
        $pill = $this->request->data('pill');
        if ($pill=='All')
             $pillStr = '';
         else 
             $pillStr = "taxons.iconic_name='$pill' AND";
        
        $dataObs = array();
        $dataObs['observations'] = [];
        
        $taxString ="";
                  
         foreach ($taxons as $tax) {
            $taxString = $taxString . 'taxons.id=' . $tax . ' OR ';
        }
        $taxNew = preg_replace('/ OR $/', '', $taxString);      

         $results = $this->Ob->query("select users.id as user_id,users.login, users.icon,iconic_name as taxon, taxons.id as taxon_id , taxons.preferred_common_name as name , taxons.small_photo as photo , "
        ."observations.id as observation_id, observations.observed_on,observations.latitude,observations.longitude "
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations_projects.project_id={$project} AND ({$taxNew}) "
        ."group by taxons.id, users.id, observations.id "     
        ."order by taxons.id, users.id, observations.id ");
                          
        $data=Array();
        
        foreach ($results as $result) {   
            if (!isset($data[$result['taxons']['taxon_id']]['Users'][$result['users']['user_id']]['Observations']))
               $data[$result['taxons']['taxon_id']]['Users'][$result['users']['user_id']]['Observations']=[];

            $obs = array(
               'id' => $result['observations']['observation_id'],
               'observed_on' => $result['observations']['observed_on'], 
               'latitude' => $result['observations']['latitude'], 
               'longitude' => $result['observations']['longitude'],
               'user_photo' =>  $result['users']['icon'],
               'user_name' =>  $result['users']['login'],
               'taxon_photo' => $result['taxons']['photo'],
               'taxon_name' => $result['taxons']['name'],
               'icon_name' => $result['taxons']['taxon']
            );
            
            array_push(  $dataObs['observations'],$obs); 
            
            array_push( $data[$result['taxons']['taxon_id']]['Users'][$result['users']['user_id']]['Observations'],$obs['id']);
            
             if (!isset($data[$result['taxons']['taxon_id']]['Observations']))
                 $data[$result['taxons']['taxon_id']]['Observations']=[];
            array_push($data[$result['taxons']['taxon_id']]['Observations'],$obs);

            $data[$result['taxons']['taxon_id']]['Users'][$result['users']['user_id']]['id']= $result['users']['user_id'];
            $data[$result['taxons']['taxon_id']]['Users'][$result['users']['user_id']]['login']= $result['users']['login'];

            $data[$result['taxons']['taxon_id']]['id'] = $result['taxons']['taxon_id'];
            $data[$result['taxons']['taxon_id']]['name'] = $result['taxons']['name'];
            $data[$result['taxons']['taxon_id']]['photo'] = $result['taxons']['photo'];         
        }            
        
         //build all the observations of taxon by taxons and order and group by date  
        $resultsOT = $this->Ob->query("select taxons.id as taxon_id, "
        ."observations.observed_on, count(observations.id) as obsCount "
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations_projects.project_id={$project} AND ({$taxNew}) "
        ."group by taxons.id, observations.observed_on "     
        ."order by taxons.id, observations.observed_on "); 
              
         $dataOT = []; 
         foreach ($resultsOT as $resultOT) { 
             if (!isset($dataOT[$resultOT['taxons']['taxon_id']]))
               $dataOT[$resultOT['taxons']['taxon_id']]=[];  

           array_push($dataOT[$resultOT['taxons']['taxon_id']],array('date' => $resultOT['observations']['observed_on'],'obsCount' => $resultOT[0]['obsCount']));     
          }
                   
        //build all the observations of user order and group by by date  
        $resultsOU = $this->Ob->query("select taxons.id as taxon_id, users.id as user_id, "
        ."observations.observed_on, count(observations.id) as obsCount "
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations_projects.project_id={$project} AND ({$taxNew}) "
        ."group by taxons.id, users.id, observations.observed_on "     
        ."order by taxons.id, users.id, observations.observed_on "); 
        
         $dataOU = []; 
         foreach ($resultsOU as $resultOU) { 
            if (!isset($dataOU[$resultOU['taxons']['taxon_id']][$resultOU['users']['user_id']]))
               $dataOU[$resultOU['taxons']['taxon_id']][$resultOU['users']['user_id']]=[];  
             
             
           array_push($dataOU[$resultOU['taxons']['taxon_id']][$resultOU['users']['user_id']],array('date' => $resultOU['observations']['observed_on'],'obsCount' => $resultOU[0]['obsCount']));   
          } 
          
        // create counts for user and taxons
   
         foreach ($data as $taxonKey=>$taxon) 
            {
                $taxonObs = 0;
                $arr = [];
                foreach ($taxon['Users'] as $userKey=>$user)
                {
                    $count = count( $data[$taxonKey]['Users'][$userKey]['Observations']);
                    $taxonObs += $count;
                    $data[$taxonKey]['Users'][$userKey]['userObs'] = $count;
                    $data[$taxonKey]['Users'][$userKey]['ObsByDate'] = $dataOU[$taxonKey][$userKey];
                    array_push($arr,$data[$taxonKey]['Users'][$userKey]);                        
                } 
                 $data[$taxonKey]['taxonObs'] = $taxonObs;
                 $data[$taxonKey]['ObsByDateAndTaxon'] = $dataOT[$taxonKey];           
                              
                }
           $dataObs['obsByTaxons'] = $data;

           $dataObs['users'] = $this->Ob->query("SELECT users.id, users.login, users.name, users.icon, users.role, COUNT(observations.id) as sumObs, COUNT(distinct observations.taxon_id) as sumTaxon "
                 ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
                 ."where observations_projects.project_id={$project} AND ({$taxNew}) "
                 ."group by users.id "     
                 ."ORDER BY sumObs DESC");  
                 
            $dataObs['taxons'] = $this->Ob->query("SELECT taxons.id as id , taxons.preferred_common_name as name , taxons.small_photo as photo, COUNT(observations.id) as sumObs"
                ." from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id"
                ." where {$pillStr} observations_projects.project_id={$project} "
                ."GROUP BY id"
                ." ORDER BY sumObs DESC");
                
                
       return json_encode($dataObs);
    }
    
         function json_bytaxonanduser()
    {
         
        $project = $this->request->data('project');          
        $users = $this->request->data('users');        
        $taxons = $this->request->data('taxas');
        $pill = $this->request->data('pill');
        
        if ($pill == 'All')
             $pillStr = '';
         else 
             $pillStr = "taxons.iconic_name='$pill' AND ";
         
        $dataObs = array();
        $dataObs['observations'] = [];
        
        $taxString ="";
                  
         foreach ($taxons as $tax) {
            $taxString = $taxString . 'taxons.id=' . $tax . ' OR ';
        }
        $taxNew = preg_replace('/ OR $/', '', $taxString); 
        
      //  echo $users;
         $results = $this->Ob->query("select users.id as user_id,users.login,users.icon,iconic_name as taxon, taxons.id as taxon_id , taxons.preferred_common_name as name , taxons.small_photo as photo , "
        ."observations.id as observation_id, observations.observed_on,observations.latitude,observations.longitude "
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations_projects.project_id={$project} AND users.id={$users['0']} "
        ."group by users.id,taxon, taxons.id, observations.id "     
        ."order by users.id,taxon, taxons.id, observations.id ");
        
        // arrange the results in the formation as : user -> iconic_name -> taxon -> observation
        $data=Array();


        foreach ($results as $result) {   
            if (!isset($data['Iconics'][$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['Observations']))
               $data['Iconics'][$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['Observations']=[];

            $obs=array(
               'id' => $result['observations']['observation_id'],
               'observed_on' => $result['observations']['observed_on'], 
               'latitude' => $result['observations']['latitude'], 
               'longitude' => $result['observations']['longitude'],
               'user_photo' =>  $result['users']['icon'],
               'user_name' =>  $result['users']['login'],
               'taxon_photo' => $result['taxons']['photo'],
               'taxon_name' => $result['taxons']['name'],
               'icon_name' => $result['taxons']['taxon']
            );

             if (in_array($result['taxons']['taxon_id'], $taxons)){ 
                 array_push($dataObs['observations'],$obs);
             //    array_push($data['Iconics'][$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['Observations'],$obs);
            }
            
         //   array_push($dataObs['observations'],$obs);
            
        //    array_push($data['Iconics'][$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['Observations'],$obs);

            $data['Iconics'][$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['id'] = $result['taxons']['taxon_id'];
            $data['Iconics'][$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['name'] = $result['taxons']['name'];
            $data['Iconics'][$result['taxons']['taxon']]['Taxons'][$result['taxons']['taxon_id']]['photo'] = $result['taxons']['photo'];         
        }      
        
         //build all the observations of taxon by taxons and order and group by date  
        $resultsOT = $this->Ob->query("select taxons.id as taxon_id, taxons.preferred_common_name as name, "
        ."observations.observed_on, count(observations.id) as obsCount "
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations_projects.project_id={$project} AND ({$taxNew}) AND users.id={$users['0']} "
        ."group by taxons.id, observations.observed_on "     
        ."order by taxons.id, observations.observed_on "); 
              
         $dataOT = []; 
         foreach ($resultsOT as $resultOT) { 
             if (!isset($dataOT[$resultOT['taxons']['taxon_id']]['ObsByDateAndTaxon']))
               $dataOT[$resultOT['taxons']['taxon_id']]['ObsByDateAndTaxon']=[];  
             
              if (!isset($dataOT[$resultOT['taxons']['taxon_id']]['name']))
               $dataOT[$resultOT['taxons']['taxon_id']]['name']= $resultOT['taxons']['name'];  
            
              if (!isset($dataOT[$resultOT['taxons']['taxon_id']]['obsCount']))
                    $dataOT[$resultOT['taxons']['taxon_id']]['obsCount'] = 0; 
              $dataOT[$resultOT['taxons']['taxon_id']]['obsCount'] += $resultOT[0]['obsCount']; 
              
           array_push($dataOT[$resultOT['taxons']['taxon_id']]['ObsByDateAndTaxon'],array('date' => $resultOT['observations']['observed_on'],'obsCount' => $resultOT[0]['obsCount']));     
          }
                          
        // create counts for taxons            
            foreach ($data['Iconics'] as $iconicKey=>$iconic) 
            {              
                $arr = [];
                foreach ($iconic['Taxons'] as $taxKey=>$tax)
                {
                    $data['Iconics'][$iconicKey]['Taxons'][$taxKey]['taxObs'] = count( $data['Iconics'][$iconicKey]['Taxons'][$taxKey]['Observations']);
                    array_push($arr,$data['Iconics'][$iconicKey]['Taxons'][$taxKey]);                        
                } 
                
                $data['Iconics'][$iconicKey]['Taxons'] = [];
                $data['Iconics'][$iconicKey]['Taxons'] = $arr;
            }
      
            $dataObs['obsByTaxons'] = $data;
            
             $dataObs['obsByDate'] = $dataOT;
                     
            $dataObs['users'] = $this->Ob->query("SELECT users.id, users.login, users.name, users.icon, users.role, COUNT(observations.id) as sumObs, COUNT(distinct observations.taxon_id) as sumTaxon "
                 ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
                 ."where observations_projects.project_id={$project} AND ({$taxNew}) "
                 ."group by users.id "     
                 ."ORDER BY sumObs DESC"); 
                 
            $dataObs['taxons'] = $this->Ob->query("SELECT taxons.id as id , taxons.preferred_common_name as name , taxons.small_photo as photo, COUNT(observations.id) as sumObs"
                ." from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id"
                ." where {$pillStr}observations_projects.project_id={$project} AND users.id={$users['0']} "
                ."GROUP BY id"
                ." ORDER BY sumObs DESC");     
                
       return json_encode($dataObs);
    }
    
    function json_byseassions()
    {
         $yearsToShow = 4;                          // detarmine the number of years to compare on each seassion
        
         $users = $this->request->data('users');
         $project = $this->request->data('project');
         $pill = $this->request->data('pill');
         $taxons = $this->request->data('taxas');     
         
         if ($pill == 'All')
             $pillStr = '';
         else 
             $pillStr = "taxons.iconic_name='$pill' AND ";
         
         $taxNew ="";
         if (!empty($taxons))  {  
         $taxString ="";
         foreach ($taxons as $tax) {
            $taxString = $taxString . 'taxons.id=' . $tax . ' OR ';
            }
        
        $taxNew = ' AND (' . preg_replace('/ OR $/', '', $taxString) . ')'; 
         }
        
        $userStr = '';
        if (!empty($users))
            $userStr = ' AND users.id=' . $users['0'];
        
         $results = $this->Ob->query("select observations.observed_on, count(observations.id) as obsCount "    
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations.observed_on IS NOT NULL AND {$pillStr} observations_projects.project_id={$project}{$userStr}{$taxNew} "
        ."group by observations.observed_on "     
        ."order by observations.observed_on asc");
        
        $lYear = $this->Ob->query("select YEAR(observations.observed_on) as last "    
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations.observed_on IS NOT NULL AND {$pillStr} observations_projects.project_id={$project} {$userStr} {$taxNew} "   
        ."order by observations.observed_on desc limit 1");
         
        $lYear =  intval($lYear[0][0]['last']) ;
        
         $fYear = $this->Ob->query("select YEAR(observations.observed_on) as first "    
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations.observed_on IS NOT NULL AND {$pillStr} observations_projects.project_id={$project} {$userStr} {$taxNew} "   
        ."order by observations.observed_on asc limit 1");             
        
         $fYear = intval($fYear[0][0]['first']) ;
 
        if (($lYear-$yearsToShow) > $fYear)
            $fYear = $lYear-$yearsToShow;
        
        for ($i = $fYear; $i <= $lYear; $i++) {     
                $seassions['summer'][$i]['observation'] = [];
                $seassions['summer'][$i]['grouped'] = [];
                $seassions['spring'][$i]['observation'] = [];
                $seassions['spring'][$i]['grouped'] = [];
                $seassions['winter'][$i]['observation'] = [];
                $seassions['winter'][$i]['grouped'] = [];
                $seassions['autumn'][$i]['observation'] = [];
                $seassions['autumn'][$i]['grouped'] = [];                
        }        

        foreach ($results as $result) {              
             $checkDate = $result['observations']['observed_on'];   
             for ($i = $fYear; $i <= $lYear; $i++){            
                   if($this->__dateCheck(date('Y-m-d',strtotime(($i-1) . '-12-21')),date('Y-m-d',strtotime(($i-1) . '-12-31')),$checkDate))
                    {
                        $convertedDate = date('Y-m-d',strtotime('1979-' . date('m',strtotime($checkDate)) . '-' . date('d',strtotime($checkDate))));                    
                        array_push($seassions['winter'][$i]['grouped'],array(strtotime($convertedDate), intval($result[0]['obsCount'])));
                        break;
                    }else if ($this->__dateCheck(date('Y-m-d',strtotime($i . '-01-01')),date('Y-m-d',strtotime($i . '-03-20')),$checkDate))
                    {
                        $convertedDate = date('Y-m-d',strtotime('1980-' . date('m',strtotime($checkDate)) . '-' . date('d',strtotime($checkDate))));                     
                        array_push($seassions['winter'][$i]['grouped'],array(strtotime($convertedDate), intval($result[0]['obsCount'])));
                        break;
                    }else if($this->__dateCheck(date('Y-m-d',strtotime($i . '-09-23')),date('Y-m-d',strtotime($i . '-12-20')),$checkDate))
                    {
                        $convertedDate = date('Y-m-d',strtotime('1980-' . date('m',strtotime($checkDate)) . '-' . date('d',strtotime($checkDate))));                      
                        array_push($seassions['autumn'][$i]['grouped'],array(strtotime($convertedDate), intval($result[0]['obsCount'])));
                        break;
                    }else if($this->__dateCheck(date('Y-m-d',strtotime($i . '-03-21')),date('Y-m-d',strtotime($i . '-06-20')),$checkDate))
                    {
                        $convertedDate = date('Y-m-d',strtotime('1980-' . date('m',strtotime($checkDate)) . '-' . date('d',strtotime($checkDate))));                      
                        array_push($seassions['spring'][$i]['grouped'],array(strtotime($convertedDate), intval($result[0]['obsCount'])));
                        break;
                    }else if($this->__dateCheck(date('Y-m-d',strtotime($i . '-06-21')),date('Y-m-d',strtotime($i . '-09-22')),$checkDate))
                    {
                        $convertedDate = date('Y-m-d',strtotime('1980-' . date('m',strtotime($checkDate)) . '-' . date('d',strtotime($checkDate))));                     
                        array_push($seassions['summer'][$i]['grouped'],array(strtotime($convertedDate), intval($result[0]['obsCount'])));
                        break;
                    }
                }
            } 
        
        $obs = $this->Ob->query("select users.login, users.icon, iconic_name as taxon, taxons.preferred_common_name as name, taxons.small_photo as photo, "
        ."observations.id as observation_id, observations.observed_on, observations.latitude, observations.longitude  "    
        ."from taxons left join observations on observations.taxon_id=taxons.id left join users on users.id=observations.user_id left join observations_projects on observations.id=observations_projects.observation_id "
        ."where observations.observed_on IS NOT NULL AND {$pillStr} observations_projects.project_id={$project}{$userStr}{$taxNew} "    
        ."order by observations.observed_on asc");  
        
         foreach ($obs as $ob) { 
            
            $o=array(
               'id' => $ob['observations']['observation_id'],
               'observed_on' => $ob['observations']['observed_on'], 
               'latitude' => $ob['observations']['latitude'], 
               'longitude' => $ob['observations']['longitude'],
               'user_photo' =>  $ob['users']['icon'],
               'user_name' =>  $ob['users']['login'],
               'taxon_photo' => $ob['taxons']['photo'],
               'taxon_name' => $ob['taxons']['name'],
               'icon_name' => $ob['taxons']['taxon']
            ); 
             
             $checkDate = $ob['observations']['observed_on'];   
             for ($i = $fYear; $i <= $lYear; $i++){            
                   if($this->__dateCheck(date('Y-m-d',strtotime(($i-1) . '-12-21')),date('Y-m-d',strtotime(($i-1) . '-12-31')),$checkDate))
                    {
                      
                       array_push($seassions['winter'][$i]['observation'],$o);
                        break;
                    }else if ($this->__dateCheck(date('Y-m-d',strtotime($i . '-01-01')),date('Y-m-d',strtotime($i . '-03-20')),$checkDate))
                    {
                       
                        array_push($seassions['winter'][$i]['observation'],$o);
                        break;
                    }else if($this->__dateCheck(date('Y-m-d',strtotime($i . '-09-23')),date('Y-m-d',strtotime($i . '-12-20')),$checkDate))
                    {
                        
                        array_push($seassions['autumn'][$i]['observation'],$o);
                        break;
                    }else if($this->__dateCheck(date('Y-m-d',strtotime($i . '-03-21')),date('Y-m-d',strtotime($i . '-06-20')),$checkDate))
                    {
                        
                         array_push($seassions['spring'][$i]['observation'],$o);
                        break;
                    }else if($this->__dateCheck(date('Y-m-d',strtotime($i . '-06-21')),date('Y-m-d',strtotime($i . '-09-22')),$checkDate))
                    {
                      
                         array_push($seassions['summer'][$i]['observation'],$o);
                        break;
                    }
                } 
        }   

                                 
        return json_encode($seassions);
       
    }  
    
      public function __dateCheck($from,$to,$check) {
        if(($check <= $to && $check >= $from)) {
            return true;
        }
        return false;
    }
}
