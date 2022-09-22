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


   
    protected function refreshCookies() {
        $this->http->put("/core/switch-tokens");
    }

    protected function buildHeaders() {
        $headers=$this->custom_headers;
        if ($this->referer) {
            $headers["referer"] = $this->referer;
        }
        return $headers;
    }


    protected function checkStatus($r) {
        if ($r->getStatusCode() == 403) {
            $r = $this->http->put("/core/switch-tokens");
            if ($r->getStatusCode() != 200) {
                throw new \Exception("API failure for " . $url . ": " . $r->getStatusCode() . " " . $r->getReasonPhrase());
            }
            $r = $this->http->request($method, $url, $params);
            if ($r->getStatusCode() != 200) {
                throw new \Exception("API failure for " . $url . ": " . $r->getStatusCode() . " " . $r->getReasonPhrase());
            }
            throw new \Exception("API failure for " . $url . ": 403 Authorisation failed");
        } else if ($r->getStatusCode() == 401) {
            throw new \Exception("API failure for " . $url . ": 401 Authentication failed");
        } else if ($r->getStatusCode() != 200) {
            throw new \Exception("API failure for " . $url . ": " . $r->getStatusCode() . " " . $r->getReasonPhrase() . "\n" . $r->getBody()->getContents());
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
                    throw new \Exception("API JSON failure for " . $url . ": Maximum stack depth exceeded\n\n" . $body);
                break;
                case JSON_ERROR_CTRL_CHAR:
                    throw new \Exception("API JSON failure for " . $url . ": Unexpected control character found\n\n" . $body);
                break;
                case JSON_ERROR_SYNTAX:
                    throw new \Exception("API JSON failure for " . $url . ": Syntax error, malformed JSON\n\n" . $body);
                break;
            }
            }
            return $json;
        } else {
            return (string) $body;
        }
    }


    public function get($url, $data=null)
    {
        $params = ["headers"=>$this->buildHeaders()];
        if ($data) $params["query"]=$data;
        $params["headers"]["accept"] = 'application/json';
        $r = $this->http->request("GET", $this->base . $url, $params);
        $this->checkStatus($r);
        return $this->parseResponse($r);
    }


    public function post($url, array $data)
    {
        $params = ["headers"=>$this->buildHeaders()];
        $params["headers"]["accept"] = 'application/json';
        $params["form_params"]=$data;
        $r = $this->http->request("POST", $this->base . $url, $params);
        $this->checkStatus($r);
        return $this->parseResponse($r);
    }

    public function put($url, array $data)
    {
        $params = ["headers"=>$this->buildHeaders()];
        $params["headers"]["accept"] = 'application/json';
        $params["json"]=$data;
        $r = $this->http->request("PUT", $this->base . $url, $params);
        $this->checkStatus($r);
        return $this->parseResponse($r);
    }

    public function delete($url, array $data)
    {
        $params = ["headers"=>$this->buildHeaders()];
        $params["headers"]["accept"] = 'application/json';
        $params["json"]=$data;
        $r = $this->http->request("DELETE", $this->base . $url, $params);
        $this->checkStatus($r);
        return $this->parseResponse($r);
    }


    public function pushAsset($url, $blob) {
        $params = ["headers"=>$this->buildHeaders()];
        $params["headers"]["accept"] = 'application/json';
        $params["body"]=$blob;
        $r = $this->http->request("PATCH", $this->base . $url, $params);
        $this->checkStatus($r);
        return $this->parseResponse($r);
    }

    public function getAsset($url) {
        $params = ["headers"=>$this->buildHeaders()];
        $r = $this->http->request("GET", $this->base . $url, $params);
        $this->checkStatus($r);
        return $this->parseResponse($r);
    }
}
