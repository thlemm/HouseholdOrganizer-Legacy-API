<?php
namespace Api\Controller;

use Api\Library\ApiException;
use Api\Entity\Auth;
use Api\Config\Database;

use \PDO;

/**
 * Class AuthController
 * Endpunkt für die Auth-Funktionen.
 *
 * @package Api\Controller
 */
class AuthController
{
  public function index($actionName) {
    try {

			// $this->initialize();

      $input = json_decode(file_get_contents('php://input'), true);
      
      $requestMethod = $_SERVER['REQUEST_METHOD'];

      if ($actionName == 'login') {
        if ($requestMethod == 'POST') {
          $result = $this->login($input);
        } else if ($requestMethod == 'OPTIONS') {
          header('HTTP/1.1 204 No Content');
        } else {
          throw new ApiException('Die Methode ist für diesen Endpunkt nicht erlaubt.', ApiException::WRONG_METHOD);
        }
      } else {
        throw new ApiException('unknown action: '.$actionName, ApiException::UNKNOWN_ACTION);
      }


      
      header('HTTP/1.0 200 OK');

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

  /**
	 * @param array $data
	 *
	 * @return array
	 * @throws ApiException
	 */
	private function login(array $data) {
    // Headers
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    // Instantiate DB & connect
    $database = new Database();
    $db = $database->connect();

    // Instantiate thing object
    $auth = new Auth($db);

    // Get username
    $auth->username = $data['username'];

    // Category read query
    $auth->getPasswordfromUser();
    $result = $auth->password;

    if ($auth->password == $data['password']) {
      $auth->createAuthToken();
      $response = $auth->jsonBody;
      return $response;
    } else {
      throw new ApiException('AUTHENTICATION_FAILED', ApiException::AUTHENTICATION_FAILED);
    }
  }


}