<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\HTTP\Server;

use Cosmic\HTTP\Request;
use Cosmic\Core\Interfaces\FactoryInterface;

/**
 * The extended version of the request class. Simple HTTP response object.
 * This class introduces mutability, so that developers can easily modify the output request.
 * 
 * Only responses can be modified and sent to the server. Request are read-only.
 */
class Response extends Request implements FactoryInterface
{
    /**
     * Create a new response object form the given request object. 
     * 
     * @param Request $dataSource The request to use as the template.
     * @return Response The converted response object.
     * @inheritdoc
     */
    public static function from($dataSource)
    {
        $response = new Response();

        if(__EXPERIMENTAL__){

            $response->setValues($dataSource->getRuntimeValues());
            
        }else{

            $response->protocol = $dataSource->getProtocol();
            $response->protocolVersion = $dataSource->getProtocolVersion();
            $response->statusCode = $dataSource->getStatusCode();
            $response->uri = $dataSource->getURI();
            $response->action = $dataSource->getAction();
            $response->method = $dataSource->getMethod();
            $response->headers = $dataSource->getHeaders();
            $response->body = $dataSource->getBody();
            $response->formData = $dataSource->getFormData();
            $response->username = $dataSource->getUsername();
            $response->password = $dataSource->getPassword();
        }

        return $response;
    }

    /**
     * Add a new header with the given value to the request. 
     * 
     * @param string $headerName The name of the header to add.
     * @param string $value The value of the header to add.
     *  
     * @return void 
     */
    public function addHeader(string $headerName, string $value): void
    {
        $this->headers[strtolower($headerName)][] = $value;
    }

    /**
     * Set the body content for this outgoing request.
     *  
     * @return void 
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * Set the status code for this outgoing request.
     * 
     * @param int $statusCode The HTTP status code. 
     * See http://www.w3.org/ for further details.
     *  
     * @return void 
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * Set the protocol for this outgoing request.
     * 
     * @param string $protocol Can be either "http" or "https" only.
     * 
     * @return void 
     */
    public function setProtocol(string $protocol): void
    {
        $this->protocol = $protocol;
    }

    /**
     * Set the protocol version for this outgoing request.
     * 
     * @param string $protocolVersion The version of the HTTP protocol.
     * 
     * @return void 
     */
    public function setProtocolVersion(string $protocolVersion): void
    {
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * Sends the HTTP response to the current client.
     * This will include all the headers previously set.
     * 
     * @return void
     */
    public function send(): void
    {
        if (!headers_sent()) {

            header("HTTP/$this->protocolVersion: $this->statusCode" . Request::CODES[$this->statusCode]);

            foreach ($this->headers as $key => $values) {
                foreach ($values as $value) {
                    header("$key: $value", false);
                }
            }

            if ($this->getBody() != __EMPTY__) {
                echo($this->getBody());
            }
        }
    }
}
