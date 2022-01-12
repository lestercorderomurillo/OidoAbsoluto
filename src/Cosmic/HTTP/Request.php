<?php

namespace Cosmic\HTTP;

use Cosmic\Utilities\Text;

/**
 * Representation of an incoming, client-side request.
 *
 * Per the HTTP specification, this class includes properties for
 * each of the following:
 *
 * - Protocol version
 * - HTTP method
 * - URI
 * - Headers
 * - Message body
 *
 * Requests are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 */
class Request
{

    /**
     * @var string $protocol The HTTP protocol. Accepts only "http" or "https".
     */
    protected string $protocol;

    /**
     * @var string $protocolVersion The HTTP protocol version.
     */
    protected string $protocolVersion;

    /**
     * @var int $statusCode The HTTP status code.
     */
    protected int $statusCode;

    /**
     * @var string $action The full URI retrieved from the server.
     */
    protected string $uri;

    /**
     * @var string $action The parsed action from the URI.
     */
    protected string $action;

    /**
     * @var string $method The HTTP used for the request. Can either "get" or "post".
     */
    protected string $method;

    /**
     * @var array $headers Contains al HTTP Headers for this request object.
     */
    protected array $headers;

    /**
     * @var string $body The HTML body content. By default, it's "200 OK".
     */
    protected string $body;

    /**
     * @var array $formData The request formData. Originally was retrieved from $_GET or $_POST.
     */
    protected array $formData;

    /**
     * @var string $username The AuthenticationMiddleware username.
     */
    protected string $username;

    /**
     * @var string $password The AuthenticationMiddleware password.
     */
    protected string $password;

    /**
     * HTTP Codes. For further information, see https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     */
    const CODES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy AuthenticationMiddleware Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network AuthenticationMiddleware Required',
    ];
    /**
     * Constructor.
     * Creates a new empty request object. (200 OK by default)
     */
    public function __construct()
    {
        $this->statusCode = http_response_code();
        $this->body = __EMPTY__;
        $this->headers = [];
        $this->protocolVersion = "1.1";
    }

    /**
     * Intercept the new incoming request from the client.
     * This method will build the client request from the PHP variables.
     * 
     * @return Request 
     */
    public static function intercept(): Request
    {
        $request = new Request();

        $request->protocol = isset($_SERVER["HTTPS"]) ? "https" : "http";
        $request->uri = $_SERVER["REQUEST_URI"];

        /*if(str_ends_with($request->uri, "/")){
            $request->uri = substr($request->uri, 0, -1);
        }*/

        $request->action = explode("?", $request->uri, 2)[0];

        if(str_ends_with($request->action, "/")){
            $request->action = substr($request->action, 0, -1);
        }

        if($request->action == __EMPTY__){
            $request->action = "/";
        }

        $request->headers = array_change_key_case(getallheaders(), CASE_LOWER);

        $request->username = Text::sanitizeString(safe($_SERVER["PHP_AUTH_USER"], ""));
        $request->password = Text::sanitizeString(safe($_SERVER["PHP_AUTH_PW"], ""));

        $request->formData = [];
        $request->method = strtolower($_SERVER['REQUEST_METHOD']);

        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){

            $input = file_get_contents('php://input');
            
            if($input !== false){
                $request->formData = json_decode($input, true);
            }

        }else{

            if ($request->method == "get") {
                foreach ($_GET as $parameter => $value) {
                    $request->formData[$parameter] = Text::sanitizeString($value);
                }
            } else if ($request->method == "post") {
                foreach ($_POST as $parameter => $value) {
                    $request->formData[$parameter] = Text::sanitizeString($value);
                }
            }

        }

        

        return $request;
    }

    /**
     * Return the used protocol for this request. Can be "http" or "https".
     * 
     * @return string The HTTP protocol.
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    /**
     * Return the used protocol version for this request.
     *  
     * @return string The protocol version.
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * Return the stored status code for this request.
     *  
     * @return int The HTTP status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Return the body content of this request.
     *  
     * @return string The HTML body representation.
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Return the uri content of this request.
     *  
     * @return string The URI of this request.
     */
    public function getURI(): string
    {
        return $this->uri;
    }

    /**
     * Return the method used to execute this request. Can be "get" or "post".
     *  
     * @return string The HTTP method.
     */
    public function getMethod(): string
    {
        return strtolower($this->method);
    }

    /**
     * Return the action path used on this request.
     *  
     * @return string The URL action path.
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Return the user agent used on this request.
     *  
     * @return string The user agent used on this request.
     */
    public function getUserAgent(): string
    {
        return $this->getHeader("user-agent");
    }

    /**
     * Checks if this request contains the specified header.
     * 
     * @param string $headerName The name of the header to search.
     *  
     * @return bool True if the header is found, false otherwise.
     */
    public function hasHeader(string $headerName): bool
    {
        return isset($this->headers[strtolower($headerName)]);
    }

    /**
     * Returns the value stored in a header by its name.
     * 
     * @param string $headerName The name of the header to search.
     *  
     * @return string The value of the header.
     */
    public function getHeader(string $headerName): string
    {
        return $this->headers[strtolower($headerName)];
    }

    /**
     * Return all headers stored in this request object.
     *  
     * @return array A dictionary containing the headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Returns the form data associated with this request. 
     * The original data was retrieved from $_GET or $_POST.
     *  
     * @return array A dictionary containing the form data.
     */
    public function getFormData(): array
    {
        return $this->formData;
    }

    /**
     * Return the complete URL requested without filters.
     *  
     * @return string The URL path.
     */
    public function getFullPath(): string
    {
        return $this->protocol . "://" . $_SERVER["HTTP_HOST"] . $this->uri;
    }

    /**
     * Return the username used for AuthenticationMiddleware in this request.
     *  
     * @return string The username.
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Return the password used for AuthenticationMiddleware in this request.
     *  
     * @return string The password.
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Return the host used for this request.
     *  
     * @return string The host.
     */
    public function getHost()
    {
        return $this->getHeader("host");
    }
}
