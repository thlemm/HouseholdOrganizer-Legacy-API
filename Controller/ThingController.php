<?php
namespace Api\Controller;

use Api\Library\ApiException;
use Api\Entity\Thing;
use Api\Config\Database;

use \PDO;

/**
 * Class ThingController
 * Endpunkt für die Thing-Funktionen.
 *
 * @package Api\Controller
 */
class ThingController extends ApiController
{
  public function index($actionName, $actionParam) {
    try {

			$this->initialize();

      $input = json_decode(file_get_contents('php://input'), true);

      $requestMethod = $_SERVER['REQUEST_METHOD'];

      if ($actionName == 'getAll') {
        if ($requestMethod == 'GET') {
          $result = $this->getAllThings();
        } elseif ($requestMethod == 'OPTIONS') {
          $result = 'worked';
        } else {
          throw new ApiException('Die Methode ist für diesen Endpunkt nicht erlaubt.', ApiException::WRONG_METHOD);
        }
      } elseif ($actionName == 'get') {
        if ($requestMethod == 'GET') {
          $result = $this->getThingById($actionParam);
        } else if ($requestMethod == 'OPTIONS') {
          $result = 'worked';
        } else {
          throw new ApiException('Die Methode ist für diesen Endpunkt nicht erlaubt.', ApiException::WRONG_METHOD);
        }
      } elseif ($actionName == 'my') {
        if ($requestMethod == 'GET') {
          $result = $this->getThingsByUsername($actionParam);
        } else if ($requestMethod == 'OPTIONS') {
          $result = 'worked';
        } else {
          throw new ApiException('Die Methode ist für diesen Endpunkt nicht erlaubt.', ApiException::WRONG_METHOD);
        }
      } elseif ($actionName == 'unranked') {
        if ($requestMethod == 'GET') {
          $result = $this->getThingUnranked($actionParam);
        } else if ($requestMethod == 'OPTIONS') {
          $result = 'worked';
        } else {
          throw new ApiException('Die Methode ist für diesen Endpunkt nicht erlaubt.', ApiException::WRONG_METHOD);
        }
      } elseif ($actionName == 'rank') {
        if ($requestMethod == 'POST') {
          $result = $this->setRanking($input);
        } else if ($requestMethod == 'OPTIONS') {
          $result = 'worked';
        } else {
          throw new ApiException('Die Methode ist für diesen Endpunkt nicht erlaubt.', ApiException::WRONG_METHOD);
        }
      } elseif ($actionName == 'create') {
        if ($requestMethod == 'POST') {
          $result = $this->create($input);
        } else if ($requestMethod == 'OPTIONS') {
          $result = 'worked';
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
	 * @return array
	 */
  private function getAllThings() {
    // Headers
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    // Instantiate DB & connect
    $database = new Database();
    $db = $database->connect();

    // Instantiate thing object
    $thing = new Thing($db);

    // Category read query
    $result = $thing->read();

    // Get row count
    $num = $result->rowCount();

    // Check if any categories
    if($num > 0) {
      // Cat array
      $thing_arr = array();
      $thing_arr['data'] = array();

      while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $cat_item = array(
          'thing_id' => $thing_id,
          'tags' => $tags,
          'type' => $type,
          'room' => $room,
          'location' => $location,
          'box_id' => $box_id,
          'picture' => $picture
        );

        // Push to "data"
        array_push($thing_arr['data'], $cat_item);
      }

      return $thing_arr;
    } else {
      // No Categories
      return array('message' => 'No Things Found');
    }
  }


	/**
	 * @param int $id
	 *
	 * @return array
	 */
	private function getThingById($id)
	{
    // Headers
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    // Instantiate DB & connect
    $database = new Database();
    $db = $database->connect();

    // Instantiate thing object
    $thing = new Thing($db);
    $thing->thing_id = $id;

    // Category read query
    $result = $thing->read_single();

    // Get row count
    $num = $result->rowCount();

    if($num > 0) {
      // Cat array
      $thing_arr = array();
      $thing_arr['data'] = array();

      while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $cat_item = array(
          'thing_id' => $thing_id,
          'tags' => $tags,
          'type' => $type,
          'room' => $room,
          'location' => $location,
          'box_id' => $box_id,
          'picture' => $picture
        );

        // Push to "data"
        array_push($thing_arr['data'], $cat_item);
      }

      return $thing_arr;
    } else {
      // No Categories
      return array('message' => 'No Things Found');
    }
	}

  /**
	 * @param string $username
	 *
	 * @return array
	 */
	private function getThingsByUsername($username)
	{
    // Headers
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    // Instantiate DB & connect
    $database = new Database();
    $db = $database->connect();

    // Instantiate thing object
    $thing = new Thing($db);
    $thing->username = $username;

    // Category read query
    $result = $thing->read_user();

    // Get row count
    $num = $result->rowCount();

    if($num > 0) {
      // Cat array
      $thing_arr = array();
      $thing_arr['data'] = array();

      while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $cat_item = array(
          'thing_id' => $thing_id,
          'tags' => $tags,
          'type' => $type,
          'room' => $room,
          'location' => $location,
          'box_id' => $box_id,
          'picture' => $picture
        );

        // Push to "data"
        array_push($thing_arr['data'], $cat_item);
      }
      return $thing_arr;
    } else {
      // No Categories
      return array('message' => 'No Things Found');
    }
	}

	/**
	 * @param string $username
	 *
	 * @return array
	 */
	private function getThingUnranked($username)
	{
    // Headers
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    // Instantiate DB & connect
    $database = new Database();
    $db = $database->connect();

    // Instantiate thing object
    $thing = new Thing($db);
    $thing->username = $username;

    // Category read query
    $result = $thing->read_single_unranked();

    // Get row count
    $num = $result->rowCount();

    if($num > 0) {
      // Cat array
      $thing_arr = array();
      $thing_arr['data'] = array();

      while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $cat_item = array(
          'thing_id' => $thing_id,
          'tags' => $tags,
          'type' => $type,
          'room' => $room,
          'picture' => $picture
        );

        // Push to "data"
        array_push($thing_arr['data'], $cat_item);
      }

      return $thing_arr;
    } else {
      // No Categories
      return array('message' => 'No Things Found');
    }
	}

