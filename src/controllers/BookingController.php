<?php
class BookingController
{
    private $db;
    private $booking;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->booking = new Booking($this->db);
    }

    // GET - Get all bookings or single booking by ID or by user_id
    public function handleGet()
    {
        if (isset($_GET['id'])) {
            $this->getById($_GET['id']);
        } else if (isset($_GET['user_id'])) {
            $this->getByUserId($_GET['user_id']);
        } else {
            $this->getAll();
        }
    }

    // Get all bookings
    public function getAll()
    {
        $stmt = $this->booking->read();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $bookings_arr = array();
            $bookings_arr["data"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $booking_item = array(
                    "id" => $id,
                    "user_id" => $user_id,
                    "from_city" => $from_city,
                    "to_city" => $to_city,
                    "schedule_time" => $schedule_time,
                    "booking_time" => $booking_time
                );
                array_push($bookings_arr["data"], $booking_item);
            }
            http_response_code(200);
            echo json_encode($bookings_arr);
        } else {
            http_response_code(200);
            echo json_encode(array("data" => array()));
        }
    }

    // Get single booking by ID
    public function getById($id)
    {
        $this->booking->id = $id;

        if ($this->booking->readOne()) {
            $booking_item = array(
                "id" => $this->booking->id,
                "user_id" => $this->booking->user_id,
                "from_city" => $this->booking->from_city,
                "to_city" => $this->booking->to_city,
                "schedule_time" => $this->booking->schedule_time,
                "booking_time" => $this->booking->booking_time
            );
            http_response_code(200);
            echo json_encode($booking_item);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Booking not found."));
        }
    }

    // Get bookings by user ID
    public function getByUserId($user_id)
    {
        $stmt = $this->booking->readByUserId($user_id);
        $num = $stmt->rowCount();

        if ($num > 0) {
            $bookings_arr = array();
            $bookings_arr["data"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $booking_item = array(
                    "id" => $id,
                    "user_id" => $user_id,
                    "from_city" => $from_city,
                    "to_city" => $to_city,
                    "schedule_time" => $schedule_time,
                    "booking_time" => $booking_time
                );
                array_push($bookings_arr["data"], $booking_item);
            }
            http_response_code(200);
            echo json_encode($bookings_arr);
        } else {
            http_response_code(200);
            echo json_encode(array("data" => array()));
        }
    }

    // POST - Create new booking
    public function handlePost()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($data->user_id) && !empty($data->from_city) &&
            !empty($data->to_city) && !empty($data->schedule_time)
        ) {

            $this->booking->user_id = $data->user_id;
            $this->booking->from_city = $data->from_city;
            $this->booking->to_city = $data->to_city;
            $this->booking->schedule_time = $data->schedule_time;

            if ($this->booking->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Booking was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create booking."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create booking. Data is incomplete."));
        }
    }

    // PUT - Update booking
    public function handlePut()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->id)) {
            $this->booking->id = $data->id;
            $this->booking->user_id = $data->user_id;
            $this->booking->from_city = $data->from_city;
            $this->booking->to_city = $data->to_city;
            $this->booking->schedule_time = $data->schedule_time;

            if ($this->booking->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Booking was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update booking."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update booking. Data is incomplete."));
        }
    }

    // DELETE - Delete booking
    public function handleDelete()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->id)) {
            $this->booking->id = $data->id;

            if ($this->booking->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Booking was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete booking."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to delete booking. Data is incomplete."));
        }
    }
}
?>