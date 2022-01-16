<?php

namespace App\ViewModels;

use Cosmic\ORM\Abstracts\Model;

class UserSummaryViewModel extends Model
{
    public string $token;
    public string $email;
    public string $role;
    public string $firstName;
    public string $lastName;
    public string $country;
    public string $birthDay;
    public string $phone;
    public string $gender;
    public int $tries;
}
