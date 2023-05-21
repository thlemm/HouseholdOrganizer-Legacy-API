<?php
namespace Api\Controller;

use Api\Library\ApiException;

use \PDO;

/**
 * Class IndexController
 * Endpunkt für die Index-Funktionen.
 *
 * @package Api\Controller
 */
class IndexController extends ApiController
{
  public function index($actionName, $actionParam) {
    try {

			$this->initialize();

      throw new ApiException('unknown action: '.$actionName, ApiException::UNKNOWN_ACTION);

		} catch (ApiException $e) {
			if ($e->getCode() == ApiException::AUTHENTICATION_FAILED) {
				header('HTTP/1.0 401 Unauthorized');
      } elseif ($e->getCode() == ApiException::MALFORMED_INPUT) {
				header('HTTP/1.0 400 Bad Request');
			} elseif ($e->getCode() == ApiException::WRONG_METHOD) {
				header('HTTP/1.0 405 Method Not Allowed');
      } elseif ($e->getCode() == ApiException::UNKNOWN_ACTION) {
				header('HTTP/1.0 400 Bad Request');
      }

      $result = ['message' => $e->getMessage()];

    }

    header('Content-Type: application/json');
    
    echo json_encode($result);
  }

}