  /**
	 * @param object $data
	 *
	 * @return array
	 */
	private function setRanking($data)
	{
    // Headers
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    // Instantiate DB & connect
    $database = new Database();
    $db = $database->connect();

    // Instantiate thing object
    $thing = new Thing($db);
    $thing->thing_id = $data['thing_id'];
    $thing->username = $data['username'];
    $thing->status = $data['status'];

    // Category read query
    $result = $thing->update_single_ranking();

    if ($result) {
      return array('message' => 'success');
    } else {
      // No Categories
      return array('message' => 'No Things Found');
    }
	}

  /**
	 * @param object $data
	 *
	 * @return array
	 */
	private function create($data)
	{
    // Headers
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    // Instantiate DB & connect
    $database = new Database();
    $db = $database->connect();

    // Instantiate thing object
    $thing = new Thing($db);

    $thing->tags = $data['tags'];
    $thing->type = $data['type'];
    $thing->room = $data['room'];
    $thing->location = $data['location'];
    $thing->box_id = $data['box_id'];
    $thing->picture = $data['picture'];

    // Category read query
    $result = $thing->add_thing();

    if ($result) {
      return array('message' => 'success');
    } else {
      // No Categories
      return array('message' => 'No Things Found');
    }
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 * @throws ApiException
	 */
	private function update(array $data)
	{
		if (!isset($data['id'])) {
			throw new ApiException('Missing ID', ApiException::MALFORMED_INPUT);
		}

		// Benutzer aus der Datenbank laden
		// ...

		$user = $this->createExampleUser();

		// Benutzer aktualisieren
		$user->username = $data['username'];

		// Benutzer wieder in der DB speichern
		// ...

		return ['id' => $user->id];
	}

	/**
	 * @param int $id
	 *
	 * @return array
	 */
	private function delete($id)
	{
		// Benutzer in der Datenbank löschen
		// ...

		return [];
	}

	/**
	 * @return User
	 */
	private function createExampleUser()
	{
		$user           = new User();
		$user->id       = 5;
		$user->username = 'thomas';

		return $user;
	}
}
