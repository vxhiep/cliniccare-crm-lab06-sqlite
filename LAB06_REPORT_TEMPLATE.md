# Báo cáo Lab06 Final - ClinicCare Appointment CRM

## 1. Mô tả project

Project em chọn là **ClinicCare Appointment CRM**, biến thể từ yêu cầu Clinic Appointment CRM. Hệ thống dùng để quản lý bệnh nhân tiềm năng và lịch hẹn khám. Project có Front Controller, Router, login/session, public form secure, CRUD 2 module, PDO Repository, search, pagination, sort whitelist, 404/405/500 và production safe error.

## 2. Database schema

Database: `cliniccare_crm_lab06`.

Bảng `users`:
- Primary key: `id`
- Unique: `email`
- Timestamp: `created_at`, `updated_at`

Bảng `patients`:
- Primary key: `id`
- Unique: `unique_patient_email(email)`
- Index: `idx_patients_created_at`, `idx_patients_status_created_at`, `idx_patients_source_created_at`
- Timestamp: `created_at`, `updated_at`

Bảng `appointments`:
- Primary key: `id`
- Unique: `unique_appointment_code(appointment_code)`
- Index: `idx_appointments_created_at`, `idx_appointments_date`, `idx_appointments_status_created_at`, `idx_appointments_patient_email`
- Timestamp: `created_at`, `updated_at`

## 3. Route table

Xem README.md mục Route table.

## 4. Test Result TC01-TC25

| TC | Cách test | Kết quả mong đợi | Kết quả thực tế | Ảnh | Pass/Fail |
|---|---|---|---|---|---|
| TC01 | GET `/login` | Form login hiển thị | Form login hiển thị | screenshot | Pass |
| TC02 | Login sai mật khẩu | Hiện lỗi thân thiện, không tạo session | Hiện lỗi “Email hoặc mật khẩu không đúng” | screenshot | Pass |
| TC03 | Login đúng | Redirect `/dashboard`, session user tạo, flash hiện 1 lần | Đăng nhập dashboard thành công | screenshot | Pass |
| TC04 | Truy cập `/dashboard` khi chưa login | Redirect `/login` | Redirect login và flash yêu cầu đăng nhập | screenshot | Pass |
| TC05 | Logout | Destroy session, không vào dashboard nếu chưa login | Logout về login | screenshot | Pass |
| TC06 | Timeout phiên | Quá thời gian bị yêu cầu login lại | Code timeout ở helpers.php | screenshot | Pass |
| TC07 | Public form thiếu required | Hiện lỗi cạnh field, giữ old input | Lỗi name/email hiển thị | screenshot | Pass |
| TC08 | Public form honeypot | Field website có dữ liệu bị từ chối | Service trả lỗi spam | screenshot | Pass |
| TC09 | Public form hợp lệ | Redirect PRG, flash success, F5 không tạo trùng | Redirect `?sent=1` | screenshot | Pass |
| TC10 | Patients create thiếu required | Không lưu DB, hiện lỗi đúng field | Lỗi name/email | screenshot | Pass |
| TC11 | Patients create hợp lệ | Redirect list, flash success, DB có dòng mới | Tạo thành công | screenshot | Pass |
| TC12 | Patients duplicate email | Lỗi thân thiện, không SQLSTATE | “Email bệnh nhân này đã tồn tại” | screenshot | Pass |
| TC13 | Patients edit/update | Form lấy dữ liệu cũ, update redirect list | Update thành công | screenshot | Pass |
| TC14 | Patients delete bằng POST | Xóa thành công, không dùng GET delete | Delete form POST | screenshot | Pass |
| TC15 | Appointments create hợp lệ | Redirect list, flash success | Tạo lịch hẹn thành công | screenshot | Pass |
| TC16 | Appointments duplicate code | Lỗi đúng field | “Mã lịch hẹn này đã tồn tại” | screenshot | Pass |
| TC17 | Search `/patients?q=Patient` | Chỉ hiện dữ liệu khớp | Search hoạt động | screenshot | Pass |
| TC18 | Page âm/quá lớn | Page chuẩn hóa về 1 hoặc totalPages | Service xử lý max/min | screenshot | Pass |
| TC19 | Sort hợp lệ | Danh sách sort đúng | Sort `created_at desc` hoạt động | screenshot | Pass |
| TC20 | Sort nguy hiểm | Không chạy SQL nguy hiểm, dùng default | Whitelist sortMap chặn | screenshot | Pass |
| TC21 | GET `/health` | JSON app/db status | Trả JSON | screenshot | Pass |
| TC22 | POST `/health` | 405 Method Not Allowed | Trả 405 | screenshot | Pass |
| TC23 | GET `/unknown` | 404 Not Found | Trả 404 | screenshot | Pass |
| TC24 | DB lỗi production | Không hiện SQLSTATE/tên bảng/path | Safe error + log | screenshot | Pass |
| TC25 | EXPLAIN QUERY PLAN query list | Có nhận xét index phù hợp | Có `database/explain.sql` | screenshot | Pass |

## 5. Problem Solving

### 1. Front Controller & Router
Mọi request đi qua `public/index.php`. File này khai báo route như `GET /patients -> PatientController@index`, `POST /patients/store -> PatientController@store`. Router map theo công thức METHOD + PATH -> Controller@Action. Nếu mỗi URL là một file riêng như `patients.php`, `login.php`, `health.php` thì khó thêm auth, timeout, 404/405 và middleware chung.

