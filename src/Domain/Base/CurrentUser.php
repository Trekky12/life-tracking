<?php

namespace App\Domain\Base;

use App\Domain\User\User;

class CurrentUser {

    private $user = null;
    private $token = null;

    function getUser() {
        // get cached user object
        if (!is_null($this->user)) {
            return $this->user;
        }
        return null;
    }

    function setUser(?User $user): void {
        $this->user = $user;
    }
    
    public function setToken($token): void {
        $this->token = $token;
    }
    
    public function getToken(){
        return $this->token;
    }

}
