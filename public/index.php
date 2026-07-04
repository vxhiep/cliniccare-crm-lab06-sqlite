<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

$appConfig = require ROOT_PATH . '/config/app.php';
$dbConfig = require ROOT_PATH . '/config/database.php';

date_default_timezone_set($appConfig['timezone'] ?? 'Asia/Ho_Chi_Minh');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require APP_PATH . '/Core/helpers.php';

spl_autoload_register(function (string $class): void {
    $folders = ['Core', 'Controllers', 'Services', 'Repositories'];
    foreach ($folders as $folder) {
        $file = APP_PATH . '/' . $folder . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

set_exception_handler(function (Throwable $e) use ($appConfig): void {
    app_log($e);
    http_response_code(500);
    $message = !empty($appConfig['debug'])
        ? $e->getMessage()
        : 'Hệ thống đang gặp lỗi. Vui lòng thử lại sau hoặc liên hệ quản trị viên.';
    render('errors/500', ['title' => '500 Server Error', 'message' => $message]);
});

if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $file = __DIR__ . $path;
    if ($path !== '/' && is_file($file)) {
        return false;
    }
}

check_session_timeout((int)($appConfig['session_timeout'] ?? 1800));

$pdo = fn(): PDO => Database::connect($dbConfig);

$GLOBALS['container'] = [
    UserRepository::class => fn() => new UserRepository($pdo()),
    PatientRepository::class => fn() => new PatientRepository($pdo()),
    AppointmentRepository::class => fn() => new AppointmentRepository($pdo()),

    AuthService::class => fn() => new AuthService($GLOBALS['container'][UserRepository::class]()),
    PatientService::class => fn() => new PatientService($GLOBALS['container'][PatientRepository::class]()),
    AppointmentService::class => fn() => new AppointmentService($GLOBALS['container'][AppointmentRepository::class]()),

    HomeController::class => fn() => new HomeController(),
    AuthController::class => fn() => new AuthController($GLOBALS['container'][AuthService::class]()),
    DashboardController::class => fn() => new DashboardController(
        $GLOBALS['container'][PatientRepository::class](),
        $GLOBALS['container'][AppointmentRepository::class]()
    ),
    PublicLeadController::class => fn() => new PublicLeadController($GLOBALS['container'][PatientService::class]()),
    PatientController::class => fn() => new PatientController($GLOBALS['container'][PatientService::class]()),
    AppointmentController::class => fn() => new AppointmentController($GLOBALS['container'][AppointmentService::class]()),
    HealthController::class => fn() => new HealthController($dbConfig),
];

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'handleLogin']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [DashboardController::class, 'index']);

$router->get('/public-leads/create', [PublicLeadController::class, 'create']);
$router->post('/public-leads', [PublicLeadController::class, 'store']);

$router->get('/patients', [PatientController::class, 'index']);
$router->get('/patients/create', [PatientController::class, 'create']);
$router->post('/patients/store', [PatientController::class, 'store']);
$router->get('/patients/edit', [PatientController::class, 'edit']);
$router->post('/patients/update', [PatientController::class, 'update']);
$router->post('/patients/delete', [PatientController::class, 'delete']);

$router->get('/appointments', [AppointmentController::class, 'index']);
$router->get('/appointments/create', [AppointmentController::class, 'create']);
$router->post('/appointments/store', [AppointmentController::class, 'store']);
$router->get('/appointments/edit', [AppointmentController::class, 'edit']);
$router->post('/appointments/update', [AppointmentController::class, 'update']);
$router->post('/appointments/delete', [AppointmentController::class, 'delete']);

$router->get('/health', [HealthController::class, 'index']);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$router->dispatch($method, $path);
