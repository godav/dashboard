<?php
App::uses('AppModel', 'Model');

class ProjectsUser extends AppModel {

    
    var $name = 'ProjectsUser';
    
    //The Associations below have been created with all possible keys, those that are not needed can be removed

     public $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',
            'dependent' => true
        ),
        'Project' => array(
            'className' => 'Project',
            'foreignKey' => 'project_id',
            'dependent' => true
        )
    );
    
   
}
