<?php

namespace App\Base;

use App\User\User;

class CurrentUser {

    private $user = null;

    function getUser() {
        // get cached user object
        if (!is_null($this->user)) {
            return $this->user;
        }
        return null;
    }

    function setUser(User $user): void {
        $this->user = $user;
    }

}
