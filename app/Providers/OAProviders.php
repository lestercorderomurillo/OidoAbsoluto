<?php

namespace App\Providers;

use Cosmic\ORM\Common\ConnectionSeeder;
use Cosmic\Core\Abstracts\AutoProvider;
use Cosmic\ORM\Databases\SQL\SQLRepository;
use Cosmic\ORM\Driver\MySQLDriver;

class OAProviders extends AutoProvider
{
    public static function boot(): void
    {
    }

    public static function provide(): void
    {
        //app()->singleton(SQLRepository::class, [new MySQLDriver(), ConnectionSeeder::from("oa")]);
    }
}
