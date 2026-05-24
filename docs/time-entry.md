# Time Entry Module

## Setup

1. Run the module migration:

   ```bash
   php artisan migrate
   ```

2. Install the face-api.js model files under:

   ```text
   public/vendor/face-api/models
   ```

   Required model families:

   ```text
   tiny_face_detector_model
   face_landmark_68_model
   face_recognition_model
   ```

3. Serve the application through HTTPS in production. Browsers require a secure context for camera and geolocation APIs.

4. Grant Face Registration access by either:

   - assigning the user role `Administrator`, `HR`, or `HR Administrator`
   - adding `face_registration` to the user's `access` flags
   - adding `face_registration` to the user's menu permissions

## Routes

- `GET /time-entry`
- `GET /time-entry/register`
- `GET /time-entry/logs`
- `POST /api/time-entry/validate-qr`
- `POST /api/time-entry/verify-face`
- `POST /api/time-entry/status`
- `POST /api/time-entry/log-attendance`
- `GET /api/face-registration/employees`
- `POST /api/face-registration/register`
- `GET /api/face-registration/logs`

## Validation Flow

1. QR token is decrypted and validated server-side against an active employee.
2. Face descriptor is compared with the encrypted active profile in `employee_face_profiles`.
3. Liveness flags are required from live camera capture.
4. Browser geolocation must include latitude, longitude, and accuracy within 150 meters.
5. DTR action buttons are enabled from the current `dtrs` row state.
6. All successful and failed attempts are written to `time_entry_logs`.

## DTR Action Rules

- No Time In: only Time In is enabled.
- Time In exists and Time Out is empty: Time Out is enabled.
- Time Out exists: Time In and Time Out are disabled.
- Overtime is enabled after Time In when no overtime entry exists.
- Overtime writes once to the existing `dtrs.time_over` column and is disabled after it exists.

## Testing

Run:

```bash
php artisan route:list
php artisan migrate --pretend
php artisan test
```

Manual browser checks:

1. Open `/time-entry` on a camera-enabled device.
2. Scan a valid employee QR.
3. Complete live face verification.
4. Allow geolocation.
5. Confirm only valid action buttons are enabled.
6. Submit an action and verify both `dtrs` and `time_entry_logs`.
7. Open `/time-entry/register` as an authorized user and verify duplicate registration is blocked.
