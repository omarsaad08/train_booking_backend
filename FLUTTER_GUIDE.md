# Flutter App Debugging Guide

## Common 401 Error Causes

### 1. Calling Protected Endpoints Without Token

If you're getting 401 on endpoints like `/schedules`, `/bookings`, or `/users`, you need to include the authentication token.

**Wrong** ❌:

```dart
final response = await http.get(
  Uri.parse('http://192.168.1.218:8080/schedules'),
);
```

**Correct** ✅:

```dart
final response = await http.get(
  Uri.parse('http://192.168.1.218:8080/schedules'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
  },
);
```

### 2. Signup/Login Should NOT Return 401

If you're getting 401 on `/signup` or `/login`, that's unexpected. These endpoints don't require authentication.

**Signup Example**:

```dart
final response = await http.post(
  Uri.parse('http://192.168.1.218:8080/signup'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'name': 'Test User',
    'email': 'test@example.com',
    'password': 'password123',
  }),
);

if (response.statusCode == 201) {
  final data = jsonDecode(response.body);
  final token = data['token'];
  // Save token for future requests
}
```

**Login Example**:

```dart
final response = await http.post(
  Uri.parse('http://192.168.1.218:8080/login'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'email': 'test@example.com',
    'password': 'password123',
  }),
);

if (response.statusCode == 200) {
  final data = jsonDecode(response.body);
  final token = data['token'];
  // Save token for future requests
}
```

### 3. Using the Token for Protected Endpoints

After login/signup, use the token:

```dart
// Get schedules (protected)
final response = await http.get(
  Uri.parse('http://192.168.1.218:8080/schedules'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
  },
);

// Create schedule (protected)
final response = await http.post(
  Uri.parse('http://192.168.1.218:8080/schedules'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
  },
  body: jsonEncode({
    'from_city': 'Cairo',
    'to_city': 'Alexandria',
    'departure_datetime': '2023-12-01 08:00:00',
    'arrival_datetime': '2023-12-01 12:00:00',
  }),
);
```

## Complete Flutter Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class ApiService {
  static const String baseUrl = 'http://192.168.1.218:8080';
  String? _token;

  // Signup
  Future<bool> signup(String name, String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/signup'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'name': name,
          'email': email,
          'password': password,
        }),
      );

      if (response.statusCode == 201) {
        final data = jsonDecode(response.body);
        _token = data['token'];
        return true;
      }
      return false;
    } catch (e) {
      print('Signup error: $e');
      return false;
    }
  }

  // Login
  Future<bool> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/login'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'email': email,
          'password': password,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        _token = data['token'];
        return true;
      }
      return false;
    } catch (e) {
      print('Login error: $e');
      return false;
    }
  }

  // Get schedules (protected)
  Future<List<dynamic>> getSchedules() async {
    if (_token == null) {
      throw Exception('Not authenticated');
    }

    try {
      final response = await http.get(
        Uri.parse('$baseUrl/schedules'),
        headers: {
          'Authorization': 'Bearer $_token',
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['data'];
      } else if (response.statusCode == 401) {
        throw Exception('Unauthorized - token may be invalid');
      }
      throw Exception('Failed to load schedules');
    } catch (e) {
      print('Get schedules error: $e');
      rethrow;
    }
  }

  // Create schedule (protected)
  Future<bool> createSchedule({
    required String fromCity,
    required String toCity,
    required String departureDateTime,
    required String arrivalDateTime,
  }) async {
    if (_token == null) {
      throw Exception('Not authenticated');
    }

    try {
      final response = await http.post(
        Uri.parse('$baseUrl/schedules'),
        headers: {
          'Authorization': 'Bearer $_token',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'from_city': fromCity,
          'to_city': toCity,
          'departure_datetime': departureDateTime,
          'arrival_datetime': arrivalDateTime,
        }),
      );

      return response.statusCode == 201;
    } catch (e) {
      print('Create schedule error: $e');
      return false;
    }
  }
}
```

## Debugging Steps

1. **Print the response**:

```dart
print('Status: ${response.statusCode}');
print('Body: ${response.body}');
```

2. **Check which endpoint returns 401**:

   - If it's `/signup` or `/login` → There's a bug, these shouldn't require auth
   - If it's `/schedules`, `/bookings`, etc. → You need to add the token

3. **Verify the token format**:

```dart
print('Token: $_token');
// Should be a long hex string like: 1943adfe0756f9e0f1f4f8956...
```

4. **Test the endpoint manually first**:

```bash
# From PowerShell on your computer
$body = @{email="test@test.com";password="test123"} | ConvertTo-Json
$response = Invoke-RestMethod -Uri "http://192.168.1.218:8080/login" -Method POST -Body $body -ContentType "application/json"
$token = $response.token
Write-Host "Token: $token"

# Test protected endpoint
$headers = @{Authorization = "Bearer $token"}
Invoke-RestMethod -Uri "http://192.168.1.218:8080/schedules" -Method GET -Headers $headers
```

## Need More Help?

Tell me:

1. Which endpoint is returning 401? (signup, login, schedules, etc.)
2. What does your Flutter code look like for that request?
3. What does `print(response.body)` show?
