<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\ORM\Abstracts;

use Cosmic\Traits\ValuesSetterTrait;
use Cosmic\Traits\ValuesGetterTrait;
use Cosmic\Utilities\Strings;

/**
 * This class represents a simple entity model. Developers should extend this class to make their own models.
 */
abstract class Model
{
    use ValuesSetterTrait;
    use ValuesGetterTrait;

    /**
     * @var array $id The entity id.
     */
    public array $id = [];

    /**
     * @var array $data The underlying stored data.
     */
    private array $data = [];

    /**
     * Constructor. By default, sets the Id to 0.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->id = 0;
    }

    /**
     * Gets the table name associated to this model, using the table constant.
     * If the constant is not present, returns the class name.
     * 
     * @return string The name of the table as in the database.
     */
    public function getTableName(): string
    {
        $const = $this->getConstant("table");

        if ($const === false) {
            return strtolower(Strings::getClassBaseName(static::class));
        }

        return $const;
    }
}
