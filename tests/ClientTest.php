<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

include __DIR__ . "/../app/HttpClient.php";

final class ClientTest extends TestCase
{
    protected $domain = "https://dev-local.api-capstonegroup.com";

    public function testHeaders()
    {
        $client = new GenerCodeClient\HttpClient($this->domain);
        try {
            $res = $client->get("/v4/core/check-user");
            $this->assertSame($res["u"], "public");
        } catch(\Exception $e) {
            echo $e->getMessage();
            $response = $e->getResponse();
            echo $response->getBody()->getContents();
        }
    }


    public function testBase()
    {
        $client = new GenerCodeClient\HttpClient($this->domain);
        $client->base = "/v4";
        try {
            $res = $client->get("/core/check-user");
            $this->assertSame($res["u"], "public");
        } catch(\Exception $e) {
            //echo $e->getMessage();
            $response = $e->getResponse();
            echo $response->getBody()->getContents();
        }
    }
}