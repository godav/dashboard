<?php
App::uses('AppModel', 'Model');

class ObservationsProject extends AppModel {

    
    var $name = 'ObservationsProject';
    
    //The Associations below have been created with all possible keys, those that are not needed can be removed

     public $belongsTo = array(
        'Observation' => array(
            'className' => 'Observation',
            'foreignKey' => 'observation_id',
            'dependent' => true
        ),
        'Project' => array(
            'className' => 'Project',
            'foreignKey' => 'project_id',
            'dependent' => true
        )
    );
    
   
}
