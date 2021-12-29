<?php

namespace App\Models;

use Cosmic\Core\Bootstrap\Model;

class UserInfo extends Model
{
    //const table = "users_info";

    public string $firstName;
    public string $lastName;
    public string $country;
    public string $birthDay;
    public string $phone;
    public string $gender;
}