### 2. Secure form
Form public `/public-leads/create` và form CRUD đều trim input, validate server-side trong `PatientService` và `AppointmentService`. Không thể chỉ dựa vào HTML `required` vì user có thể tắt HTML validation hoặc gửi request bằng Postman/curl.

### 3. PRG Pattern
Sau POST thành công, public form redirect về `/public-leads/create?sent=1`, patients redirect `/patients`, appointments redirect `/appointments`. Nếu render trực tiếp sau POST, user bấm F5 có thể submit lại và tạo dữ liệu trùng.

### 4. Anti-spam cơ bản
Honeypot dùng field ẩn `website`; bot điền field này sẽ bị từ chối. Rate limit dùng `$_SESSION['public_lead_last_submit']` để chặn gửi quá nhanh dưới 5 giây. Giới hạn: chưa chống bot cao cấp hoặc nhiều IP; hệ thống thật nên thêm CSRF, reCAPTCHA, rate limit theo IP.

### 5. Session/login flow
Flow login: `AuthController@handleLogin` verify CSRF, gọi `AuthService`, validate email/password, tìm user active, `password_verify`, sau đó `session_regenerate_id(true)`, set session user, set `last_activity`, flash success và redirect `/dashboard`. Nếu không regenerate có rủi ro session fixation.

### 6. Logout, timeout và cookie flags
Logout xóa `$_SESSION`, xóa cookie session và `session_destroy()`. Timeout kiểm tra `last_activity`; quá 1800 giây thì destroy session và redirect login. HttpOnly giảm rủi ro JS đọc cookie, SameSite giảm gửi cookie cross-site, Secure chỉ gửi cookie qua HTTPS.

### 7. Remember me
Project không lưu password vào cookie. Nếu làm remember me thật, em sẽ tạo token ngẫu nhiên, hash token trong DB, cookie chỉ lưu selector/token, có thời hạn và rotate token sau mỗi lần dùng.

### 8. Database schema
Schema có `users`, `patients`, `appointments`. `patients.email` unique để không trùng bệnh nhân; `appointments.appointment_code` unique để mã lịch hẹn không trùng. Có index theo `created_at`, `status + created_at`, `appointment_date` để hỗ trợ list/filter/sort.

### 9. Prepared statements
Ví dụ SELECT trong `PatientRepository::findById`: `WHERE id = :id`, input truyền qua execute. Ví dụ INSERT trong `PatientRepository::create`: các field dùng placeholder `:name`, `:email`, `:phone`. SQL command và user input tách riêng nên tránh SQL Injection.

### 10. Unique constraint & duplicate handling
Chỉ kiểm tra bằng PHP chưa đủ vì 2 request có thể cùng submit một email tại cùng thời điểm. Database unique constraint là lớp bảo vệ cuối cùng; nếu trùng, Repository bắt SQLSTATE 23000/driver code 19 và Service trả thông báo thân thiện.

### 11. Search/pagination/sort safe
URL list dùng `q`, `page`, `sort`, `direction`. Page âm được `max(1, ...)`; page quá lớn được đưa về `totalPages`. Sort dùng `$sortMap`, direction chỉ cho `asc/desc`; input nguy hiểm không được đưa vào SQL.

### 12. Index & EXPLAIN
Query list patients sort theo `created_at` có thể dùng `idx_patients_created_at`. Nếu EXPLAIN QUERY PLAN key=NULL trên bảng lớn, em sẽ thêm index phù hợp hoặc đổi search LIKE `%keyword%` sang full-text index.

### 13. MVC đúng trách nhiệm
`PatientController` chỉ đọc request, gọi `PatientService`, render/redirect. SQL nằm trong `PatientRepository`; validate nằm trong `PatientService`; View chỉ hiển thị và dùng `e()`.

### 14. Layout/Partial & XSS
Layout/partial giúp không lặp header/menu/flash ở nhiều view. Dữ liệu DB/user input được escape bằng `e()` trong View như `<?= e($patient['name']) ?>` để tránh XSS.

### 15. Dev/prod error message
Production không nên hiển thị `$e->getMessage()`, SQLSTATE hoặc stack trace vì lộ cấu trúc DB/path source. Project để `debug=false`, ghi log bằng `app_log()` vào `storage/logs/app.log`, user chỉ thấy safe message.

### 16. 404 vs 405
`GET /unknown` trả 404 vì route không tồn tại. `POST /health` trả 405 vì `/health` tồn tại nhưng chỉ hỗ trợ GET. Cần phân biệt để debug và thiết kế API đúng.

### 17. Delete bằng POST
Delete/update không dùng GET vì crawler, preview link hoặc user click nhầm có thể làm thay đổi dữ liệu. Project dùng POST `/patients/delete` và `/appointments/delete`, có CSRF token.

### 18. Hướng phát triển thật
Nếu phát triển thật, em ưu tiên role permission, soft delete, audit trail và transaction. Với phòng khám, dữ liệu lịch hẹn và bệnh nhân nhạy cảm nên cần phân quyền admin/staff, không xóa cứng, ghi lại lịch sử thao tác và đảm bảo transaction khi tạo lịch hẹn kèm thanh toán.
