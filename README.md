# ClinicCare Appointment CRM - PHP Lab06 Final SQLite

Biến thể Lab06 Final: **Clinic Appointment CRM**. Project quản lý bệnh nhân tiềm năng và lịch hẹn khám, không giữ nguyên Secure Mini CRM mẫu. Phiên bản này đã được chuyển sang dùng **SQLite qua PDO_SQLite** để chạy gọn, không cần tạo database MySQL/MariaDB.

## 1. Chức năng chính

- Front Controller + Router: mọi request đi qua `public/index.php`.
- Login/logout bằng session, cookie flags, `session_regenerate_id(true)`, timeout, flash message.
- Public form `/public-leads/create` có validate server-side, giữ old input, PRG, honeypot, rate limit 5 giây/lần.
- Module A: `patients` CRUD, unique email, search, pagination, sort whitelist.
- Module B: `appointments` CRUD, unique appointment_code, search, pagination, sort whitelist.
- PDO SQLite chuẩn: `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, `EMULATE_PREPARES=false`, `PRAGMA foreign_keys=ON`.
- 404, 405, 500 production safe error.
- EXPLAIN mẫu trong `database/explain.sql` dùng `EXPLAIN QUERY PLAN`.

## 2. Yêu cầu môi trường

- PHP 8.x
- Extension `pdo_sqlite`
- Git
- Browser/Postman/curl

Kiểm tra:

```bash
php -v
php -m | grep -i sqlite
git --version
```

Trên Windows PowerShell có thể kiểm tra SQLite extension bằng:

```powershell
php -m | findstr /i sqlite
```

Nếu chưa thấy `pdo_sqlite`, mở file `php.ini` và bật các dòng sau rồi restart terminal/server:

```ini
extension=pdo_sqlite
extension=sqlite3
```

## 3. Cấu hình database SQLite

File cấu hình nằm tại `config/database.php`:

```php
return [
    'driver' => 'sqlite',
    'path' => dirname(__DIR__) . '/database/cliniccare.sqlite',
];
```

Project có cơ chế tự tạo database SQLite nếu file `database/cliniccare.sqlite` chưa tồn tại. Khi kết nối lần đầu, app sẽ chạy:

```text
database/schema.sql
database/seed.sql
```

Vì vậy cách nhanh nhất là chỉ cần chạy server. Nếu muốn tạo lại dữ liệu từ đầu, xóa file:

```text
database/cliniccare.sqlite
```

rồi mở lại website.

## 4. Chạy project

Tại thư mục gốc project:

```bash
php -S localhost:8000 -t public public/index.php
```

Mở:

```text
http://localhost:8000
```

## 5. Tài khoản demo

```text
Email: admin@cliniccare.test
Password: 123456
```

## 6. Route table

| Method | URL | Controller@Action | Ý nghĩa |
|---|---|---|---|
| GET | `/` | HomeController@index | Trang giới thiệu |
| GET | `/login` | AuthController@login | Form login |
| POST | `/login` | AuthController@handleLogin | Xác thực login |
| POST | `/logout` | AuthController@logout | Logout sạch |
| GET | `/dashboard` | DashboardController@index | Dashboard yêu cầu login |
| GET | `/public-leads/create` | PublicLeadController@create | Form công khai |
| POST | `/public-leads` | PublicLeadController@store | Lưu form công khai |
| GET | `/patients` | PatientController@index | List/search/page/sort |
| GET | `/patients/create` | PatientController@create | Form tạo patient |
| POST | `/patients/store` | PatientController@store | Tạo patient |
| GET | `/patients/edit?id=1` | PatientController@edit | Form sửa patient |
| POST | `/patients/update` | PatientController@update | Cập nhật patient |
| POST | `/patients/delete` | PatientController@delete | Xóa patient bằng POST |
| GET | `/appointments` | AppointmentController@index | List/search/page/sort |
| GET | `/appointments/create` | AppointmentController@create | Form tạo appointment |
| POST | `/appointments/store` | AppointmentController@store | Tạo appointment |
| GET | `/appointments/edit?id=1` | AppointmentController@edit | Form sửa appointment |
| POST | `/appointments/update` | AppointmentController@update | Cập nhật appointment |
| POST | `/appointments/delete` | AppointmentController@delete | Xóa appointment bằng POST |
| GET | `/health` | HealthController@index | JSON app/database status |

## 7. Test nhanh bằng curl

```bash
curl -i http://localhost:8000/health
curl -i -X POST http://localhost:8000/health
curl -i http://localhost:8000/unknown
```

Kỳ vọng:

- `GET /health` trả JSON.
- `POST /health` trả 405.
- `/unknown` trả 404.

## 8. Chạy EXPLAIN SQLite

Nếu máy có lệnh `sqlite3`, chạy:

```bash
sqlite3 database/cliniccare.sqlite < database/explain.sql
```

Nếu không có lệnh `sqlite3`, vẫn có thể mở file `database/explain.sql` để chụp câu lệnh, hoặc chạy bằng DB Browser for SQLite.

## 9. Cấu trúc project

```text
project/
├── public/
│   ├── index.php
│   └── assets/style.css
├── config/
│   ├── app.php
│   └── database.php
├── app/
│   ├── Core/
│   ├── Controllers/
│   ├── Services/
│   ├── Repositories/
│   └── Views/
├── database/
│   ├── schema.sql
│   ├── seed.sql
│   ├── explain.sql
│   └── cliniccare.sqlite     # được tự tạo khi chạy lần đầu
├── storage/logs/
└── README.md
```

## 10. Lưu ý debug/prod

- `config/app.php` đang để `'debug' => false` để production safe.
- Khi DB lỗi, user chỉ thấy thông báo an toàn; chi tiết lỗi ghi vào `storage/logs/app.log`.
- Không hiển thị SQLSTATE, tên bảng, path hoặc stack trace cho user.

## 11. Các thay đổi so với bản MySQL

- `config/database.php` đổi sang `driver => sqlite` và `path => database/cliniccare.sqlite`.
- `Database.php` đổi DSN sang `sqlite:` và bật `PRAGMA foreign_keys=ON`.
- `schema.sql` đổi `AUTO_INCREMENT` thành `INTEGER PRIMARY KEY AUTOINCREMENT`.
- `ENUM` được thay bằng `TEXT CHECK (...)`.
- `ON UPDATE CURRENT_TIMESTAMP` được thay bằng cập nhật thủ công trong Repository: `updated_at = CURRENT_TIMESTAMP`.
- Duplicate key SQLite được bắt qua `SQLSTATE 23000`/driver code `19` và trả lỗi thân thiện.
- `EXPLAIN` đổi thành `EXPLAIN QUERY PLAN`.

## 12. Gợi ý ảnh cần chụp khi nộp

- T01: terminal `php -v`, `php -m | grep -i sqlite`, Git.
- T02: VS Code Explorer cấu trúc project.
- T03: code `public/index.php` và `Router.php`.
- T04: code `session_set_cookie_params()`.
- T07-T09: public form lỗi, submit đúng, honeypot/rate limit.
- T10-T12: login sai/đúng, logout, timeout.
- T13-T16: schema SQLite, seed, Database.php, Repository.
- T20-T25: CRUD, duplicate, search/page/sort nguy hiểm.
- T26-T29: health JSON, 404/405, production safe error, EXPLAIN QUERY PLAN.
- T30: GitHub README và `git log --oneline`.
