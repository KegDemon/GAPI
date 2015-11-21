<?php

namespace Core\Models;

class ModelProfiles 
{   
    public function getProfiles($accounts)
    {
        $accounts = explode(',', $this->accounts);
        foreach ( $accounts as $account ) {
            $this->profileBatch->add( $this->service->management_profiles->listManagementProfiles($account, '~all'), $account );
        }
        
        $profiles = $this->profileBatch->execute();
        
        return $profiles;
    }
}