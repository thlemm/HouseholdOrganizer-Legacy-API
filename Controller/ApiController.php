<?php
namespace Api\Controller;

use Api\Library\ApiException;
use Api\Config\Database;

use \PDO;

abstract class ApiController
{
	public function initialize()
	{
		$this->checkToken();
    // $this->checkIpAddress();
    // $this->checkHostName();
	}

	/**
	 * @throws ApiException
	 */
	private function checkToken()
	{
		// Token prüfen
		$split = [];

    $requestMethod = $_SERVER['REQUEST_METHOD'];
    if ($requestMethod == 'OPTIONS') {
      return;
    }

		// Wenn der Server das für uns auftrennt.
		if (isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
			$split = [$_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']];
		}

		// Falls wir mal selbst was testen wollen - PHPUnit!
		if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$split = explode('=', $_SERVER['HTTP_AUTHORIZATION']);
		}

		// Das hier wird wohl normalerweise richtig sein
		if (function_exists('getallheaders')) {
			$headers = getallheaders();

			if (isset($headers['Authorization'])) {
				$split = explode(' ', $headers['Authorization']);
			}
		}

		if (isset($split[1])) {
      $token = trim($split[1], '"');
      $result = $this->validateToken($token);
			if ($result == 1) {
				return;
			}
		}

		throw new ApiException('access denied', ApiException::AUTHENTICATION_FAILED);
	}

	/**
	 * @throws ApiException
	 */
	private function checkIpAddress()
	{
		$whiteList = ['127.0.0.1'];

		$ip = $_SERVER['REMOTE_ADDR'];

		foreach ($whiteList as $allowed) {
			if ($ip == $allowed) {
				// Exakte Übereinstimmung, direkt fertig.
				return;
			} elseif (strpos($allowed, '/') !== false) {
				// Netzmaske prüfen
				list($allowed, $netmask) = explode('/', $allowed, 2);
				$x = explode('.', $allowed);
				while (count($x) < 4) {
					$x[] = '0';
				}
				$range        = sprintf("%u.%u.%u.%u", (int)$x[0], (int)$x[1], (int)$x[2], (int)$x[3]);
				$rangeDecimal = ip2long($range);
				$ipDecimal    = ip2long($ip);

				$wildcardDecimal = pow(2, (32 - $netmask)) - 1;
				$netmaskDecimal  = ~$wildcardDecimal;

				if (($ipDecimal & $netmaskDecimal) == ($rangeDecimal & $netmaskDecimal)) {
					// Netzmaske enhält die IP
					return;
				}
			}
		}

		throw new ApiException('access denied', ApiException::AUTHENTICATION_FAILED);
  }
  
  /**
	 * @throws ApiException
	 */
	private function checkHostName()
	{
		$whiteList = ['localhost'];

		$ip = $_SERVER['SERVER_NAME'];

		foreach ($whiteList as $allowed) {
			if ($ip == $allowed) {
				// Exakte Übereinstimmung, direkt fertig.
				return;
			} 
		}

		throw new ApiException('access denied', ApiException::AUTHENTICATION_FAILED);
	}

  /**
	 * @param string $token
	 *
	 * @return array
	 */
  private function validateToken($token){
    // Instantiate DB & connect
    $database = new Database();
    $db = $database->connect();
    $query = "SELECT EXISTS(SELECT * FROM `catalog_users` WHERE `token` = '".$token."' AND `timestamp` > (NOW() - INTERVAL 120 MINUTE))  AS val;";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $result['val'];
  }
}