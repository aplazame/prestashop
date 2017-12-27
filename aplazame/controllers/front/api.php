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

    public static function notFound()
    {
        return array(
            'status_code' => 404,
            'payload' => array(
                'status' => 404,
                'type' => 'NOT_FOUND',
            ),
        );
    }

    public static function clientError($detail)
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
        $payload = Tools::jsonDecode(Tools::file_get_contents('php://input'), true);

        $response = $this->route($path, $pathArguments, $queryArguments, $payload);

        $this->http_response_code($response['status_code']);
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

                return $controller->confirm($payload);
            case '/order/{order_id}/history/':
                include_once _PS_MODULE_DIR_ . 'aplazame/controllers/front/Api/order.php';
                $controller = new AplazameApiOrder(Db::getInstance());

                return $controller->history($pathArguments, $queryArguments);
            default:
                return self::notFound();
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
}
