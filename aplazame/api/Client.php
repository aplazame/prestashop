<?php

class Aplazame_Client
{
    /**
     * @var string
     */
    private $apiBaseUri;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var bool
     */
    private $sandbox;

    public function __construct($apiBaseUri, $accessToken, $sandbox)
    {
        $this->apiBaseUri = $apiBaseUri;
        $this->accessToken = $accessToken;
        $this->sandbox = $sandbox;
    }

    public function callToRest($method, $path, array $values = null)
    {
        $url = $this->apiBaseUri . $path;
        $headers = array(
            'Authorization: Bearer ' . $this->accessToken,
            'Accept: application/vnd.aplazame.' . ($this->sandbox ? 'sandbox.' : '') . 'v1+json',
        );

        $versions = array(
            'PHP/' . PHP_VERSION,
            'Prestashop/' . _PS_VERSION_,
            'AplazamePrestashop/' . Aplazame::VERSION,
        );
        $headers[] = 'User-Agent: ' . implode(', ', $versions);

        if ($values) {
            $headers[] = 'Content-type: application/json';
            $values = json_encode($values);
        }

        $result = $this->doCurlRequest($method, $url, $headers, $values);
        $result['is_error'] = ($result['code'] >= 400);
        $result['payload'] = json_decode($result['payload'], true);

        return $result;
    }

    protected function doCurlRequest($method, $url, $headers, $values)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($values) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $values);
        }

        $responseBody = curl_exec($ch);

        if (false === $responseBody) {
            $message = curl_error($ch);
            $code = curl_errno($ch);

            curl_close($ch);

            throw new RuntimeException($message, $code);
        }

        $result = array(
            'payload' => $responseBody,
            'code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        );

        curl_close($ch);

        return $result;
    }
}
