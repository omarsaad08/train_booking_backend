# Quick Debug Steps

## After you try to login from Flutter, run this command:

```powershell
docker logs train_booking_backend-php-1 --tail 50 | Select-String "LOGIN"
```

This will show us:

1. What data Flutter is sending
2. Whether the user exists in the database
3. Whether the password verification is failing

## What to look for:

- **"Raw input:"** - Shows the exact JSON Flutter sent
- **"Email:"** - Shows if the email was parsed correctly
- **"Password provided:"** - Shows if password was included
- **"User found:"** or **"User NOT found:"** - Shows if the email exists in database
- **"Password verified successfully"** or **"Password verification FAILED"** - Shows if password matches

## Try this now:

1. Attempt login from your Flutter app
2. Run: `docker logs train_booking_backend-php-1 --tail 50 | Select-String "LOGIN"`
3. Share the output with me

This will tell us exactly what's wrong!
