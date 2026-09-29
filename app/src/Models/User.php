<?php

namespace Models;

use Interface\User as UserInterface;

class User implements UserInterface 
{
    public int $id;

    public string $username;

    public string $email;

    public function __construct() {}
}
