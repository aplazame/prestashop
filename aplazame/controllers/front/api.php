<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2016 Aplazame
 * @license   see file: LICENSE
 */

/**
 * @property Aplazame module
 */
class AplazameApiModuleFrontController extends ModuleFrontController
{
    public static function forbidden()
    {
        return array(
            'status_code' => 403,
            'payload' => array(
                'status' => 403,
                'type' => 'FORBIDDEN',
            ),
        );
    }

    public static function not_found()
    {
        return array(
            'status_code' => 404,
            'payload' => array(
                'status' => 404,
                'type' => 'NOT_FOUND',
            ),
        );
    }

    public static function client_error($detail)
    {
        return array(
            'status_code' => 400,
            'payload' => array(
                'status' => 400,
                'type' => 'CLIENT_ERROR',
                'detail' => $detail,
            ),
        );
    }

    public static function collection($page, $page_size, array $elements)
    {
        return array(
            'status_code' => 200,
            'payload' => array(
                'query' => array(
                    'page' => $page,
                    'page_size' => $page_size,
                ),
                'elements' => $elements,
            ),
        );
    }

    public function postProcess()
    {
        $path = Tools::getValue('path', '');
        $pathArguments = Tools::jsonDecode(Tools::getValue('path_arguments', '[]'), true);
        $queryArguments = Tools::jsonDecode(Tools::getValue('query_arguments', '[]'), true);
        $payload = Tools::jsonDecode(file_get_contents('php://input'), true);

        $response = $this->route($path, $pathArguments, $queryArguments, $payload);

        http_response_code($response['status_code']);
        header('Content-Type: application/json');

        exit(Tools::jsonEncode($response['payload']));
    }

    /**
     * @param string $path
     * @param array $pathArguments
     * @param array $queryArguments
     * @param null|array $payload
     *
     * @return array
     */
    public function route($path, array $pathArguments, array $queryArguments, $payload)
    {
        if (!$this->verify_authentication()) {
            return self::forbidden();
        }

        switch ($path) {
            case '/article/':
                include_once _PS_MODULE_DIR_ . 'aplazame/controllers/front/Api/article.php';
                $controller = new AplazameApiArticle(Db::getInstance());

                return $controller->articles($queryArguments);
            case '/confirm/':
                include_once _PS_MODULE_DIR_ . 'aplazame/controllers/front/Api/confirm.php';
                $controller = new AplazameApiConfirm(
                    Db::getInstance(),
                    (bool) Configuration::get('APLAZAME_SANDBOX'),
                    $this->module
                );

                return $controller->confirm($payload);
            case '/order/{order_id}/history/':
                include_once _PS_MODULE_DIR_ . 'aplazame/controllers/front/Api/order.php';
                $controller = new AplazameApiOrder(Db::getInstance());

                return $controller->history($pathArguments, $queryArguments);
            default:
                return self::not_found();
        }
    }

    /**
     * @return bool
     */
    private function verify_authentication()
    {
        $privateKey = Configuration::get('APLAZAME_SECRET_KEY');

        $authorization = $this->getHeaderAuthorization();
        if (!$authorization || empty($privateKey)) {
            return false;
        }

        return ($authorization === $privateKey);
    }

    private function getHeaderAuthorization()
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            $headers = $this->getallheaders();
        }
        $headers = array_change_key_case($headers, CASE_LOWER);

        if (isset($headers['authorization'])) {
            return trim(str_replace('Bearer', '', $headers['authorization']));
        }

        return false;
    }

    private function getallheaders()
    {
        $headers = array();
        $copy_server = array(
            'CONTENT_TYPE'   => 'content-type',
            'CONTENT_LENGTH' => 'content-length',
            'CONTENT_MD5'    => 'content-md5',
        );

        foreach ($_SERVER as $name => $value) {
            if (Tools::substr($name, 0, 5) === 'HTTP_') {
                $name = Tools::substr($name, 5);
                if (!isset($copy_server[$name]) || !isset($_SERVER[$name])) {
                    $headers[str_replace(' ', '-', Tools::strtolower(str_replace('_', ' ', $name)))] = $value;
                }
            } elseif (isset($copy_server[$name])) {
                $headers[$copy_server[$name]] = $value;
            }
        }

        if (!isset($headers['authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $headers['authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
    }
}
