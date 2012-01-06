<?php

class CancelTransition extends WorkflowTransition {
    const sourceState = '*';
    const destState   = 'CancelledState';
    const actionName  = 'Cancel';
    
    const sortIndex = 10;

    public function getAllowedPermissionList(){
        return array('dept_approve', 'sig_auth_approve', 'register');
    }
    
    public function getActionName()
    {
        return self::actionName;
    }
    
    public function getSourceState(){
        return self::sourceState;
    }
    
    public function getDestState(){
        return self::destState;
    }
    
    public function getSortIndex(){
        return self::sortIndex;
    }
    
    public function getName(){
        return 'CancelTransition';
    }
}

?>