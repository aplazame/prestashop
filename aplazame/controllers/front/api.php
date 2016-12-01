<?php

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
        $path = isset($_GET['path']) ? $_GET['path'] : '';
        $pathArguments = isset($_GET['path_arguments']) ? json_decode($_GET['path_arguments'], true) : array();
        $queryArguments = isset($_GET['query_arguments']) ? json_decode($_GET['query_arguments'], true) : array();

        $response = $this->route($path, $pathArguments, $queryArguments);

        http_response_code($response['status_code']);
        header('Content-Type: application/json');

        exit(Tools::jsonEncode($response['payload']));
    }

    /**
     * @param string $path
     * @param array $pathArguments
     * @param array $queryArguments
     *
     * @return array
     */
    public function route($path, array $pathArguments, array $queryArguments)
    {
        if (!$this->verify_authentication()) {
            return self::forbidden();
        }

        switch ($path) {
            case '/article/':
                include_once _PS_MODULE_DIR_ . 'aplazame/controllers/front/Api/article.php';
                $controller = new AplazameApiArticle(Db::getInstance());

                return $controller->articles($queryArguments);
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
            $headers = array_change_key_case($headers, CASE_LOWER);
        } else {
            $headers = $this->getallheaders();
        }

        if (isset($headers['authorization'])) {
            return trim(str_replace('Bearer', '', $headers['authorization']));
        }

        return false;
    }

    private function getallheaders()
    {
        $headers = '';
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value;
            }
        }

        return $headers;
    }
}
