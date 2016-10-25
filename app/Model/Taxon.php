<?php
App::uses('AppModel', 'Model');

class Taxon extends AppModel {
    
     public $hasMany = array(
        'Observation' => array(
            'className' => 'Observation',
            'foreignKey' => 'taxon_id'
        ),
         'ProjectsTaxon' => array(
            'className' => 'ProjectsTaxon',
            'foreignKey' => 'taxon_id',
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