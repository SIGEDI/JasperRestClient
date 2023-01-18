<?php

namespace Jaspersoft\Tool;

use Jaspersoft\Exception\RESTRequestException;

class RESTRequest
{
    protected $url;
    protected $verb;
    protected $request_body;
    protected $request_length;
    protected $username;
    protected $password;
    protected $accept_type;
    protected $content_type;
    protected $response_body;
    protected $response_info;
    protected $file_to_upload = [];
    protected $headers;
    protected $curl_timeout;
    private $response_headers;

    public function __construct($url = null, $verb = 'GET', $request_body = null)
    {
        $this->url = $url;
        $this->verb = $verb;
        $this->request_body = $request_body;
        $this->request_length = 0;
        $this->username = null;
        $this->password = null;
        $this->accept_type = null;
        $this->content_type = 'application/json';
        $this->response_body = null;
        $this->response_info = null;
        $this->file_to_upload = [];
        $this->curl_timeout = 30;

        if ($this->request_body !== null) {
            $this->buildPostBody();
        }
    }

    /** This function will convert an indexed array of headers into an associative array where the key matches
     * the key of the headers, and the value matches the value of the header.
     *
     * This is useful to access headers by name that may be returned in the response from makeRequest.
     *
     * @param $array array Indexed header array returned by makeRequest
     *
     * @return array
     */
    public $errorCode;

    public static function splitHeaderArray($array): array
    {
        $result = [];
        foreach ($array as $value) {
            $pair = explode(':', $value, 2);
            if (count($pair) > 1) {
                $result[$pair[0]] = ltrim($pair[1]);
            } else {
                $result[] = $value;
            }
        }

        return $result;
    }

    public function flush(): void
    {
        $this->request_body = null;
        $this->request_length = 0;
        $this->verb = 'GET';
        $this->response_body = null;
        $this->response_info = null;
        $this->content_type = 'application/json';
        $this->accept_type = 'application/json';
        $this->file_to_upload = null;
        $this->headers = null;
    }

    public function execute(): void
    {
        $ch = curl_init();
        $this->setAuth($ch);
        $this->setTimeout($ch);
        try {
            switch (mb_strtoupper($this->verb)) {
                case 'GET':
                    $this->executeGet($ch);
                    break;
                case 'POST':
                    $this->executePost($ch);
                    break;
                case 'PUT':
                    $this->executePut($ch);
                    break;
                case 'DELETE':
                    $this->executeDelete($ch);
                    break;
                case 'PUT_MP':
                    $this->verb = 'PUT';
                    $this->executePutMultipart($ch);
                    break;
                case 'POST_MP':
                    $this->verb = 'POST';
                    $this->executePostMultipart($ch);
                    break;
                case 'POST_BIN':
                    $this->verb = 'POST';
                    $this->executeBinarySend($ch);
                    break;
                case 'PUT_BIN':
                    $this->verb = 'PUT';
                    $this->executeBinarySend($ch);
                    break;
                default:
                    throw new \InvalidArgumentException('Current verb ('.$this->verb.') is an invalid REST verb.');
            }
        } catch (\InvalidArgumentException|\Exception $e) {
            curl_close($ch);
            throw $e;
        }
    }

    public function buildPostBody($data = null): void
    {
        $data = ($data !== null) ? $data : $this->request_body;
        $this->request_body = $data;
    }

    protected function executeGet($ch): void
    {
        $this->doExecute($ch);
    }

    protected function executePost($ch): void
    {
        if (!is_string($this->request_body)) {
            $this->buildPostBody();
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->request_body);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        $this->doExecute($ch);
    }

    protected function executeBinarySend($ch): void
    {
        $post = $this->request_body;

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->verb);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

        $this->response_body = curl_exec($ch);
        $this->response_info = curl_getinfo($ch);

