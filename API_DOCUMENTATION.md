# API Documentation

## Base URL

The API is accessible at the root of your server (e.g., `http://localhost:8080` or `http://192.168.1.218:8080`).

## Authentication

Most endpoints require authentication. After signup or login, you'll receive a token that must be included in the `Authorization` header for all protected endpoints.

**Header Format:**

```
Authorization: Bearer <your-token-here>
```

## Endpoints

### Authentication (No Auth Required)

| Method   | Endpoint  | Description        | Request Body (JSON)                                                          | Response (JSON)                    |
| :------- | :-------- | :----------------- | :--------------------------------------------------------------------------- | :--------------------------------- |
| **POST** | `/signup` | Create new account | `{"name": "John Doe", "email": "john@example.com", "password": "secret123"}` | `{"token": "abcdef1234567890..."}` |
| **POST** | `/login`  | Login to account   | `{"email": "john@example.com", "password": "secret123"}`                     | `{"token": "abcdef1234567890..."}` |

### Users (Auth Required)

| Method  | Endpoint               | Description       | Request Body (JSON) | Response (JSON)                                                 | Auth Required |
| :------ | :--------------------- | :---------------- | :------------------ | :-------------------------------------------------------------- | :------------ |
| **GET** | `/users?id={id}`       | Get user by ID    | N/A                 | `{"id": 1, "name": "...", "email": "...", "created_at": "..."}` | ✅ Yes        |
| **GET** | `/users?email={email}` | Get user by email | N/A                 | `{"id": 1, "name": "...", "email": "...", "created_at": "..."}` | ✅ Yes        |

### Bookings (Auth Required)

| Method     | Endpoint                 | Description          | Request Body (JSON)                                                                                     | Response (JSON)                                                                                                                                     | Auth Required |
| :--------- | :----------------------- | :------------------- | :------------------------------------------------------------------------------------------------------ | :-------------------------------------------------------------------------------------------------------------------------------------------------- | :------------ |
| **GET**    | `/bookings`              | Get all bookings     | N/A                                                                                                     | `{"data": [{"id": 1, "user_id": 1, "from_city": "Cairo", "to_city": "Alexandria", "schedule_time": "2023-12-01 08:00:00", "booking_time": "..."}]}` | ✅ Yes        |
| **GET**    | `/bookings?id={id}`      | Get booking by ID    | N/A                                                                                                     | `{"id": 1, "user_id": 1, "from_city": "Cairo", "to_city": "Alexandria", "schedule_time": "...", "booking_time": "..."}`                             | ✅ Yes        |
| **GET**    | `/bookings?user_id={id}` | Get bookings by user | N/A                                                                                                     | `{"data": [{"id": 1, "user_id": 1, "from_city": "...", "to_city": "...", "schedule_time": "...", "booking_time": "..."}]}`                          | ✅ Yes        |
| **POST**   | `/bookings`              | Create a booking     | `{"user_id": 1, "from_city": "Cairo", "to_city": "Alexandria", "schedule_time": "2023-12-01 08:00:00"}` | `{"message": "Booking was created."}`                                                                                                               | ✅ Yes        |
| **PUT**    | `/bookings`              | Update a booking     | `{"id": 1, "user_id": 1, "from_city": "...", "to_city": "...", "schedule_time": "..."}`                 | `{"message": "Booking was updated."}`                                                                                                               | ✅ Yes        |
| **DELETE** | `/bookings`              | Delete a booking     | `{"id": 1}`                                                                                             | `{"message": "Booking was deleted."}`                                                                                                               | ✅ Yes        |

## Example Usage

### 1. Signup

```bash
curl -X POST http://localhost:8080/signup \
  -H "Content-Type: application/json" \
  -d '{"name": "John Doe", "email": "john@example.com", "password": "secret123"}'
```

Response:

```json
{
  "token": "abcdef1234567890..."
}
```

### 2. Create a Booking

```bash
curl -X POST http://localhost:8080/bookings \
  -H "Authorization: Bearer <your-token>" \
  -H "Content-Type: application/json" \
  -d '{"user_id": 1, "from_city": "Cairo", "to_city": "Alexandria", "schedule_time": "2023-12-01 08:00:00"}'
```

### 3. Get User's Bookings

```bash
curl -X GET "http://localhost:8080/bookings?user_id=1" \
  -H "Authorization: Bearer <your-token>"
```

## Flutter Example

```dart
// Create a booking
final response = await http.post(
  Uri.parse('http://192.168.1.218:8080/bookings'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
  },
  body: jsonEncode({
    'user_id': userId,
    'from_city': 'Cairo',
    'to_city': 'Alexandria',
    'schedule_time': '2023-12-01 08:00:00',
  }),
);

// Get user's bookings
final response = await http.get(
  Uri.parse('http://192.168.1.218:8080/bookings?user_id=$userId'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
  },
);
```
