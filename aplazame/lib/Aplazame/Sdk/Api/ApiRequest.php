<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2021 Aplazame
 * @license   see file: LICENSE
 */

class Aplazame_Sdk_Api_ApiRequest extends Aplazame_Sdk_Http_Request
{
    const SDK_VERSION = '0.2.2';
    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';
    const FORMAT_YAML = 'yaml';

    /**
     * @param string $accessToken
     *
     * @return string
     */
    public static function createAuthorizationHeader($accessToken)
    {
        return 'Bearer ' . $accessToken;
    }

    /**
     * @param bool $useSandbox
     * @param int $apiVersion
     * @param string $format
     *
     * @return string
     */
    public static function createAcceptHeader($useSandbox, $apiVersion, $format)
    {
        $header = 'application/vnd.aplazame';
        if ($useSandbox) {
            $header .= '.sandbox';
        }
        $header .= sprintf('.v%d+%s', $apiVersion, $format);

        return $header;
    }

    /**
     * @param bool $useSandbox
     * @param string $accessToken The Access Token of the request (Public API key or Private API key)
     * @param string $method The HTTP method of the request.
     * @param string $uri The URI of the request.
     * @param mixed $data The data of the request.
     */
    public function __construct(
        $useSandbox,
        $accessToken,
        $method,
        $uri,
        $data = null
    ) {
        /** @var Aplazame $aplazame */
        $aplazame = ModuleCore::getInstanceByName('aplazame');

        $headers = array(
            'Accept' => array(self::createAcceptHeader($useSandbox, 1, self::FORMAT_JSON)),
            'Authorization' => array(self::createAuthorizationHeader($accessToken)),
            'User-Agent' => array(
                'Sdk/' . self::SDK_VERSION,
                'PHP/' . PHP_VERSION,
                'Prestashop/' . _PS_VERSION_,
                'AplazamePrestashop/' . $aplazame->version,
            ),
            'Accept-Language' => array('es'),
        );

        if ($data && !is_string($data)) {
            $data = json_encode($data);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new DomainException(json_last_error_msg(), json_last_error());
            }
            $headers['Content-Type'] = array('application/json');
        }

        parent::__construct($method, $uri, $headers, $data);
    }
}
