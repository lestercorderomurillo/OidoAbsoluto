<?php

namespace App\Models;

use Pipeline\Core\Boot\ModelBase;

class UserInfo extends ModelBase
{
    const table = "users_info";

    public string $first_name;
    public string $last_name;
    public string $country;
    public string $birth_day;
    public string $phone;
    public string $gender;
}
