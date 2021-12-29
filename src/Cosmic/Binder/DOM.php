<?php

namespace Cosmic\Binder;

use Cosmic\HTTP\Request;
use Cosmic\HTTP\Server\Router;
use Cosmic\Binder\Exceptions\NotFoundComponentException;
use Cosmic\Bundle\Components\View;
use Cosmic\Core\Exceptions\NotFoundDependencyException;
use Cosmic\Utilities\Collection;
use Cosmic\Utilities\Text;

use function Cosmic\Core\Bootstrap\app;

/**
 * This class represents the cosmic Binder tree.
 */
class DOM
{
    /**
     * @var Callback[] $callbacks Holds all the callbacks as a list.
     */
    private array $callbacks;

    /**
     * @var Router $router The router that this DOM object uses to put entry points for server side rendering.
     */
    private Router $router;

    /**
     * @var Compiler $compiler The compiler that this DOM tree uses for rendering components.
     */
    private Compiler $compiler;

    /**
     * Constructor. Expected be resolved using dependency injection.
     * 
     * @param Compiler $compiler The compiler instance.
     * @param Router $router The router instance.
     * 
     * @return void
     */
    public function __construct(Compiler $compiler, Router $router)
    {
        $this->callbacks = [];
        $this->compiler = $compiler;
        $this->router = $router;
    }

    /**
     * Return the full class namespace from the DOM tag name.
     * Thanks to this, elements can later on create instances of this component.
     * 
     * @param string $tag The dom tag to get the namespace from.
     * 
     * @return string The full class name of the component.
     * @throws NotFoundComponentException On error.
     */
    public function getComponentClassName(string $tag): string
    {
        try {
            return app()->get(Component::getPublishNameFromTag($tag)); 
        } catch (NotFoundDependencyException $exception) {
            throw new NotFoundComponentException("Binder doesn't have the component \"$tag\" registered yet");
        }
    }

    /**
     * Register the component class in the container. When
     * 
     * @param string $className The component class to inject into the container.
     * 
     * @return void
     */
    public function registerComponent(string $className): void
    {
        app()->injectPrimitive(Component::getPublishName($className), $className);
    }

    /**
     * Add the given callback to the router to retrieve ajax-responses from the server when doing server side rendering.
     * 
     * @param Callback $callback The callback to be added.
     * 
     * @return void
     */
    public function registerCallback(Callback $callback): void
    {
        $compiler = $this->compiler;

        $this->router->get($callback->getEntryPointToken(),  function (Request $request) use ($callback, $compiler) {


            return "callback request - component: " . $callback->getComponentName() . " with action: " . $callback->getComponentAction();
            /*$componentName = $callback->getComponentName();

            $formData = $request->getFormData();
            
            // emm.. we need the props genius..
            $componentInstance = new View("test", "en");
            $componentInstance->setKey($formData['key']);
            $componentInstance->setValues($formData['state'], true);

            $element = new Element($componentInstance, )
            $compiler->compileElement()*/
        });
    }

    /**
     * Return the resulting compiled callbacks as one single javascript string that be exported 
     * later on in the build.js file. This method merge all callbacks from all components.
     *
     * @return string 
     */
    public function getClientSideCallbacks(): string
    {
        $bundle = [];
        foreach ($this->callbacks as $callback) {
            $bundle[] = $callback->getJavascriptFunction();
        }

        return implode("\n", $bundle);
    }

    /**
     * Return a collection of all files required to compile for components. 
     * 
     * @return File[] A collection of files. 
     */
    public function getSCSSFiles(): array
    {
        $all = app()->all();
        $files = [];

        foreach ($all as $key => $value){
            if(Text::startsWith($key, "Component@")){

                $value = $value->get();
                $files = Collection::mergeList(Component::getStyleFilesPath($value));
            }
        }

        return array_unique($files);
    }
}