        curl_close($ch);
    }

    // Set verb to PUT_MP to use this function
    protected function executePutMultipart($ch): void
    {
        $post = $this->request_body;

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $this->response_body = curl_exec($ch);
        $this->response_info = curl_getinfo($ch);

        curl_close($ch);
    }

    // Set verb to POST_MP to use this function
    protected function executePostMultipart($ch): void
    {
        $post = $this->request_body;

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $this->response_body = curl_exec($ch);
        $this->response_info = curl_getinfo($ch);

        curl_close($ch);
    }

    protected function executePut($ch): void
    {
        if (!is_string($this->request_body)) {
            $this->buildPostBody();
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->request_body);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

        $this->doExecute($ch);
    }

    protected function executeDelete($ch): void
    {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        $this->doExecute($ch);
    }

    protected function doExecute(&$curlHandle): void
    {
        $this->setCurlOpts($curlHandle);
        $response = curl_exec($curlHandle);
        $this->response_info = curl_getinfo($curlHandle);

        $response = preg_replace("/^(?:HTTP\/1.1 100.*?\\r\\n\\r\\n)+/ms", '', $response);

        //  100-continue chunks are returned on multipart communications
        $headerBlock = mb_strstr($response, "\r\n\r\n", true);

        // strstr returns the matched characters and following characters, but we want to discard of "\r\n\r\n", so
        // we delete the first 4 bytes of the returned string.
        $this->response_body = mb_substr(mb_strstr($response, "\r\n\r\n"), 4);
        // headers are always separated by \n until the end of the header block which is separated by \r\n\r\n.
        $this->response_headers = explode("\r\n", $headerBlock);

        curl_close($curlHandle);
    }

    protected function setCurlOpts(&$curlHandle): void
    {
        curl_setopt($curlHandle, CURLOPT_URL, $this->url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_COOKIEFILE, '/dev/null');
        curl_setopt($curlHandle, CURLOPT_HEADER, true);

        if (!empty($this->content_type)) {
            $this->headers[] = 'Content-Type: '.$this->content_type;
        }
        if (!empty($this->accept_type)) {
            $this->headers[] = 'Accept: '.$this->accept_type;
        }
        if (!empty($this->headers)) {
            curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $this->headers);
        }
    }

    protected function setAuth(&$curlHandle): void
    {
        if ($this->username !== null && $this->password !== null) {
            curl_setopt($curlHandle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curlHandle, CURLOPT_USERPWD, $this->username.':'.$this->password);
        }
    }

    protected function setTimeout(&$curlHandle): void
    {
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, $this->curl_timeout);
    }

    public function defineTimeout($seconds): void
    {
        $this->curl_timeout = $seconds;
    }

    public function getFileToUpload(): array
    {
        return $this->file_to_upload;
    }

    public function setFileToUpload($filepath): void
    {
        $this->file_to_upload = $filepath;
    }

    /**
     * @return null
     */
    public function getAcceptType()
    {
        return $this->accept_type;
    }

    public function setAcceptType($accept_type): void
    {
        $this->accept_type = $accept_type;
    }

    public function getContentType(): string
    {
        return $this->content_type;
    }

    public function setContentType($content_type): void
    {
        $this->content_type = $content_type;
    }

    /**
     * @return null
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @return null
     */
    public function getResponseBody()
    {
        return $this->response_body;
    }

    /**
     * @return null
     */
    public function getResponseInfo()
    {
        return $this->response_info;
    }

    /**
     * @return mixed|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url): void
    {
        $this->url = $url;
    }

    /**
     * @return null
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @return mixed|string
     */
    public function getVerb()
    {
        return $this->verb;
    }

    public function setVerb($verb): void
    {
        $this->verb = $verb;
    }

    /**
     * @throws RESTRequestException
     */
    public function handleError($statusCode, $expectedCodes, $responseBody)
    {
        if (!empty($responseBody)) {
            $errorData = json_decode($responseBody);
            $exception = new RESTRequestException(
                (empty($errorData->message)) ? RESTRequestException::UNEXPECTED_CODE_MSG : $errorData->message
            );
            $exception->expectedStatusCodes = $expectedCodes;
            $exception->statusCode = $statusCode;
            if (!empty($errorData->errorCode)) {
                $exception->errorCode = $errorData->errorCode;
            }
            if (!empty($errorData->parameters)) {
                $exception->parameters = $errorData->parameters;
            }
        } else {
            $exception = new RESTRequestException(RESTRequestException::UNEXPECTED_CODE_MSG);
            $exception->expectedStatusCodes = $expectedCodes;
            $exception->statusCode = $statusCode;
        }
        throw $exception;
    }

    /**
     * @throws RESTRequestException
     */
    public function makeRequest(
        $url,
        $expectedCodes = [200],
        $verb = null,
        $reqBody = null,
        $returnData = false,
        $contentType = 'application/json',
        $acceptType = 'application/json',
        $headers = []
    ): array
    {
        $this->performReset($url, $verb, $reqBody);
        $info = $this->getResponseInfo();
        $statusCode = $info['http_code'];
        $body = $this->getResponseBody();

        $headers = $this->response_headers;

        // An exception is thrown here if the expected code does not match the status code in the response
        if (!in_array($statusCode, $expectedCodes, true)) {
            $this->handleError($statusCode, $expectedCodes, $body);
        }

        return compact('body', 'statusCode', 'headers');
    }

    /**
     * @throws RESTRequestException
     *
     * @return true|null
     */
    public function prepAndSend(
        $url,
        $expectedCodes = [200],
        $verb = null,
        $reqBody = null,
        $returnData = false,
        $contentType = 'application/json',
        $acceptType = 'application/json',
        $headers = []
    ) {
        $this->performReset($url, $verb, $reqBody);
        $statusCode = $this->getResponseInfo();
        $responseBody = $this->getResponseBody();
        $statusCode = $statusCode['http_code'];

        if (!in_array($statusCode, $expectedCodes, true)) {
            $this->handleError($statusCode, $expectedCodes, $responseBody);
        }

        if ($returnData) {
            return $this->getResponseBody();
        }

        return true;
    }

    /**
     * This function creates a multipart/form-data request and sends it to the server.
     * this function should only be used when a file is to be sent with a request (PUT/POST).
     *
     * @param string      $url          - URL to send request to
     * @param int|string  $expectedCode - HTTP Status Code you expect to receive on success
     * @param string      $verb         - HTTP Verb to send with request
     * @param string|null $reqBody      - The body of the request if necessary
     * @param array|null  $file         - An array with the URI string representing the image, and the filepath to the image. (i.e: array('/images/JRLogo', '/home/user/jasper.jpg') )
     * @param bool        $returnData   - whether you wish to receive the data returned by the server or not
     *
     * @throws \Exception
     *
     * @return array - Returns an array with the response info and the response body, since the server sends a 100 request, it is hard to validate the success of the request
     */
    public function multipartRequestSend(
        string $url,
        $expectedCode = 200,
        string $verb = 'PUT_MP',
        string $reqBody = null,
        array $file = null,
        bool $returnData = false
    ): array {
        $this->performReset($url, $verb, $reqBody);
        $response = $this->getResponseInfo();
        $responseBody = $this->getResponseBody();
        $statusCode = $response['http_code'];

        return [$statusCode, $responseBody];
    }

    /**
     * @throws RESTRequestException
     */
    public function sendBinary($url, $expectedCodes, $body, $contentType, $contentDisposition, $contentDescription, $verb = 'POST')
    {
        $this->flush();
        $this->setUrl($url);
        $this->setVerb($verb.'_BIN');
        $this->buildPostBody($body);
        $this->setContentType($contentType);
        $this->headers = [
            'Content-Type: '.$contentType,
            'Content-Disposition: '.$contentDisposition,
            'Content-Description: '.$contentDescription,
            'Accept: application/json',
        ];

        $this->execute();

        $statusCode = $this->getResponseInfo();
        $responseBody = $this->getResponseBody();
        $statusCode = $statusCode['http_code'];

        if (!in_array($statusCode, $expectedCodes, true)) {
            $this->handleError($statusCode, $expectedCodes, $responseBody);
        }

        return $this->getResponseBody();
    }

    private function performReset($url, $verb, $reqBody): void
    {
        $this->flush();
        $this->setUrl($url);
        if ($verb !== null) {
            $this->setVerb($verb);
        }
        if ($reqBody !== null) {
            $this->buildPostBody($reqBody);
        }
        if (!empty($contentType)) {
            $this->setContentType($contentType);
        }
        if (!empty($acceptType)) {
            $this->setAcceptType($acceptType);
        }
        if (!empty($headers)) {
            $this->headers = $headers;
        }

        $this->execute();
    }
}
