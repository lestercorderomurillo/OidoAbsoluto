<?php

namespace App\Models;

use Cosmic\ORM\Bootstrap\Model;

class UserInfo extends Model
{
    public string $firstName;
    public string $lastName;
    public string $country;
    public string $birthDay;
    public string $phone;
    public string $gender;
}
