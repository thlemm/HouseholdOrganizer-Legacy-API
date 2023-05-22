<?php
namespace Api\Entity;

class Auth extends BaseEntity
{
  // DB Stuff
  private $conn;
  private $table = 'catalog_users';

  // Properties
  public $user_id;
  public $username;
  public $password;
  public $token;
  public $jsonBody;

  // Constructor with DB
  public function __construct($db) {
    $this->conn = $db;
  }

  // Get password
  public function getPasswordfromUser(){
    // Create query
    $query = 'SELECT
        `password`
      FROM
        ' . $this->table . '
      WHERE username = ?
      LIMIT 0,1';

    //Prepare statement
    $stmt = $this->conn->prepare($query);

    // Bind ID
    $stmt->bindParam(1, $this->username);

    // Execute query
    $stmt->execute();

    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (isset($row['password'])) {
      $this->password = $row['password'];
    } else {
      $this->password = false;
    }
  }

  public function createAuthToken(){
    $this->token = bin2hex(random_bytes(32));

    $query = "UPDATE ". $this->table ." SET `token` = '".$this->token."' WHERE `username` = '".$this->username."';";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();

    $this->jsonBody = (object)array();
    $this->jsonBody->username = $this->username;
    $this->jsonBody->accessToken = $this->token;
    $this->jsonBody->tokenType = "Bearer";
  }

}
