<?php

namespace GenerCodeClient;

class API {

    private $type;
    private $prefix;
    private $client;

    function __construct($client, $type = "slim", $prefix = "") {
        $this->client = $client;
        $this->type = $type;
        $this->prefix = $prefix;
    }


    function get($model, $args) {
        if ($this->type == "slim") {
            return $this->client->get($this->prefix . "/data/" . $model, $args);
        } else if ($this->type == "laravel") {
            return $this->client->get($this->prefix . "/" . $model);
        }
    }


    function post($model, $args) {
        if ($this->type == "slim") {
            return $this->client->post($this->prefix . "/data/" . $model, $args);
        } else if ($this->type == "laravel") {
            return $this->client->post($this->prefix . "/" . $model);
        }
    }

    
    function put($model, $id, $args) {
        if ($this->type == "slim") {
            $args["--id"] = $id;
            return $this->client->put($this->prefix . "/data/" . $model, $args);
        } else if ($this->type == "laravel") {
            return $this->client->put($this->prefix . "/" . $model . "/" . $id);
        }
    }


    function destroy($model, $id) {
        if ($this->type == "slim") {
            return $this->client->delete($this->prefix . "/data/" . $model, ["--id"=>$id]);
        } else if ($this->type == "laravel") {
            return $this->client->delete($this->prefix . "/" . $model . "/" . $id);
        }
    }


    function active($model, $id) {
        if ($this->type == "slim") {
            return $this->client->get($this->prefix . "/data/" . $model . "/active", ["--id"=>$id]);
        } else if ($this->type == "laravel") {
            return $this->client->get($this->prefix . "/" . $model . "/" . $id);
        }
    }

    
    function getReference($model, $field, $id) {
        if ($this->type == "slim") {
            return $this->client->get($this->prefix . "/reference/" . $model . "/" . $field . "/" . $id);
        } else if ($this->type == "laravel") {
            return $this->client->get($this->prefix . "/" . $model . "/" . $field . "/" . $id);
        }
    }


    function getAsset($model, $field, $id) {
        if ($this->type == "slim") {
            return $this->client->get($this->prefix . "/asset/" . $model . "/" . $field . "/" . $id);
        } else if ($this->type == "laravel") {
            return $this->client->get($this->prefix . "/" . $model . "/" . $field . "/" . $id);
        }
    }


    function queueStatus($dispatch_id) {
        if ($this->type == "slim") {
            return $this->client->get($this->prefix . "/dispatch/status/" . $id);
        } else if ($this->type == "laravel") {
            return $this->client->get($this->prefix . "/queue/status/" . $id);
        }
    }
}