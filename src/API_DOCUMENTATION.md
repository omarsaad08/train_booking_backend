# API Documentation

## Base URL

The API is accessible at the root of your server (e.g., `http://localhost:8000`).

## Authentication

Most endpoints require authentication. After signup or login, you'll receive a token that must be included in the `Authorization` header for all protected endpoints.

**Header Format:**

```
Authorization: Bearer <your-token-here>
```

or simply:

```
Authorization: <your-token-here>
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

### Routes (Auth Required)

| Method     | Endpoint          | Description     | Request Body (JSON)                                 | Response (JSON)                                                 | Auth Required |
| :--------- | :---------------- | :-------------- | :-------------------------------------------------- | :-------------------------------------------------------------- | :------------ |
| **GET**    | `/routes`         | Get all routes  | N/A                                                 | `{"data": [{"id": 1, "start_city": "...", "end_city": "..."}]}` | ✅ Yes        |
| **GET**    | `/routes?id={id}` | Get route by ID | N/A                                                 | `{"id": 1, "start_city": "...", "end_city": "..."}`             | ✅ Yes        |
| **POST**   | `/routes`         | Create a route  | `{"start_city": "New York", "end_city": "Boston"}`  | `{"message": "Route was created."}`                             | ✅ Yes        |
| **PUT**    | `/routes`         | Update a route  | `{"id": 1, "start_city": "...", "end_city": "..."}` | `{"message": "Route was updated."}`                             | ✅ Yes        |
| **DELETE** | `/routes`         | Delete a route  | `{"id": 1}`                                         | `{"message": "Route was deleted."}`                             | ✅ Yes        |

### Train Schedules (Auth Required)

| Method     | Endpoint             | Description        | Request Body (JSON)                                                                                       | Response (JSON)                                                                                | Auth Required |
| :--------- | :------------------- | :----------------- | :-------------------------------------------------------------------------------------------------------- | :--------------------------------------------------------------------------------------------- | :------------ |
| **GET**    | `/schedules`         | Get all schedules  | N/A                                                                                                       | `{"data": [{"id": 1, "route_id": 1, "departure_datetime": "...", "arrival_datetime": "..."}]}` | ✅ Yes        |
| **GET**    | `/schedules?id={id}` | Get schedule by ID | N/A                                                                                                       | `{"id": 1, "route_id": 1, "departure_datetime": "...", "arrival_datetime": "..."}`             | ✅ Yes        |
| **POST**   | `/schedules`         | Create a schedule  | `{"route_id": 1, "departure_datetime": "2023-12-01 08:00:00", "arrival_datetime": "2023-12-01 12:00:00"}` | `{"message": "Train schedule was created."}`                                                   | ✅ Yes        |
| **PUT**    | `/schedules`         | Update a schedule  | `{"id": 1, "route_id": 1, "departure_datetime": "...", "arrival_datetime": "..."}`                        | `{"message": "Train schedule was updated."}`                                                   | ✅ Yes        |
| **DELETE** | `/schedules`         | Delete a schedule  | `{"id": 1}`                                                                                               | `{"message": "Train schedule was deleted."}`                                                   | ✅ Yes        |

### Bookings (Auth Required)

| Method     | Endpoint            | Description       | Request Body (JSON)                         | Response (JSON)                                                                | Auth Required |
| :--------- | :------------------ | :---------------- | :------------------------------------------ | :----------------------------------------------------------------------------- | :------------ |
| **GET**    | `/bookings`         | Get all bookings  | N/A                                         | `{"data": [{"id": 1, "user_id": 1, "schedule_id": 1, "booking_time": "..."}]}` | ✅ Yes        |
| **GET**    | `/bookings?id={id}` | Get booking by ID | N/A                                         | `{"id": 1, "user_id": 1, "schedule_id": 1, "booking_time": "..."}`             | ✅ Yes        |
| **POST**   | `/bookings`         | Create a booking  | `{"user_id": 1, "schedule_id": 1}`          | `{"message": "Booking was created."}`                                          | ✅ Yes        |
| **PUT**    | `/bookings`         | Update a booking  | `{"id": 1, "user_id": 1, "schedule_id": 1}` | `{"message": "Booking was updated."}`                                          | ✅ Yes        |
| **DELETE** | `/bookings`         | Delete a booking  | `{"id": 1}`                                 | `{"message": "Booking was deleted."}`                                          | ✅ Yes        |

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

### 2. Use the token for authenticated requests

```bash
curl -X GET http://localhost:8080/routes \
  -H "Authorization: Bearer abcdef1234567890..."
```
