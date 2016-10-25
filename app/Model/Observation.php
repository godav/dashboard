<?php
App::uses('AppModel', 'Model');

class Observation extends AppModel {
    
    var $name = 'Observation';
    var $displayField = 'name';    
      
     public $hasMany = array(
         'ObservationsProject' => array(
            'className' => 'ObservationsProject',
            'foreignKey' => 'observation_id',
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
    
        public $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',
            'dependent' => true
        ),
        'Taxon' => array(
            'className' => 'Taxon',
            'foreignKey' => 'taxon_id',
            'dependent' => true
        )
    );
        

}