<?php
App::uses('AppController', 'Controller');

class ProjectsController extends AppController {

	 public $components = array('Session');

	// Admin Functions

	// Admin index
	public function control_index() 
	{
		$this->headerMenu['projects'] = 'active';
		$this->set('title_for_layout', 'ניהול פרוייקטים');
	}

	//  Add Project
	function control_add(){

		$this->layout = false;
		$data = $this->request->data;

		if(isset($data['id'])){
			$this->autoRender = false;
			$this->Project->create();
			if($this->Project->save($data)){
				return json_encode(array(
					'success'=> true,
					'data'	=> $data
					));
			}else {
				return json_encode(array('success'=> false));
			}
		}
	}

	//  Update Project
	function control_update(){

		$this->autoRender = false;
		$data = $this->request->data;

		if(isset($data['id'])){
			if($this->Project->save($data)){
				return json_encode(array('success'=> true));
			}else {
				return json_encode(array('success'=> false));
			}
		}

		return json_encode(array('success'=> false));
	}

	//  Delete A Project
	function control_del($id = null) {
		$this->autoRender = false;

		if($id)
			$this->Project->delete($id);

		return;
	}

	function sync(){

		$this->autoRender 	= false;
		$this->layout 		= 'ajax';

		$projects 			= $this->Project->find('all');

		$inatProjectUrl = "/projects/";
		$inatSpeciesCountUrl = "/observations/species_counts?&per_page=1&project_id=";

		$inatUrls = array(
			$this->iNat['url'],
			"http://api.inaturalist.org/v1"
		);

		//	Loop All saved projects
		foreach($projects as $project ){
			echo $project['Project']['slug'] . "<br>";
			$this->iNat['url'] = $inatUrls[0];

			$d =  $this->_curlWrap($inatProjectUrl . $project['Project']['slug'] . ".json", null,"GET", true);

			$saveData = array(
				'id'			        => $project['Project']['id'],
				'title' 		        => $d->title,
				'description' 	        => $d->description,
				'icon_url' 		        => $d->icon_url,
				'slug' 			        => $d->slug,
				'observations_count' 	=> $d->project_observations_count,
			);

			//	Get Project Members
			$this->iNat['url'] = $inatUrls[1];
			$members =  $this->_curlWrap($inatProjectUrl . $project['Project']['slug'] . "/members", null,"GET", true);

			if(isset($members->total_results))
				$saveData['members_count'] = $members->total_results;

			//	Get Taxa count
			$speciesCount = $this->_curlWrap($inatSpeciesCountUrl . $project['Project']['id'], null,"GET", true);
			if(isset($speciesCount->total_results))
				$saveData['taxa_count'] = $speciesCount->total_results;


			//	Saving Project data

			//$this->Project->id = $project['Project']['id'];
			$this->Project->save($saveData);
			$this->Project->clear();
		}

		//	Save All Project together
		//$t = $this->Project->saveMany($saveData);


		return;

	}

	// JSON API functions

	// projects index
	function json_index($encode = true)
	{
		$this->Project->recursive = -1;
		$proj = $this->Project->find('all');
		// put them all in an anon array
		$ret = array();
		foreach( $proj as $P )
			$ret[] = $P['Project'];
		// return json array of projects
		return $encode ? json_encode($ret) : $ret;
	}

	function og_view($p_id = null)
	{
		$meta = array();

		$r = $this->_curlWrap("/projects/{$p_id}.json?locale=iw", null , 'GET');

		//	pr($r);

		//  Set Item Title
		$meta['og:title'] = "פרוייקט: ";
		if(isset($r->title)) $meta['og:title'] .= $r->title;

		//  Images
		if($r->icon_url) $meta['og:image'] = $r->icon_url;

		//  Description
		$meta['og:description'] = '';
		if(isset($r->description))  $meta['og:description'] = addslashes($r->description) . " | ";
		$meta['og:description'] .= 'תצפיטבע - קהילה מנטרת טבע בגולן.';

		$this->set('meta', $meta);



	}
}
