<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2022 Aplazame
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

    public static function success(array $payload)
    {
        return array(
            'status_code' => 200,
            'payload' => $payload,
        );
    }

    public static function collection($page, $page_size, array $elements)
    {
        return self::success(array(
                'query' => array(
                    'page' => $page,
                    'page_size' => $page_size,
                ),
                'elements' => $elements,
            ));
    }

    public function postProcess()
    {
        $path = Tools::getValue('path', '');
        $queryArguments = $_GET;
        $payload = Tools::jsonDecode(Tools::file_get_contents('php://input'), true);

        $response = $this->route($path, $queryArguments, $payload);

        $this->http_response_code($response['status_code']);
        header('Content-Type: application/json');

        exit(Tools::jsonEncode($response['payload']));
    }

    /**
     * @param string $path
     * @param array $queryArguments
     * @param null|array $payload
     *
     * @return array
     */
    public function route($path, array $queryArguments, $payload)
    {
        if (!$this->verifyAuthentication()) {
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

                return $controller->confirm($queryArguments, $payload);
            case '/order/history/':
                include_once _PS_MODULE_DIR_ . 'aplazame/controllers/front/Api/order.php';
                $controller = new AplazameApiOrder(Db::getInstance());

                return $controller->history($queryArguments);
            default:
                return self::not_found();
        }
    }

    /**
     * @return bool
     */
    private function verifyAuthentication()
    {
        $privateKey = Configuration::get('APLAZAME_SECRET_KEY');

        $authorization = $this->getAuthorizationFromRequest();
        if (!$authorization || empty($privateKey)) {
            return false;
        }

        return ($authorization === $privateKey);
    }

    private function getAuthorizationFromRequest()
    {
        $token = Tools::getValue('access_token', null);
        if ($token) {
            return $token;
        }

        return false;
    }

    public function http_response_code($code = null)
    {
        if (function_exists('http_response_code')) {
            return http_response_code($code);
        }

        switch ($code) {
            case 100:
                $text = 'Continue';
                break;
            case 101:
                $text = 'Switching Protocols';
                break;
            case 200:
                $text = 'OK';
                break;
            case 201:
                $text = 'Created';
                break;
            case 202:
                $text = 'Accepted';
                break;
            case 203:
                $text = 'Non-Authoritative Information';
                break;
            case 204:
                $text = 'No Content';
                break;
            case 205:
                $text = 'Reset Content';
                break;
            case 206:
                $text = 'Partial Content';
                break;
            case 300:
                $text = 'Multiple Choices';
                break;
            case 301:
                $text = 'Moved Permanently';
                break;
            case 302:
                $text = 'Moved Temporarily';
                break;
            case 303:
                $text = 'See Other';
                break;
            case 304:
                $text = 'Not Modified';
                break;
            case 305:
                $text = 'Use Proxy';
                break;
            case 400:
                $text = 'Bad Request';
                break;
            case 401:
                $text = 'Unauthorized';
                break;
            case 402:
                $text = 'Payment Required';
                break;
            case 403:
                $text = 'Forbidden';
                break;
            case 404:
                $text = 'Not Found';
                break;
            case 405:
                $text = 'Method Not Allowed';
                break;
            case 406:
                $text = 'Not Acceptable';
                break;
            case 407:
                $text = 'Proxy Authentication Required';
                break;
            case 408:
                $text = 'Request Time-out';
                break;
            case 409:
                $text = 'Conflict';
                break;
            case 410:
                $text = 'Gone';
                break;
            case 411:
                $text = 'Length Required';
                break;
            case 412:
                $text = 'Precondition Failed';
                break;
            case 413:
                $text = 'Request Entity Too Large';
                break;
            case 414:
                $text = 'Request-URI Too Large';
                break;
            case 415:
                $text = 'Unsupported Media Type';
                break;
            case 500:
                $text = 'Internal Server Error';
                break;
            case 501:
                $text = 'Not Implemented';
                break;
            case 502:
                $text = 'Bad Gateway';
                break;
            case 503:
                $text = 'Service Unavailable';
                break;
            case 504:
                $text = 'Gateway Time-out';
                break;
            case 505:
                $text = 'HTTP Version not supported';
                break;
            default:
                exit('Unknown http status code "' . htmlentities($code) . '"');
        }

        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

        header($protocol . ' ' . $code . ' ' . $text);

        return $code;
    }
}
