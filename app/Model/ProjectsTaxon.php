<?php
App::uses('AppModel', 'Model');

class ProjectsTaxon extends AppModel {

    
    var $name = 'ProjectsTaxon';
    
    //The Associations below have been created with all possible keys, those that are not needed can be removed

     public $belongsTo = array(
        'Taxon' => array(
            'className' => 'Taxon',
            'foreignKey' => 'taxon_id',
            'dependent' => true
        ),
        'Project' => array(
            'className' => 'Project',
            'foreignKey' => 'project_id',
            'dependent' => true
        )
    );
    
   
}
