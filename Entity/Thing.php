<?php
namespace Api\Entity;

class Thing extends BaseEntity {
  // DB Stuff
  private $conn;
  private $table = 'catalog_things';

  // Properties
  public $thing_id;
  public $tags;
  public $type;
  public $room;
  public $location;
  public $box_id;
  public $picture;
  public $username;
  public $status;

  // Constructor with DB
  public function __construct($db) {
    $this->conn = $db;
  }

  // Get all things
  public function read() {
    // Create query
    $query = 'SELECT `thing_id`, `tags`, `type`, `room`, `location`, `box_id`, `picture` FROM '. $this->table .' ORDER BY `thing_id` ASC;';

    // Prepare statement
    $stmt = $this->conn->prepare($query);

    // Execute query
    $stmt->execute();

    return $stmt;
  }

  // Get single thing
  public function read_single(){
    // Create query
    $query = 'SELECT `thing_id`, `tags`, `type`, `room`, `location`, `box_id`, `picture` FROM '. $this->table .' WHERE `thing_id` = '.$this->thing_id.' LIMIT 0,1;';

    //Prepare statement
    $stmt = $this->conn->prepare($query);

    // Execute query
    $stmt->execute();
    error_log(json_encode($stmt));
    return $stmt;
  }

  // Get things from user
  public function read_user(){
    // Create query
    $query = 'SELECT `thing_id`, `tags`, `type`, `room`, `location`, `box_id`, `picture` FROM '. $this->table .' WHERE `'.$this->username.'` = 1  ORDER BY `thing_id` ASC;';

    //Prepare statement
    $stmt = $this->conn->prepare($query);

    // Execute query
    $stmt->execute();

    return $stmt;
  }

  // Get things from user
  public function read_single_unranked(){
    // Create query
    $query = 'SELECT `thing_id`, `tags`, `type`, `room`, `picture` FROM '. $this->table .' WHERE `'.$this->username.'` IS NULL ORDER BY `thing_id` ASC LIMIT 1;';

    //Prepare statement
    $stmt = $this->conn->prepare($query);

    // Execute query
    $stmt->execute();

    return $stmt;
  }


  // Set user ranking on thing_id
  public function update_single_ranking(){
    // Create query
    $query = 'UPDATE '.$this->table.' SET `'.$this->username.'` = '.$this->status.' WHERE `thing_id` = '.$this->thing_id.';';
    error_log($query);
    //Prepare statement
    $stmt = $this->conn->prepare($query);

    // Execute query
    if($stmt->execute()) {
      return true;
    }

    // Print error if something goes wrong
    printf("Error: $s.\n", $stmt->error);

    return false;
  }

  // Create thing
  public function add_thing() {
    // Create Query
    $query = "INSERT INTO ".$this->table ." SET tags = :tags, type = :type, room = :room, picture = :picture;";

    // Prepare Statement
    $stmt = $this->conn->prepare($query);

    // Clean data
    $this->tags = htmlspecialchars(strip_tags($this->tags));
    $this->type = htmlspecialchars(strip_tags($this->type));
    $this->room = htmlspecialchars(strip_tags($this->room));
    $this->picture = htmlspecialchars(strip_tags($this->picture));

    // Bind data
    $stmt-> bindParam(':tags', $this->tags);
    $stmt-> bindParam(':type', $this->type);
    $stmt-> bindParam(':room', $this->room);
    $stmt-> bindParam(':picture', $this->picture);

    // Execute query
    if($stmt->execute()) {
      return true;
    }

    // Print error if something goes wrong
    printf("Error: $s.\n", $stmt->error);

    return false;
  }

  // Update Category
  public function update() {
    // Create Query
    $query = 'UPDATE ' .
      $this->table . '
        SET
          name = :name
          WHERE
          id = :id';

      // Prepare Statement
      $stmt = $this->conn->prepare($query);

      // Clean data
      $this->name = htmlspecialchars(strip_tags($this->name));
      $this->id = htmlspecialchars(strip_tags($this->id));

      // Bind data
      $stmt-> bindParam(':name', $this->name);
      $stmt-> bindParam(':id', $this->id);

      // Execute query
      if($stmt->execute()) {
        return true;
      }

      // Print error if something goes wrong
      printf("Error: $s.\n", $stmt->error);

      return false;
  }

  // Delete Category
  public function delete() {
    // Create query
    $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';

    // Prepare Statement
    $stmt = $this->conn->prepare($query);

    // clean data
    $this->id = htmlspecialchars(strip_tags($this->id));

    // Bind Data
    $stmt-> bindParam(':id', $this->id);

    // Execute query
    if($stmt->execute()) {
      return true;
    }

    // Print error if something goes wrong
    printf("Error: $s.\n", $stmt->error);

    return false;
    }
}
