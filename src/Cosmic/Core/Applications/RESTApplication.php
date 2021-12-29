<?php

namespace Cosmic\Core\Applications;

use Cosmic\Core\Bootstrap\Application;

/**
 * This class represents a restful application.
 */
abstract class RESTApplication extends Application
{
    /**
     * @inheritdoc
     */
    protected function onConfiguration(): void
    {
    }

    /**
     * @inheritdoc
     */
    protected function onServicesInjection(): void
    {
    }

    /**
     * @inheritdoc
     */
    protected function onInitialization(): void
    {
        exit();
    }
}
