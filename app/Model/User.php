<?php
App::uses('AppModel', 'Model');

class User extends AppModel {
      var $name = 'User';
      
      public $hasMany = array(
        'Observation' => array(
            'className' => 'Observation',
            'foreignKey' => 'user_id',
        ),
        'ProjectsUser' => array(
            'className' => 'ProjectsUser',
            'foreignKey' => 'user_id',
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