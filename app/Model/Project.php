<?php
App::uses('AppModel', 'Model');

class Project extends AppModel {

    var $order = 'Project.title';    
    var $name = 'Project';
    var $displayField = 'name';
    
    //The Associations below have been created with all possible keys, those that are not needed can be removed

    var $hasMany = array(
        'ObservationsProject' => array(
            'className' => 'ObservationsProject',
            'foreignKey' => 'project_id',
            'dependent' => false,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        ),
        
        'ProjectsUser' => array(
            'className' => 'ProjectsUser',
            'foreignKey' => 'project_id',
            'dependent' => false,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        ),
         'ProjectsTaxon' => array(
            'className' => 'ProjectsTaxon',
            'foreignKey' => 'project_id',
            'dependent' => false,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        )
    );

}
