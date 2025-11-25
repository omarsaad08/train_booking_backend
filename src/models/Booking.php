<?php
class Booking
{
    private $conn;
    private $table_name = "bookings";

    public $id;
    public $user_id;
    public $from_city;
    public $to_city;
    public $schedule_time;
    public $booking_time;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Get all bookings
    public function read()
    {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY schedule_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get single booking by ID
    public function readOne()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->user_id = $row['user_id'];
            $this->from_city = $row['from_city'];
            $this->to_city = $row['to_city'];
            $this->schedule_time = $row['schedule_time'];
            $this->booking_time = $row['booking_time'];
            return true;
        }
        return false;
    }

    // Get bookings by user ID
    public function readByUserId($user_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = ? ORDER BY schedule_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt;
    }

    // Create new booking
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET user_id=:user_id, from_city=:from_city, to_city=:to_city, schedule_time=:schedule_time";

        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->from_city = htmlspecialchars(strip_tags($this->from_city));
        $this->to_city = htmlspecialchars(strip_tags($this->to_city));
        $this->schedule_time = htmlspecialchars(strip_tags($this->schedule_time));

        // Bind parameters
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":from_city", $this->from_city);
        $stmt->bindParam(":to_city", $this->to_city);
        $stmt->bindParam(":schedule_time", $this->schedule_time);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update booking
    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                 SET user_id=:user_id, from_city=:from_city, to_city=:to_city, schedule_time=:schedule_time
                 WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->from_city = htmlspecialchars(strip_tags($this->from_city));
        $this->to_city = htmlspecialchars(strip_tags($this->to_city));
        $this->schedule_time = htmlspecialchars(strip_tags($this->schedule_time));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind parameters
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":from_city", $this->from_city);
        $stmt->bindParam(":to_city", $this->to_city);
        $stmt->bindParam(":schedule_time", $this->schedule_time);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete booking
    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>