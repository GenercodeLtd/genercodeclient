<?php

namespace GenerCodeClient;

class HttpClient {

    protected $http;
    protected $jar;
    protected $headers;
    protected $custom_headers=[];
    protected $referer;
    protected $base = "";

    function __construct($domain) {
        $this->jar = new \GuzzleHttp\Cookie\SessionCookieJar('PHPCOOKIES', true);
        $this->http = new \GuzzleHttp\Client(["base_uri"=>rtrim($domain, "/"), 'cookies' => $this->jar]);
    }

    function __set($name, $value) {
        if (property_exists($this, $name)) $this->$name = $value;
    }


    function __get($name) {
        return (property_exists($this, $name)) ? $this->$name : null;
    }

    function regHeader($header, $value) {
        $this->custom_headers[$header] = $value;
    }


    protected function buildHeaders() {
        $headers=$this->custom_headers;
        if ($this->referer) {
            $headers["referer"] = $this->referer;
        }

        return $headers;
    }


    protected function checkStatus($url, $r) {
        $content_type = $r->getHeader("Content-Type");
        $code = $r->getStatusCode();
        if ($code == 401 OR $code == 403) {
            throw new \Exception("API failure for " . $url . ": " . $code ." Authentication failed");
        } else if ($code != 200) {
            echo "\nError is " . $r->getBody()->getContents();
            if (strpos($content_type[0], "json") !== false) {
                throw new ApiErrorException($url, $code, $r->getBody()->getContents());
            } else {
                throw new \Exception("API failure for " . $url . ": " . $r->getStatusCode() . " " . $r->getReasonPhrase() . $r->getBody()->getContents());
            }
        }
    }


    protected function parseResponse($r) {
    
        $body = $r->getBody();
       
        $content_type = $r->getHeader("Content-Type");

        if (strpos($content_type[0], "json") !== false) {
            $json = json_decode($body, true);
            if ($json === null) {
                switch (json_last_error()) {
                case JSON_ERROR_DEPTH:
                    throw new \Exception("API JSON failure: Maximum stack depth exceeded\n\n" . $body);
                break;
                case JSON_ERROR_CTRL_CHAR:
                    throw new \Exception("API JSON failure: Unexpected control character found\n\n" . $body);
                break;
                case JSON_ERROR_SYNTAX:
                    throw new \Exception("API JSON failure: Syntax error, malformed JSON\n\n" . $body);
                break;
            }
            }
            return $json;
        } else {
            return $body->getContents();
        }
    }


    public function get($url, $data=null)
    {
        $params = ["headers"=>$this->buildHeaders(), 'http_errors' => false];
        if ($data) $params["query"]=$data;
        $params["headers"]["accept"] = 'application/json';
        $r = $this->http->request("GET", $this->base . $url, $params);
        $this->checkStatus($url, $r);
        return $this->parseResponse($r);
    }


    public function post($url, array $data, bool $is_multipart = false)
    {
        $params = ["headers"=>$this->buildHeaders(), 'http_errors' => false];
        $params["headers"]["accept"] = 'application/json';
        if ($is_multipart) {
            $marr = [];
            foreach($data as $key=>$val) {
                if (is_array($val)) {
                    if (isset($val["contents"])) {
                        $val["name"] = $key;
                        $marr[] = $val;
                    } else {
                        $marr[] = ["name"=>$key, "contents"=>base64_encode(json_encode($val))];
                    }
                } else {
                    $marr[] = ["name"=>$key, "contents"=>$val];
                }
            }
            $params["multipart"] = $marr;
        } else {
            $params["form_params"]=$data;
        }
        $r = $this->http->request("POST", $this->base . $url, $params);
        $this->checkStatus($url, $r);
        return $this->parseResponse($r);
        
    }


    public function sendXML($url, $xml) {
        $headers = $this->buildHeaders();
        $headers["Content-Type"] = "text/xml; charset=UTF8";
        $params = ["headers"=>$headers, "http_errors" => false, "body"=>$xml];
        $r = $this->http->request("POST", $url, $params);
       
    }


    public function put($url, array $data)
    {
        $params = ["headers"=>$this->buildHeaders(), 'http_errors' => false];
        $params["headers"]["accept"] = 'application/json';
        $params["json"]=$data;
       
        $r = $this->http->request("PUT", $this->base . $url, $params);
        $this->checkStatus($url, $r);
        return $this->parseResponse($r);
    }


    public function delete($url, array $data)
    {
        $params = ["headers"=>$this->buildHeaders(), 'http_errors' => false];
        $params["headers"]["accept"] = 'application/json';
        $params["json"]=$data;
        $r = $this->http->request("DELETE", $this->base . $url, $params);
        $this->checkStatus($url, $r);
        return $this->parseResponse($r);
    }


    public function pushAsset($url, $field_name, $file, $file_name = null) {
        $postFile = $this->createFile($file, $file_name);
        $params = ["headers"=>$this->buildHeaders(), 'http_errors' => false];
    
        //$params["headers"]["accept"] = 'application/json';
        //$params["form_params"]=["name"=>$name];
        $postFile["name"] = $field_name;
        $params["multipart"] = [$postFile];
        $r = $this->http->request("POST", $this->base . $url, $params);
        $this->checkStatus($url, $r);
        return $this->parseResponse($r);
    }


    public function getAsset($url) {
        $params = ["headers"=>$this->buildHeaders(), 'http_errors' => false];
        $r = $this->http->request("GET", $this->base . $url, $params);
        $this->checkStatus($url, $r);
        $body = $r->getBody();
        return $body->getContents();
    }


    public function createFile($src, $filename = null) {
        $filename ??= basename($src);
        return [
            "contents" => \GuzzleHttp\Psr7\Utils::tryFopen($src, 'r'),
            "filename" => $filename
        ];
    }
}
