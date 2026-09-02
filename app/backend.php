<?php

declare(strict_types=1);

/**
 * API mandiri untuk frontend React.
 *
 * File ini sengaja tidak memakai bootstrap Laravel. Ia memakai PDO dan skema
 * database yang sudah ada, sehingga frontend dan backend dapat dijalankan
 * secara terpisah selama masa migrasi.
 */

final class ApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 400,
        public readonly array $errors = []
    ) {
        parent::__construct($message);
    }
}

const ROOT_PATH = __DIR__ . '/..';
const MAX_UPLOAD_SIZE = 10 * 1024 * 1024;

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$environment = loadEnvironment(ROOT_PATH . '/.env');
if (!$environment) {
    $environment = loadEnvironment(ROOT_PATH . '/.env.example');
}

date_default_timezone_set(envValue('APP_TIMEZONE', 'UTC'));
setCorsHeaders();
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');

if (requestMethod() === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    routeRequest();
} catch (ApiException $exception) {
    jsonResponse([
        'success' => false,
        'message' => $exception->getMessage(),
        'errors' => $exception->errors ?: null,
    ], $exception->status);
} catch (PDOException $exception) {
    $debug = filter_var(envValue('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL);
    jsonResponse([
        'success' => false,
        'message' => 'Database belum siap atau tidak dapat dihubungi.',
        'detail' => $debug ? $exception->getMessage() : null,
    ], 503);
} catch (Throwable $exception) {
    $debug = filter_var(envValue('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL);
    jsonResponse([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.',
        'detail' => $debug ? $exception->getMessage() : null,
    ], 500);
}

function routeRequest(): never
{
    $route = trim((string) ($_GET['route'] ?? 'health'), '/');
    $method = requestMethod();

    if ($route === 'health' && $method === 'GET') {
        $database = 'connected';
        try {
            database()->query('SELECT 1');
        } catch (Throwable) {
            $database = 'unavailable';
        }

        jsonResponse([
            'success' => true,
            'data' => [
                'service' => 'Absensi API',
                'status' => 'ok',
                'database' => $database,
                'time' => date(DATE_ATOM),
            ],
        ]);
    }

    if ($route === 'auth/login' && $method === 'POST') {
        login();
    }

    if ($route === 'auth/bootstrap' && $method === 'POST') {
        bootstrapFirstUser();
    }

    if ($route === 'auth/forgot-password' && $method === 'POST') {
        forgotPassword();
    }

    if ($route === 'auth/reset-password' && $method === 'POST') {
        resetPassword();
    }

    if ($route === 'auth/verification/verify' && $method === 'GET') {
        verifyEmailAddress();
    }

    if ($route === 'files' && $method === 'GET') {
        requireUser();
        serveStoredFile((string) ($_GET['path'] ?? ''));
    }

    $user = requireUser();

    if ($route === 'auth/me' && $method === 'GET') {
        jsonResponse(['success' => true, 'data' => publicUser($user)]);
    }

    if ($route === 'auth/logout' && $method === 'POST') {
        logout();
    }

    if ($route === 'auth/verification/send' && $method === 'POST') {
        sendEmailVerification($user);
    }

    if ($route === 'dashboard' && $method === 'GET') {
        dashboard($user);
    }

    if ($route === 'attendance' && $method === 'POST') {
        saveAttendance($user);
    }

    if ($route === 'profile' && $method === 'POST') {
        updateProfile($user);
    }

    if ($route === 'access' && $method === 'GET') {
        requirePermission($user, 'manage-access');
        accessDashboard($user);
    }

    if ($route === 'access/assign' && $method === 'POST') {
        requirePermission($user, 'manage-access');
        assignUserAccess();
    }

    if ($route === 'roles' && $method === 'POST') {
        requirePermission($user, 'manage-access');
        createRole();
    }

    if (preg_match('/^roles\/(\d+)$/', $route, $matches)) {
        requirePermission($user, 'manage-access');
        if ($method === 'POST' || $method === 'PUT') {
            updateRole((int) $matches[1]);
        }
        if ($method === 'DELETE') {
            deleteRole((int) $matches[1]);
        }
    }

    if (preg_match('/^roles\/(\d+)\/permissions$/', $route, $matches) && $method === 'POST') {
        requirePermission($user, 'manage-access');
        syncRolePermissions((int) $matches[1]);
    }

    if ($route === 'permissions' && $method === 'POST') {
        requirePermission($user, 'manage-access');
        createPermission();
    }

    if (preg_match('/^permissions\/(\d+)$/', $route, $matches)) {
        requirePermission($user, 'manage-access');
        if ($method === 'POST' || $method === 'PUT') {
            updatePermission((int) $matches[1]);
        }
        if ($method === 'DELETE') {
            deletePermission((int) $matches[1]);
        }
    }

    if ($route === 'directory' && $method === 'GET') {
        userDirectory();
    }

    if ($route === 'users' && $method === 'GET') {
        requirePermission($user, 'manage-users');
        listUsers();
    }

    if ($route === 'users' && $method === 'POST') {
        requirePermission($user, 'manage-users');
        createUser();
    }

    if (preg_match('/^users\/(\d+)$/', $route, $matches)) {
        requirePermission($user, 'manage-users');
        $id = (int) $matches[1];
        if ($method === 'GET') {
            showUser($id);
        }
        if ($method === 'POST' || $method === 'PUT') {
            updateUser($id);
        }
        if ($method === 'DELETE') {
            deleteUser($id, (int) $user['id']);
        }
    }

    if ($route === 'schedules' && $method === 'GET') {
        listSchedules($user);
    }

    if ($route === 'schedules' && ($method === 'POST' || $method === 'PUT')) {
        requirePermission($user, 'manage-schedules');
        saveSchedule();
    }

    if ($route === 'logbooks' && $method === 'GET') {
        listLogbooks($user);
    }

    if ($route === 'logbooks' && $method === 'POST') {
        createLogbook($user);
    }

    if (preg_match('/^logbooks\/(\d+)$/', $route, $matches)) {
        $id = (int) $matches[1];
        if ($method === 'POST' || $method === 'PUT') {
            updateLogbook($user, $id);
        }
        if ($method === 'DELETE') {
            deleteLogbook($user, $id);
        }
    }

    if ($route === 'reports/attendance' && $method === 'GET') {
        attendanceReport($user);
    }

    if ($route === 'reports/logbooks' && $method === 'GET') {
        logbookReport($user);
    }

    if ($route === 'pns' && $method === 'GET') {
        searchPns();
    }

    if ($route === 'referrals' && $method === 'GET') {
        listReferrals($user);
    }

    if ($route === 'referrals' && $method === 'POST') {
        createReferral($user);
    }

    if (preg_match('/^referrals\/(\d+)$/', $route, $matches)) {
        $id = (int) $matches[1];
        if ($method === 'GET') {
            showReferral($user, $id);
        }
        if ($method === 'POST' || $method === 'PUT') {
            updateReferral($user, $id);
        }
        if ($method === 'DELETE') {
            deleteReferral($user, $id);
        }
    }

    if (preg_match('/^referrals\/(\d+)\/confirm$/', $route, $matches) && $method === 'POST') {
        requirePermission($user, 'manage-referrals');
        confirmReferral((int) $matches[1]);
    }

    throw new ApiException('Endpoint tidak ditemukan.', 404);
}

function login(): never
{
    $data = requestData();
    $email = strtolower(trim((string) ($data['email'] ?? '')));
    $password = (string) ($data['password'] ?? '');

    $errors = [];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = ['Email tidak valid.'];
    }
    if ($password === '') {
        $errors['password'] = ['Password wajib diisi.'];
    }
    if ($errors) {
        throw new ApiException('Data login belum benar.', 422, $errors);
    }

    ensureTokenTable();
    $user = fetchOne('SELECT * FROM users WHERE LOWER(email) = ? LIMIT 1', [$email]);
    if (!$user || !password_verify($password, (string) $user['password'])) {
        usleep(350000);
        throw new ApiException('Email atau password salah.', 401);
    }

    $remember = filter_var($data['remember'] ?? false, FILTER_VALIDATE_BOOL);
    $plainToken = bin2hex(random_bytes(32));
    $expiresAt = (new DateTimeImmutable($remember ? '+30 days' : '+12 hours'))->format('Y-m-d H:i:s');

    executeStatement(
        'INSERT INTO api_tokens (user_id, token_hash, expires_at, last_used_at, created_at) VALUES (?, ?, ?, ?, ?)',
        [(int) $user['id'], hash('sha256', $plainToken), $expiresAt, nowString(), nowString()]
    );

    jsonResponse([
        'success' => true,
        'message' => 'Login berhasil.',
        'data' => [
            'token' => $plainToken,
            'expires_at' => $expiresAt,
            'user' => publicUser($user),
        ],
    ]);
}

function bootstrapFirstUser(): never
{
    $count = (int) fetchValue('SELECT COUNT(*) FROM users');
    if ($count > 0) {
        throw new ApiException('Inisialisasi hanya tersedia saat tabel users masih kosong.', 403);
    }

    $data = requestData();
    $data['jabatan'] = 'manajemen';
    $validated = validateUserPayload($data, true, null);
    $now = nowString();
    executeStatement(
        'INSERT INTO users (name, email, jabatan, nomor_hp, password, email_verified_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        [
            $validated['name'],
            $validated['email'],
            'manajemen',
            $validated['nomor_hp'],
            password_hash($validated['password'], PASSWORD_BCRYPT),
            $now,
            $now,
            $now,
        ]
    );

    jsonResponse(['success' => true, 'message' => 'Akun manajemen pertama berhasil dibuat.'], 201);
}

function forgotPassword(): never
{
    $data = requestData();
    $email = strtolower(trim((string) ($data['email'] ?? '')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new ApiException('Email tidak valid.', 422, ['email' => ['Masukkan alamat email yang valid.']]);
    }

    $user = fetchOne('SELECT id, name, email FROM users WHERE LOWER(email) = ? LIMIT 1', [$email]);
    $debugUrl = null;
    if ($user) {
        $plainToken = bin2hex(random_bytes(32));
        executeStatement('DELETE FROM password_reset_tokens WHERE email = ?', [$email]);
        executeStatement(
            'INSERT INTO password_reset_tokens (email, token, created_at) VALUES (?, ?, ?)',
            [$email, hash('sha256', $plainToken), nowString()]
        );
        $debugUrl = frontendUrl('/reset-password?token=' . urlencode($plainToken) . '&email=' . urlencode($email));
        sendAppMail(
            $email,
            'Reset password SiHadir',
            "Halo {$user['name']},\n\nGunakan tautan berikut untuk membuat password baru. Tautan berlaku 60 menit:\n{$debugUrl}\n\nAbaikan email ini bila Anda tidak meminta reset password."
        );
    }

    $response = [
        'success' => true,
        'message' => 'Jika email terdaftar, tautan reset password telah dikirim.',
    ];
    if ($debugUrl && filter_var(envValue('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL)) {
        $response['debug_reset_url'] = $debugUrl;
    }
    jsonResponse($response);
}

function resetPassword(): never
{
    $data = requestData();
    $email = strtolower(trim((string) ($data['email'] ?? '')));
    $plainToken = trim((string) ($data['token'] ?? ''));
    $password = (string) ($data['password'] ?? '');
    $confirmation = (string) ($data['password_confirmation'] ?? '');
    $errors = [];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = ['Email tidak valid.'];
    }
    if ($plainToken === '') {
        $errors['token'] = ['Token reset tidak tersedia.'];
    }
    if (mb_strlen($password) < 8) {
        $errors['password'] = ['Password minimal 8 karakter.'];
    }
    if ($password !== $confirmation) {
        $errors['password_confirmation'] = ['Konfirmasi password tidak sama.'];
    }
    if ($errors) {
        throw new ApiException('Data reset password belum benar.', 422, $errors);
    }

    $reset = fetchOne('SELECT email, token, created_at FROM password_reset_tokens WHERE email = ? LIMIT 1', [$email]);
    $createdAt = $reset ? strtotime((string) $reset['created_at']) : false;
    if (!$reset || !hash_equals((string) $reset['token'], hash('sha256', $plainToken)) || $createdAt === false || $createdAt < time() - 3600) {
        throw new ApiException('Tautan reset password tidak valid atau sudah kedaluwarsa.', 422, ['token' => ['Minta tautan reset password yang baru.']]);
    }

    $user = fetchOne('SELECT id FROM users WHERE LOWER(email) = ? LIMIT 1', [$email]);
    if (!$user) {
        throw new ApiException('Tautan reset password tidak valid.', 422);
    }
    executeStatement('UPDATE users SET password = ?, remember_token = ?, updated_at = ? WHERE id = ?', [password_hash($password, PASSWORD_BCRYPT), bin2hex(random_bytes(30)), nowString(), (int) $user['id']]);
    executeStatement('DELETE FROM password_reset_tokens WHERE email = ?', [$email]);
    ensureTokenTable();
    executeStatement('DELETE FROM api_tokens WHERE user_id = ?', [(int) $user['id']]);
    jsonResponse(['success' => true, 'message' => 'Password berhasil diubah. Silakan login kembali.']);
}

function sendEmailVerification(array $user): never
{
    if (!empty($user['email_verified_at'])) {
        jsonResponse(['success' => true, 'message' => 'Email sudah terverifikasi.']);
    }
    $url = issueEmailVerification($user);
    $response = ['success' => true, 'message' => 'Tautan verifikasi telah dikirim ke email Anda.'];
    if (filter_var(envValue('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL)) {
        $response['debug_verification_url'] = $url;
    }
    jsonResponse($response);
}

function verifyEmailAddress(): never
{
    ensureEmailVerificationTable();
    $userId = (int) ($_GET['id'] ?? 0);
    $plainToken = trim((string) ($_GET['token'] ?? ''));
    $record = fetchOne('SELECT id, user_id, token_hash, expires_at FROM api_email_verifications WHERE user_id = ? ORDER BY id DESC LIMIT 1', [$userId]);
    if (!$record || $plainToken === '' || !hash_equals((string) $record['token_hash'], hash('sha256', $plainToken)) || strtotime((string) $record['expires_at']) < time()) {
        throw new ApiException('Tautan verifikasi tidak valid atau sudah kedaluwarsa.', 422);
    }
    executeStatement('UPDATE users SET email_verified_at = ?, updated_at = ? WHERE id = ?', [nowString(), nowString(), $userId]);
    executeStatement('DELETE FROM api_email_verifications WHERE user_id = ?', [$userId]);
    jsonResponse(['success' => true, 'message' => 'Alamat email berhasil diverifikasi.']);
}

function issueEmailVerification(array $user): string
{
    ensureEmailVerificationTable();
    $plainToken = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 86400);
    executeStatement('DELETE FROM api_email_verifications WHERE user_id = ?', [(int) $user['id']]);
    executeStatement('INSERT INTO api_email_verifications (user_id, token_hash, expires_at, created_at) VALUES (?, ?, ?, ?)', [(int) $user['id'], hash('sha256', $plainToken), $expires, nowString()]);
    $url = frontendUrl('/verify-email?id=' . (int) $user['id'] . '&token=' . urlencode($plainToken));
    sendAppMail((string) $user['email'], 'Verifikasi email SiHadir', "Halo {$user['name']},\n\nVerifikasi alamat email Anda melalui tautan berikut:\n{$url}\n\nTautan berlaku selama 24 jam.");
    return $url;
}

function logout(): never
{
    $token = bearerToken();
    if ($token !== '') {
        executeStatement('DELETE FROM api_tokens WHERE token_hash = ?', [hash('sha256', $token)]);
    }
    jsonResponse(['success' => true, 'message' => 'Anda telah keluar.']);
}

function dashboard(array $user): never
{
    $now = new DateTimeImmutable();
    $today = $now->format('Y-m-d 00:00:00');
    $recent = $now->modify('-36 hours')->format('Y-m-d H:i:s');
    $monthStart = $now->modify('first day of this month')->setTime(0, 0)->format('Y-m-d H:i:s');
    $monthEnd = $now->modify('first day of next month')->setTime(0, 0)->format('Y-m-d H:i:s');

    $attendance = fetchOne(
        'SELECT * FROM absens WHERE user_id = ? AND (created_at >= ? OR (foto_pulang IS NULL AND created_at >= ?)) ORDER BY id DESC LIMIT 1',
        [(int) $user['id'], $today, $recent]
    );

    $stats = [
        'hadir' => (int) fetchValue('SELECT COUNT(*) FROM absens WHERE user_id = ? AND created_at >= ? AND created_at < ?', [(int) $user['id'], $monthStart, $monthEnd]),
        'terlambat' => (int) fetchValue("SELECT COUNT(*) FROM absens WHERE user_id = ? AND created_at >= ? AND created_at < ? AND telat IS NOT NULL AND telat <> ''", [(int) $user['id'], $monthStart, $monthEnd]),
        'logbook' => (int) fetchValue('SELECT COUNT(*) FROM logbooks WHERE user_id = ? AND tanggal >= ? AND tanggal < ?', [(int) $user['id'], substr($monthStart, 0, 10), substr($monthEnd, 0, 10)]),
        'perjalanan_dinas' => (int) fetchValue('SELECT COUNT(DISTINCT rujukan_id) FROM rujukan_user WHERE user_id = ?', [(int) $user['id']]),
    ];

    jsonResponse([
        'success' => true,
        'data' => [
            'user' => publicUser($user),
            'attendance' => $attendance ?: null,
            'stats' => $stats,
            'shifts' => shiftDefinitions(),
            'server_time' => $now->format(DATE_ATOM),
        ],
    ]);
}

function saveAttendance(array $user): never
{
    $data = requestData();
    $action = (string) ($data['action'] ?? '');
    $now = new DateTimeImmutable();
    $recent = $now->modify('-36 hours')->format('Y-m-d H:i:s');
    $latest = fetchOne(
        'SELECT * FROM absens WHERE user_id = ? AND created_at >= ? ORDER BY id DESC LIMIT 1',
        [(int) $user['id'], $recent]
    );

    if ($action === 'clock_in') {
        $shift = strtolower(trim((string) ($data['shift'] ?? '')));
        $shifts = shiftDefinitions();
        if (!isset($shifts[$shift])) {
            throw new ApiException('Shift wajib dipilih.', 422, ['shift' => ['Pilih shift yang tersedia.']]);
        }
        if ($latest && empty($latest['foto_pulang'])) {
            throw new ApiException('Absensi masuk sebelumnya belum ditutup.', 409);
        }

        $photo = saveDataImage((string) ($data['photo'] ?? ''), 'absensi', 'absensi_' . safeFileName((string) $user['name']));
        [$startTime, $endTime] = $shifts[$shift]['times'];
        $plannedStart = new DateTimeImmutable($now->format('Y-m-d') . ' ' . $startTime . ':00');
        $plannedEnd = new DateTimeImmutable($now->format('Y-m-d') . ' ' . $endTime . ':00');
        if ($plannedEnd <= $plannedStart) {
            $plannedEnd = $plannedEnd->modify('+1 day');
        }
        $late = $now > $plannedStart ? formatDuration($plannedStart, $now) : null;

        executeStatement(
            'INSERT INTO absens (user_id, status_shift, jam_masuk_shift, jam_pulang_shift, foto_masuk, jam_masuk, foto_pulang, jam_pulang, telat, pulang_awal, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NULL, NULL, ?, NULL, ?, ?)',
            [
                (int) $user['id'],
                $shift,
                $plannedStart->format('Y-m-d H:i:s'),
                $plannedEnd->format('Y-m-d H:i:s'),
                $photo,
                $now->format('Y-m-d H:i:s'),
                $late,
                $now->format('Y-m-d H:i:s'),
                $now->format('Y-m-d H:i:s'),
            ]
        );

        jsonResponse(['success' => true, 'message' => 'Absen masuk berhasil disimpan.'], 201);
    }

    if ($action === 'clock_out') {
        if (!$latest || !empty($latest['foto_pulang'])) {
            throw new ApiException('Tidak ada absensi masuk yang perlu ditutup.', 409);
        }

        $photo = saveDataImage((string) ($data['photo'] ?? ''), 'absensi', 'pulang_' . safeFileName((string) $user['name']));
        $plannedEnd = new DateTimeImmutable((string) $latest['jam_pulang_shift']);
        $early = $now < $plannedEnd ? formatDuration($now, $plannedEnd) : null;

        executeStatement(
            'UPDATE absens SET foto_pulang = ?, jam_pulang = ?, pulang_awal = ?, updated_at = ? WHERE id = ?',
            [$photo, $now->format('Y-m-d H:i:s'), $early, $now->format('Y-m-d H:i:s'), (int) $latest['id']]
        );

        jsonResponse(['success' => true, 'message' => 'Absen pulang berhasil disimpan.']);
    }

    throw new ApiException('Aksi absensi tidak dikenal.', 422);
}

function updateProfile(array $user): never
{
    $data = requestData();
    $name = trim((string) ($data['name'] ?? $user['name']));
    $email = strtolower(trim((string) ($data['email'] ?? $user['email'])));
    $phone = trim((string) ($data['nomor_hp'] ?? ($user['nomor_hp'] ?? '')));
    $errors = [];

    if (mb_strlen($name) < 3) {
        $errors['name'] = ['Nama minimal 3 karakter.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = ['Email tidak valid.'];
    }
    $duplicate = fetchOne('SELECT id FROM users WHERE LOWER(email) = ? AND id <> ?', [$email, (int) $user['id']]);
    if ($duplicate) {
        $errors['email'] = ['Email sudah digunakan.'];
    }
    if ($errors) {
        throw new ApiException('Data profil belum benar.', 422, $errors);
    }

    $photo = $user['foto'] ?? null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $newPhoto = saveUploadedImage($_FILES['foto'], 'photos', 'profil_' . safeFileName($name));
        deleteStoredFile((string) $photo);
        $photo = $newPhoto;
    }

    $passwordSql = '';
    $emailChanged = strcasecmp($email, (string) $user['email']) !== 0;
    $verificationSql = $emailChanged ? ', email_verified_at = NULL' : '';
    $params = [$name, $email, $phone, $photo, nowString()];
    $newPassword = (string) ($data['password'] ?? '');
    if ($newPassword !== '') {
        if (mb_strlen($newPassword) < 6) {
            throw new ApiException('Password baru minimal 6 karakter.', 422, ['password' => ['Password minimal 6 karakter.']]);
        }
        $currentPassword = (string) ($data['current_password'] ?? '');
        if (!password_verify($currentPassword, (string) $user['password'])) {
            throw new ApiException('Password saat ini tidak benar.', 422, ['current_password' => ['Password saat ini tidak benar.']]);
        }
        $passwordSql = ', password = ?';
        $params[] = password_hash($newPassword, PASSWORD_BCRYPT);
    }
    $params[] = (int) $user['id'];

    executeStatement("UPDATE users SET name = ?, email = ?, nomor_hp = ?, foto = ?, updated_at = ?{$passwordSql}{$verificationSql} WHERE id = ?", $params);
    $updated = fetchOne('SELECT * FROM users WHERE id = ?', [(int) $user['id']]);
    $response = ['success' => true, 'message' => 'Profil berhasil diperbarui.', 'data' => publicUser($updated)];
    if ($emailChanged) {
        $verificationUrl = issueEmailVerification($updated);
        $response['message'] = 'Profil diperbarui. Silakan verifikasi alamat email baru.';
        if (filter_var(envValue('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL)) {
            $response['debug_verification_url'] = $verificationUrl;
        }
    }
    jsonResponse($response);
}

function accessDashboard(array $currentUser): never
{
    ensureDefaultAccessData($currentUser);
    $roles = fetchAll("SELECT id, name, guard_name FROM roles WHERE guard_name = 'web' ORDER BY name");
    $permissions = fetchAll("SELECT id, name, guard_name FROM permissions WHERE guard_name = 'web' ORDER BY name");
    $rolePermissions = fetchAll('SELECT role_id, permission_id FROM role_has_permissions');
    $userRoles = fetchAll("SELECT model_id AS user_id, role_id FROM model_has_roles WHERE model_type = 'App\\Models\\User'");
    $userPermissions = fetchAll("SELECT model_id AS user_id, permission_id FROM model_has_permissions WHERE model_type = 'App\\Models\\User'");
    $users = fetchAll('SELECT id, name, email, jabatan, foto FROM users ORDER BY name');

    $permissionMap = [];
    foreach ($rolePermissions as $row) {
        $permissionMap[(int) $row['role_id']][] = (int) $row['permission_id'];
    }
    foreach ($roles as &$role) {
        $role['permission_ids'] = $permissionMap[(int) $role['id']] ?? [];
    }
    $roleMap = [];
    foreach ($userRoles as $row) {
        $roleMap[(int) $row['user_id']][] = (int) $row['role_id'];
    }
    $directMap = [];
    foreach ($userPermissions as $row) {
        $directMap[(int) $row['user_id']][] = (int) $row['permission_id'];
    }
    foreach ($users as &$item) {
        $item['role_ids'] = $roleMap[(int) $item['id']] ?? [];
        $item['permission_ids'] = $directMap[(int) $item['id']] ?? [];
    }
    jsonResponse(['success' => true, 'data' => ['roles' => $roles, 'permissions' => $permissions, 'users' => $users]]);
}

function assignUserAccess(): never
{
    $data = requestData();
    $userId = (int) ($data['user_id'] ?? 0);
    if (!fetchOne('SELECT id FROM users WHERE id = ?', [$userId])) {
        throw new ApiException('Pengguna tidak ditemukan.', 422);
    }
    $roleIds = normalizeIds($data['role_ids'] ?? []);
    $permissionIds = normalizeIds($data['permission_ids'] ?? []);
    validateAccessIds('roles', $roleIds);
    validateAccessIds('permissions', $permissionIds);

    $pdo = database();
    $pdo->beginTransaction();
    try {
        executeStatement("DELETE FROM model_has_roles WHERE model_type = 'App\\Models\\User' AND model_id = ?", [$userId]);
        foreach ($roleIds as $roleId) {
            executeStatement("INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES (?, 'App\\Models\\User', ?)", [$roleId, $userId]);
        }
        executeStatement("DELETE FROM model_has_permissions WHERE model_type = 'App\\Models\\User' AND model_id = ?", [$userId]);
        foreach ($permissionIds as $permissionId) {
            executeStatement("INSERT INTO model_has_permissions (permission_id, model_type, model_id) VALUES (?, 'App\\Models\\User', ?)", [$permissionId, $userId]);
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
    jsonResponse(['success' => true, 'message' => 'Hak akses pengguna berhasil diperbarui.']);
}

function createRole(): never
{
    $name = trim((string) (requestData()['name'] ?? ''));
    if ($name === '' || mb_strlen($name) > 100) {
        throw new ApiException('Nama role wajib diisi dan maksimal 100 karakter.', 422);
    }
    if (fetchOne("SELECT id FROM roles WHERE LOWER(name) = LOWER(?) AND guard_name = 'web'", [$name])) {
        throw new ApiException('Nama role sudah digunakan.', 409);
    }
    executeStatement('INSERT INTO roles (name, guard_name, created_at, updated_at) VALUES (?, ?, ?, ?)', [$name, 'web', nowString(), nowString()]);
    jsonResponse(['success' => true, 'message' => 'Role berhasil ditambahkan.'], 201);
}

function updateRole(int $id): never
{
    $name = trim((string) (requestData()['name'] ?? ''));
    if ($name === '' || mb_strlen($name) > 100) {
        throw new ApiException('Nama role wajib diisi dan maksimal 100 karakter.', 422);
    }
    if (!fetchOne('SELECT id FROM roles WHERE id = ?', [$id])) {
        throw new ApiException('Role tidak ditemukan.', 404);
    }
    if (fetchOne("SELECT id FROM roles WHERE LOWER(name) = LOWER(?) AND guard_name = 'web' AND id <> ?", [$name, $id])) {
        throw new ApiException('Nama role sudah digunakan.', 409);
    }
    executeStatement('UPDATE roles SET name = ?, updated_at = ? WHERE id = ?', [$name, nowString(), $id]);
    jsonResponse(['success' => true, 'message' => 'Role berhasil diperbarui.']);
}

function deleteRole(int $id): never
{
    $role = fetchOne('SELECT id, name FROM roles WHERE id = ?', [$id]);
    if (!$role) {
        throw new ApiException('Role tidak ditemukan.', 404);
    }
    if (in_array(strtolower((string) $role['name']), ['super-admin', 'manajemen'], true)) {
        throw new ApiException('Role sistem utama tidak dapat dihapus.', 409);
    }
    $pdo = database();
    $pdo->beginTransaction();
    try {
        executeStatement('DELETE FROM model_has_roles WHERE role_id = ?', [$id]);
        executeStatement('DELETE FROM role_has_permissions WHERE role_id = ?', [$id]);
        executeStatement('DELETE FROM roles WHERE id = ?', [$id]);
        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
    jsonResponse(['success' => true, 'message' => 'Role berhasil dihapus.']);
}

function syncRolePermissions(int $roleId): never
{
    if (!fetchOne('SELECT id FROM roles WHERE id = ?', [$roleId])) {
        throw new ApiException('Role tidak ditemukan.', 404);
    }
    $permissionIds = normalizeIds(requestData()['permission_ids'] ?? []);
    validateAccessIds('permissions', $permissionIds);
    $pdo = database();
    $pdo->beginTransaction();
    try {
        executeStatement('DELETE FROM role_has_permissions WHERE role_id = ?', [$roleId]);
        foreach ($permissionIds as $permissionId) {
            executeStatement('INSERT INTO role_has_permissions (permission_id, role_id) VALUES (?, ?)', [$permissionId, $roleId]);
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
    jsonResponse(['success' => true, 'message' => 'Permission role berhasil diperbarui.']);
}

function createPermission(): never
{
    $name = trim((string) (requestData()['name'] ?? ''));
    if ($name === '' || mb_strlen($name) > 100) {
        throw new ApiException('Nama permission wajib diisi dan maksimal 100 karakter.', 422);
    }
    if (fetchOne("SELECT id FROM permissions WHERE LOWER(name) = LOWER(?) AND guard_name = 'web'", [$name])) {
        throw new ApiException('Nama permission sudah digunakan.', 409);
    }
    executeStatement('INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES (?, ?, ?, ?)', [$name, 'web', nowString(), nowString()]);
    jsonResponse(['success' => true, 'message' => 'Permission berhasil ditambahkan.'], 201);
}

function updatePermission(int $id): never
{
    $name = trim((string) (requestData()['name'] ?? ''));
    if ($name === '' || mb_strlen($name) > 100) {
        throw new ApiException('Nama permission wajib diisi dan maksimal 100 karakter.', 422);
    }
    if (!fetchOne('SELECT id FROM permissions WHERE id = ?', [$id])) {
        throw new ApiException('Permission tidak ditemukan.', 404);
    }
    if (fetchOne("SELECT id FROM permissions WHERE LOWER(name) = LOWER(?) AND guard_name = 'web' AND id <> ?", [$name, $id])) {
        throw new ApiException('Nama permission sudah digunakan.', 409);
    }
    executeStatement('UPDATE permissions SET name = ?, updated_at = ? WHERE id = ?', [$name, nowString(), $id]);
    jsonResponse(['success' => true, 'message' => 'Permission berhasil diperbarui.']);
}

function deletePermission(int $id): never
{
    if (!fetchOne('SELECT id FROM permissions WHERE id = ?', [$id])) {
        throw new ApiException('Permission tidak ditemukan.', 404);
    }
    $pdo = database();
    $pdo->beginTransaction();
    try {
        executeStatement('DELETE FROM model_has_permissions WHERE permission_id = ?', [$id]);
        executeStatement('DELETE FROM role_has_permissions WHERE permission_id = ?', [$id]);
        executeStatement('DELETE FROM permissions WHERE id = ?', [$id]);
        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
    jsonResponse(['success' => true, 'message' => 'Permission berhasil dihapus.']);
}

function ensureDefaultAccessData(array $currentUser): void
{
    $permissionNames = ['manage-users', 'manage-schedules', 'manage-logbooks', 'view-all-reports', 'manage-referrals', 'manage-access'];
    foreach ($permissionNames as $name) {
        if (!fetchOne("SELECT id FROM permissions WHERE name = ? AND guard_name = 'web'", [$name])) {
            executeStatement('INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES (?, ?, ?, ?)', [$name, 'web', nowString(), nowString()]);
        }
    }
    foreach (['super-admin', 'manajemen', 'pegawai'] as $name) {
        if (!fetchOne("SELECT id FROM roles WHERE name = ? AND guard_name = 'web'", [$name])) {
            executeStatement('INSERT INTO roles (name, guard_name, created_at, updated_at) VALUES (?, ?, ?, ?)', [$name, 'web', nowString(), nowString()]);
        }
    }
    $permissions = array_map('intval', array_column(fetchAll("SELECT id FROM permissions WHERE name IN ('manage-users','manage-schedules','manage-logbooks','view-all-reports','manage-referrals','manage-access')"), 'id'));
    foreach (['super-admin', 'manajemen'] as $roleName) {
        $role = fetchOne("SELECT id FROM roles WHERE name = ? AND guard_name = 'web'", [$roleName]);
        foreach ($permissions as $permissionId) {
            if (!fetchOne('SELECT role_id FROM role_has_permissions WHERE role_id = ? AND permission_id = ?', [(int) $role['id'], $permissionId])) {
                executeStatement('INSERT INTO role_has_permissions (permission_id, role_id) VALUES (?, ?)', [$permissionId, (int) $role['id']]);
            }
        }
    }
    $assigned = fetchOne("SELECT role_id FROM model_has_roles WHERE model_type = 'App\\Models\\User' AND model_id = ?", [(int) $currentUser['id']]);
    if (!$assigned && strtolower(trim((string) ($currentUser['jabatan'] ?? ''))) === 'manajemen') {
        $managerRole = fetchOne("SELECT id FROM roles WHERE name = 'manajemen' AND guard_name = 'web'");
        executeStatement("INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES (?, 'App\\Models\\User', ?)", [(int) $managerRole['id'], (int) $currentUser['id']]);
    }
}

function normalizeIds(mixed $value): array
{
    if (is_string($value)) {
        $decoded = json_decode($value, true);
        $value = is_array($decoded) ? $decoded : [];
    }
    if (!is_array($value)) {
        return [];
    }
    return array_values(array_unique(array_filter(array_map('intval', $value), static fn (int $id): bool => $id > 0)));
}

function validateAccessIds(string $table, array $ids): void
{
    if (!$ids) {
        return;
    }
    if (!in_array($table, ['roles', 'permissions'], true)) {
        throw new ApiException('Jenis hak akses tidak valid.', 422);
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $count = (int) fetchValue("SELECT COUNT(*) FROM {$table} WHERE id IN ({$placeholders})", $ids);
    if ($count !== count($ids)) {
        throw new ApiException('Terdapat hak akses yang tidak valid.', 422);
    }
}

function userDirectory(): never
{
    $search = trim((string) ($_GET['search'] ?? ''));
    $params = [];
    $where = '';
    if ($search !== '') {
        $where = ' WHERE name LIKE ? OR email LIKE ? OR jabatan LIKE ?';
        $term = '%' . $search . '%';
        $params = [$term, $term, $term];
    }
    $users = fetchAll('SELECT id, name, email, jabatan, foto FROM users' . $where . ' ORDER BY name LIMIT 500', $params);
    jsonResponse(['success' => true, 'data' => $users]);
}

function listUsers(): never
{
    $search = trim((string) ($_GET['search'] ?? ''));
    $position = trim((string) ($_GET['jabatan'] ?? ''));
    [$page, $perPage, $offset] = paginationInput();
    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = '(name LIKE ? OR email LIKE ? OR jabatan LIKE ?)';
        $term = '%' . $search . '%';
        array_push($params, $term, $term, $term);
    }
    if ($position !== '') {
        $where[] = 'jabatan = ?';
        $params[] = $position;
    }
    $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
    $total = (int) fetchValue('SELECT COUNT(*) FROM users' . $whereSql, $params);
    $users = fetchAll(
        'SELECT id, name, email, jabatan, foto, nomor_hp, jadwal, created_at, updated_at FROM users' . $whereSql . " ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}",
        $params
    );
    $positions = fetchAll("SELECT DISTINCT jabatan FROM users WHERE jabatan IS NOT NULL AND jabatan <> '' ORDER BY jabatan");

    jsonResponse([
        'success' => true,
        'data' => $users,
        'meta' => paginationMeta($total, $page, $perPage),
        'positions' => array_column($positions, 'jabatan'),
    ]);
}

function showUser(int $id): never
{
    $user = fetchOne('SELECT id, name, email, jabatan, foto, nomor_hp, jadwal, created_at, updated_at FROM users WHERE id = ?', [$id]);
    if (!$user) {
        throw new ApiException('User tidak ditemukan.', 404);
    }
    jsonResponse(['success' => true, 'data' => $user]);
}

function createUser(): never
{
    $data = requestData();
    $validated = validateUserPayload($data, true, null);
    $photo = isset($_FILES['foto']) ? saveUploadedImage($_FILES['foto'], 'photos', 'profil_' . safeFileName($validated['name'])) : null;
    $now = nowString();

    executeStatement(
        'INSERT INTO users (name, email, jabatan, foto, nomor_hp, jadwal, password, email_verified_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [
            $validated['name'], $validated['email'], $validated['jabatan'], $photo,
            $validated['nomor_hp'], $validated['jadwal'], password_hash($validated['password'], PASSWORD_BCRYPT),
            $now, $now, $now,
        ]
    );

    jsonResponse(['success' => true, 'message' => 'User berhasil ditambahkan.'], 201);
}

function updateUser(int $id): never
{
    $existing = fetchOne('SELECT * FROM users WHERE id = ?', [$id]);
    if (!$existing) {
        throw new ApiException('User tidak ditemukan.', 404);
    }
    $data = requestData();
    $validated = validateUserPayload($data, false, $id, $existing);
    $photo = $existing['foto'] ?? null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $newPhoto = saveUploadedImage($_FILES['foto'], 'photos', 'profil_' . safeFileName($validated['name']));
        deleteStoredFile((string) $photo);
        $photo = $newPhoto;
    }

    $sql = 'UPDATE users SET name = ?, email = ?, jabatan = ?, foto = ?, nomor_hp = ?, jadwal = ?, updated_at = ?';
    $params = [$validated['name'], $validated['email'], $validated['jabatan'], $photo, $validated['nomor_hp'], $validated['jadwal'], nowString()];
    if ($validated['password'] !== '') {
        $sql .= ', password = ?';
        $params[] = password_hash($validated['password'], PASSWORD_BCRYPT);
    }
    $sql .= ' WHERE id = ?';
    $params[] = $id;
    executeStatement($sql, $params);

    jsonResponse(['success' => true, 'message' => 'User berhasil diperbarui.']);
}

function deleteUser(int $id, int $currentUserId): never
{
    if ($id === $currentUserId) {
        throw new ApiException('Akun yang sedang dipakai tidak dapat dihapus.', 409);
    }
    $user = fetchOne('SELECT id, foto FROM users WHERE id = ?', [$id]);
    if (!$user) {
        throw new ApiException('User tidak ditemukan.', 404);
    }

    $references =
        (int) fetchValue('SELECT COUNT(*) FROM rujukan_user WHERE user_id = ?', [$id]) +
        (int) fetchValue('SELECT COUNT(*) FROM absens WHERE user_id = ?', [$id]) +
        (int) fetchValue('SELECT COUNT(*) FROM logbooks WHERE user_id = ?', [$id]) +
        (int) fetchValue('SELECT COUNT(*) FROM schedules WHERE user_id = ?', [$id]);
    if ($references > 0) {
        throw new ApiException('User masih memiliki data absensi, logbook, jadwal, atau perjalanan dinas sehingga tidak dapat dihapus.', 409);
    }

    executeStatement('DELETE FROM api_tokens WHERE user_id = ?', [$id]);
    executeStatement('DELETE FROM users WHERE id = ?', [$id]);
    deleteStoredFile((string) ($user['foto'] ?? ''));
    jsonResponse(['success' => true, 'message' => 'User berhasil dihapus.']);
}

function listSchedules(array $user): never
{
    [$year, $month] = monthInput();
    $start = sprintf('%04d-%02d-01', $year, $month);
    $end = (new DateTimeImmutable($start))->modify('+1 month')->format('Y-m-d');
    $params = [];
    $scope = '';
    $canManage = hasPermission($user, 'manage-schedules');
    if (!$canManage) {
        $scope = ' WHERE id = ?';
        $params[] = (int) $user['id'];
    }
    $users = fetchAll('SELECT id, name, jabatan, foto FROM users' . $scope . ' ORDER BY name', $params);
    $userIds = array_map(static fn (array $item): int => (int) $item['id'], $users);
    $schedules = [];
    if ($userIds) {
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $schedules = fetchAll(
            "SELECT id, user_id, tanggal_masuk, tanggal_pulang, status FROM schedules WHERE tanggal_masuk >= ? AND tanggal_masuk < ? AND user_id IN ({$placeholders}) ORDER BY tanggal_masuk",
            array_merge([$start, $end], $userIds)
        );
    }

    try {
        $types = fetchAll('SELECT id, name, masuk, keluar FROM user_types ORDER BY name');
    } catch (PDOException) {
        $types = [];
    }
    if (!$types) {
        $types = array_map(
            static fn (string $key, array $item): array => ['id' => $key, 'name' => $item['label'], 'masuk' => $item['times'][0], 'keluar' => $item['times'][1]],
            array_keys(shiftDefinitions()),
            array_values(shiftDefinitions())
        );
    }

    jsonResponse([
        'success' => true,
        'data' => [
            'users' => $users,
            'schedules' => $schedules,
            'types' => $types,
            'year' => $year,
            'month' => $month,
            'days_in_month' => (int) (new DateTimeImmutable($start))->format('t'),
            'can_manage' => $canManage,
        ],
    ]);
}

function saveSchedule(): never
{
    $data = requestData();
    $userId = (int) ($data['user_id'] ?? 0);
    $date = (string) ($data['date'] ?? '');
    $status = trim((string) ($data['status'] ?? ''));

    if (!fetchOne('SELECT id FROM users WHERE id = ?', [$userId])) {
        throw new ApiException('User tidak ditemukan.', 422);
    }
    if (!validDate($date)) {
        throw new ApiException('Tanggal jadwal tidak valid.', 422);
    }
    if (mb_strlen($status) > 50) {
        throw new ApiException('Status jadwal terlalu panjang.', 422);
    }
    $existing = fetchOne('SELECT id FROM schedules WHERE user_id = ? AND tanggal_masuk = ?', [$userId, $date]);
    if ($status === '') {
        if ($existing) {
            executeStatement('DELETE FROM schedules WHERE id = ?', [(int) $existing['id']]);
        }
        jsonResponse(['success' => true, 'message' => 'Jadwal berhasil dikosongkan.']);
    }
    $overnight = str_contains(strtolower($status), 'malam');
    $endDate = $overnight ? (new DateTimeImmutable($date))->modify('+1 day')->format('Y-m-d') : $date;

    if ($existing) {
        executeStatement('UPDATE schedules SET tanggal_pulang = ?, status = ?, updated_at = ? WHERE id = ?', [$endDate, $status, nowString(), (int) $existing['id']]);
    } else {
        executeStatement('INSERT INTO schedules (user_id, tanggal_masuk, tanggal_pulang, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)', [$userId, $date, $endDate, $status, nowString(), nowString()]);
    }

    jsonResponse(['success' => true, 'message' => 'Jadwal berhasil disimpan.']);
}

function listLogbooks(array $user): never
{
    [$year, $month] = monthInput();
    [$page, $perPage, $offset] = paginationInput(24);
    $search = trim((string) ($_GET['search'] ?? ''));
    $start = sprintf('%04d-%02d-01', $year, $month);
    $end = (new DateTimeImmutable($start))->modify('+1 month')->format('Y-m-d');
    $where = ['l.tanggal >= ?', 'l.tanggal < ?'];
    $params = [$start, $end];
    $canManage = hasPermission($user, 'manage-logbooks');
    $canViewAll = $canManage || hasPermission($user, 'view-all-reports');

    if (!$canViewAll) {
        $where[] = 'l.user_id = ?';
        $params[] = (int) $user['id'];
    }
    if ($search !== '') {
        $term = '%' . $search . '%';
        $where[] = '(l.name LIKE ? OR l.keterangan LIKE ? OR u.name LIKE ?)';
        array_push($params, $term, $term, $term);
    }
    $whereSql = ' WHERE ' . implode(' AND ', $where);
    $total = (int) fetchValue('SELECT COUNT(*) FROM logbooks l JOIN users u ON u.id = l.user_id' . $whereSql, $params);
    $items = fetchAll(
        'SELECT l.*, u.name AS user_name, u.jabatan AS user_jabatan, u.foto AS user_foto FROM logbooks l JOIN users u ON u.id = l.user_id' . $whereSql . " ORDER BY l.tanggal DESC, l.id DESC LIMIT {$perPage} OFFSET {$offset}",
        $params
    );

    jsonResponse(['success' => true, 'data' => $items, 'meta' => paginationMeta($total, $page, $perPage), 'can_manage' => $canManage]);
}

function createLogbook(array $user): never
{
    $data = requestData();
    $name = trim((string) ($data['name'] ?? ''));
    $description = trim((string) ($data['keterangan'] ?? ''));
    $date = (string) ($data['tanggal'] ?? date('Y-m-d'));
    $errors = [];
    if (mb_strlen($name) < 3) {
        $errors['name'] = ['Nama kegiatan minimal 3 karakter.'];
    }
    if ($description === '') {
        $errors['keterangan'] = ['Keterangan wajib diisi.'];
    }
    if (!validDate($date)) {
        $errors['tanggal'] = ['Tanggal tidak valid.'];
    }
    if (!isset($_FILES['foto'])) {
        $errors['foto'] = ['Foto kegiatan wajib dipilih.'];
    }
    if ($errors) {
        throw new ApiException('Data logbook belum benar.', 422, $errors);
    }
    $photo = saveUploadedImage($_FILES['foto'], 'logbook', 'logbook_' . safeFileName((string) $user['name']));
    executeStatement(
        'INSERT INTO logbooks (user_id, name, foto, keterangan, tanggal, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [(int) $user['id'], $name, $photo, $description, $date, nowString(), nowString()]
    );
    jsonResponse(['success' => true, 'message' => 'Logbook berhasil disimpan.'], 201);
}

function updateLogbook(array $user, int $id): never
{
    $item = fetchOne('SELECT * FROM logbooks WHERE id = ?', [$id]);
    if (!$item) {
        throw new ApiException('Logbook tidak ditemukan.', 404);
    }
    if (!hasPermission($user, 'manage-logbooks') && (int) $item['user_id'] !== (int) $user['id']) {
        throw new ApiException('Anda tidak berhak mengubah logbook ini.', 403);
    }
    $data = requestData();
    $name = trim((string) ($data['name'] ?? $item['name']));
    $description = trim((string) ($data['keterangan'] ?? $item['keterangan']));
    $date = (string) ($data['tanggal'] ?? $item['tanggal']);
    if (mb_strlen($name) < 3 || $description === '' || !validDate($date)) {
        throw new ApiException('Data logbook belum benar.', 422);
    }
    $photo = $item['foto'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $newPhoto = saveUploadedImage($_FILES['foto'], 'logbook', 'logbook_' . safeFileName((string) $user['name']));
        deleteStoredFile((string) $photo);
        $photo = $newPhoto;
    }
    executeStatement('UPDATE logbooks SET name = ?, foto = ?, keterangan = ?, tanggal = ?, updated_at = ? WHERE id = ?', [$name, $photo, $description, $date, nowString(), $id]);
    jsonResponse(['success' => true, 'message' => 'Logbook berhasil diperbarui.']);
}

function deleteLogbook(array $user, int $id): never
{
    $item = fetchOne('SELECT * FROM logbooks WHERE id = ?', [$id]);
    if (!$item) {
        throw new ApiException('Logbook tidak ditemukan.', 404);
    }
    if (!hasPermission($user, 'manage-logbooks') && (int) $item['user_id'] !== (int) $user['id']) {
        throw new ApiException('Anda tidak berhak menghapus logbook ini.', 403);
    }
    executeStatement('DELETE FROM logbooks WHERE id = ?', [$id]);
    deleteStoredFile((string) $item['foto']);
    jsonResponse(['success' => true, 'message' => 'Logbook berhasil dihapus.']);
}

function attendanceReport(array $user): never
{
    [$year, $month] = monthInput();
    $search = trim((string) ($_GET['search'] ?? ''));
    $start = sprintf('%04d-%02d-01', $year, $month);
    $end = (new DateTimeImmutable($start))->modify('+1 month')->format('Y-m-d');
    $where = [];
    $params = [];
    if (!hasPermission($user, 'view-all-reports')) {
        $where[] = 'id = ?';
        $params[] = (int) $user['id'];
    }
    if ($search !== '') {
        $where[] = '(name LIKE ? OR jabatan LIKE ?)';
        $term = '%' . $search . '%';
        array_push($params, $term, $term);
    }
    $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
    $users = fetchAll('SELECT id, name, jabatan, foto FROM users' . $whereSql . ' ORDER BY name', $params);
    $ids = array_map(static fn (array $item): int => (int) $item['id'], $users);
    $attendance = [];
    $schedules = [];
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $attendance = fetchAll(
            "SELECT id, user_id, status_shift, foto_masuk, jam_masuk, foto_pulang, jam_pulang, telat, pulang_awal, created_at FROM absens WHERE created_at >= ? AND created_at < ? AND user_id IN ({$placeholders}) ORDER BY created_at",
            array_merge([$start . ' 00:00:00', $end . ' 00:00:00'], $ids)
        );
        $schedules = fetchAll(
            "SELECT id, user_id, tanggal_masuk, tanggal_pulang, status FROM schedules WHERE tanggal_masuk >= ? AND tanggal_masuk < ? AND user_id IN ({$placeholders}) ORDER BY tanggal_masuk",
            array_merge([$start, $end], $ids)
        );
    }
    jsonResponse([
        'success' => true,
        'data' => [
            'users' => $users,
            'attendance' => $attendance,
            'schedules' => $schedules,
            'year' => $year,
            'month' => $month,
            'days_in_month' => (int) (new DateTimeImmutable($start))->format('t'),
        ],
    ]);
}

function logbookReport(array $user): never
{
    [$year, $month] = monthInput();
    $requestedUser = (int) ($_GET['user_id'] ?? 0);
    $start = sprintf('%04d-%02d-01', $year, $month);
    $end = (new DateTimeImmutable($start))->modify('+1 month')->format('Y-m-d');
    $where = ['l.tanggal >= ?', 'l.tanggal < ?'];
    $params = [$start, $end];

    if (!hasPermission($user, 'view-all-reports')) {
        $where[] = 'l.user_id = ?';
        $params[] = (int) $user['id'];
    } elseif ($requestedUser > 0) {
        $where[] = 'l.user_id = ?';
        $params[] = $requestedUser;
    }

    $items = fetchAll(
        'SELECT l.*, u.name AS user_name, u.jabatan AS user_jabatan, u.foto AS user_foto FROM logbooks l JOIN users u ON u.id = l.user_id WHERE ' . implode(' AND ', $where) . ' ORDER BY u.name, l.tanggal, l.id',
        $params
    );
    $users = [];
    foreach ($items as $item) {
        $id = (int) $item['user_id'];
        if (!isset($users[$id])) {
            $users[$id] = ['id' => $id, 'name' => $item['user_name'], 'jabatan' => $item['user_jabatan'], 'foto' => $item['user_foto']];
        }
    }
    if (!$items) {
        $scopeId = !hasPermission($user, 'view-all-reports') ? (int) $user['id'] : $requestedUser;
        if ($scopeId > 0) {
            $emptyUser = fetchOne('SELECT id, name, jabatan, foto FROM users WHERE id = ?', [$scopeId]);
            if ($emptyUser) {
                $users[(int) $emptyUser['id']] = $emptyUser;
            }
        }
    }

    jsonResponse(['success' => true, 'data' => ['items' => $items, 'users' => array_values($users), 'year' => $year, 'month' => $month]]);
}

function searchPns(): never
{
    $search = trim((string) ($_GET['search'] ?? ''));
    $params = [];
    $where = '';
    if ($search !== '') {
        $where = ' WHERE nama LIKE ? OR nip LIKE ? OR jabatan LIKE ?';
        $term = '%' . $search . '%';
        $params = [$term, $term, $term];
    }
    $items = fetchAll('SELECT id, nama, nip, pangkat_golongan, jabatan FROM pns' . $where . ' ORDER BY nama LIMIT 100', $params);
    jsonResponse(['success' => true, 'data' => $items]);
}

function listReferrals(array $user): never
{
    [$page, $perPage, $offset] = paginationInput(20);
    $search = trim((string) ($_GET['search'] ?? ''));
    $from = trim((string) ($_GET['from_date'] ?? ''));
    $to = trim((string) ($_GET['to_date'] ?? ''));
    $where = [];
    $params = [];

    if (!hasPermission($user, 'manage-referrals')) {
        $where[] = 'EXISTS (SELECT 1 FROM rujukan_user own_ru WHERE own_ru.rujukan_id = r.id AND own_ru.user_id = ?)';
        $params[] = (int) $user['id'];
    }
    if ($search !== '') {
        $term = '%' . $search . '%';
        $where[] = '(r.nama_rujukan LIKE ? OR r.alamat_rujukan LIKE ? OR r.tempat LIKE ? OR EXISTS (SELECT 1 FROM rujukan_user sru LEFT JOIN users su ON su.id = sru.user_id LEFT JOIN pns sp ON sp.id = sru.pns_id WHERE sru.rujukan_id = r.id AND (su.name LIKE ? OR su.jabatan LIKE ? OR sp.nama LIKE ? OR sp.nip LIKE ?)))';
        array_push($params, $term, $term, $term, $term, $term, $term, $term);
    }
    if ($from !== '' && validDate($from)) {
        $where[] = 'r.created_at >= ?';
        $params[] = $from . ' 00:00:00';
    }
    if ($to !== '' && validDate($to)) {
        $where[] = 'r.created_at <= ?';
        $params[] = $to . ' 23:59:59';
    }
    $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
    $total = (int) fetchValue('SELECT COUNT(*) FROM rujukan_models r' . $whereSql, $params);
    $items = fetchAll('SELECT r.* FROM rujukan_models r' . $whereSql . " ORDER BY r.id DESC LIMIT {$perPage} OFFSET {$offset}", $params);
    attachParticipants($items);

    jsonResponse([
        'success' => true,
        'data' => $items,
        'meta' => paginationMeta($total, $page, $perPage),
        'can_manage' => hasPermission($user, 'manage-referrals'),
    ]);
}

function showReferral(array $user, int $id): never
{
    $item = accessibleReferral($user, $id);
    $items = [$item];
    attachParticipants($items);
    jsonResponse(['success' => true, 'data' => $items[0]]);
}

function createReferral(array $user): never
{
    $data = requestData();
    [$payload, $participants] = validateReferralPayload($data);
    if (!hasPermission($user, 'manage-referrals') && !in_array((int) $user['id'], array_column($participants, 'user_id'), true)) {
        $participants[] = ['user_id' => (int) $user['id'], 'pns_id' => null];
    }
    $proof = isset($_FILES['bukti_rujukan']) && $_FILES['bukti_rujukan']['error'] !== UPLOAD_ERR_NO_FILE
        ? saveUploadedImage($_FILES['bukti_rujukan'], 'rujukan', 'bukti_rujukan') : null;
    $receipt = isset($_FILES['kuitansi_bensin']) && $_FILES['kuitansi_bensin']['error'] !== UPLOAD_ERR_NO_FILE
        ? saveUploadedImage($_FILES['kuitansi_bensin'], 'rujukan', 'kuitansi_bensin') : null;
    $number = trim((string) ($data['nomor_surat'] ?? '')) ?: generateLetterNumber();
    $now = nowString();

    $pdo = database();
    $pdo->beginTransaction();
    try {
        executeStatement(
            'INSERT INTO rujukan_models (nama_rujukan, alamat_rujukan, bukti_rujukan, kuitansi_bensin, perihal, dasar_surat, menimbang, nomor_surat, tanggal_surat, waktu, tempat, biaya_perdin, alat_angkut, tanggal_berangkat, tanggal_kembali, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $payload['nama_rujukan'], $payload['alamat_rujukan'], $proof, $receipt, $payload['perihal'], $payload['dasar_surat'], $payload['menimbang'],
                $number, date('Y-m-d'), $payload['waktu'], $payload['tempat'], $payload['biaya_perdin'],
                $payload['alat_angkut'], $payload['tanggal_berangkat'], $payload['tanggal_kembali'], $now, $now,
            ]
        );
        $id = (int) $pdo->lastInsertId();
        syncReferralParticipants($id, $participants);
        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        deleteStoredFile((string) $proof);
        deleteStoredFile((string) $receipt);
        throw $exception;
    }
    jsonResponse(['success' => true, 'message' => 'Perjalanan dinas berhasil dibuat.', 'data' => ['id' => $id]], 201);
}

function updateReferral(array $user, int $id): never
{
    $item = accessibleReferral($user, $id);
    if (!hasPermission($user, 'manage-referrals') && ($item['status'] ?? null) === 'confirmed') {
        throw new ApiException('Perjalanan dinas yang sudah dikonfirmasi tidak dapat diubah.', 409);
    }
    $data = requestData();
    [$payload, $participants] = validateReferralPayload($data);
    if (!hasPermission($user, 'manage-referrals') && !in_array((int) $user['id'], array_column($participants, 'user_id'), true)) {
        $participants[] = ['user_id' => (int) $user['id'], 'pns_id' => null];
    }
    $proof = $item['bukti_rujukan'] ?? null;
    $receipt = $item['kuitansi_bensin'] ?? null;
    $oldProof = null;
    $oldReceipt = null;
    if (isset($_FILES['bukti_rujukan']) && $_FILES['bukti_rujukan']['error'] !== UPLOAD_ERR_NO_FILE) {
        $oldProof = $proof;
        $proof = saveUploadedImage($_FILES['bukti_rujukan'], 'rujukan', 'bukti_rujukan');
    }
    if (isset($_FILES['kuitansi_bensin']) && $_FILES['kuitansi_bensin']['error'] !== UPLOAD_ERR_NO_FILE) {
        $oldReceipt = $receipt;
        $receipt = saveUploadedImage($_FILES['kuitansi_bensin'], 'rujukan', 'kuitansi_bensin');
    }

    $pdo = database();
    $pdo->beginTransaction();
    try {
        executeStatement(
            'UPDATE rujukan_models SET nama_rujukan = ?, alamat_rujukan = ?, bukti_rujukan = ?, kuitansi_bensin = ?, perihal = ?, dasar_surat = ?, menimbang = ?, waktu = ?, tempat = ?, biaya_perdin = ?, alat_angkut = ?, tanggal_berangkat = ?, tanggal_kembali = ?, updated_at = ? WHERE id = ?',
            [
                $payload['nama_rujukan'], $payload['alamat_rujukan'], $proof, $receipt, $payload['perihal'], $payload['dasar_surat'], $payload['menimbang'],
                $payload['waktu'], $payload['tempat'], $payload['biaya_perdin'], $payload['alat_angkut'],
                $payload['tanggal_berangkat'], $payload['tanggal_kembali'], nowString(), $id,
            ]
        );
        syncReferralParticipants($id, $participants);
        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        if ($proof !== ($item['bukti_rujukan'] ?? null)) {
            deleteStoredFile((string) $proof);
        }
        if ($receipt !== ($item['kuitansi_bensin'] ?? null)) {
            deleteStoredFile((string) $receipt);
        }
        throw $exception;
    }
    deleteStoredFile((string) $oldProof);
    deleteStoredFile((string) $oldReceipt);
    jsonResponse(['success' => true, 'message' => 'Perjalanan dinas berhasil diperbarui.']);
}

function deleteReferral(array $user, int $id): never
{
    $item = accessibleReferral($user, $id);
    if (!hasPermission($user, 'manage-referrals') && ($item['status'] ?? null) === 'confirmed') {
        throw new ApiException('Perjalanan dinas yang sudah dikonfirmasi tidak dapat dihapus.', 409);
    }
    $pdo = database();
    $pdo->beginTransaction();
    try {
        executeStatement('DELETE FROM rujukan_user WHERE rujukan_id = ?', [$id]);
        executeStatement('DELETE FROM rujukan_models WHERE id = ?', [$id]);
        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
    deleteStoredFile((string) ($item['bukti_rujukan'] ?? ''));
    deleteStoredFile((string) ($item['kuitansi_bensin'] ?? ''));
    jsonResponse(['success' => true, 'message' => 'Perjalanan dinas berhasil dihapus.']);
}

function confirmReferral(int $id): never
{
    if (!fetchOne('SELECT id FROM rujukan_models WHERE id = ?', [$id])) {
        throw new ApiException('Perjalanan dinas tidak ditemukan.', 404);
    }
    executeStatement("UPDATE rujukan_models SET status = 'confirmed', updated_at = ? WHERE id = ?", [nowString(), $id]);
    jsonResponse(['success' => true, 'message' => 'Perjalanan dinas berhasil dikonfirmasi.']);
}

function validateUserPayload(array $data, bool $passwordRequired, ?int $ignoreId, array $fallback = []): array
{
    $name = trim((string) ($data['name'] ?? ($fallback['name'] ?? '')));
    $email = strtolower(trim((string) ($data['email'] ?? ($fallback['email'] ?? ''))));
    $position = trim((string) ($data['jabatan'] ?? ($fallback['jabatan'] ?? '')));
    $phone = trim((string) ($data['nomor_hp'] ?? ($fallback['nomor_hp'] ?? '')));
    $schedule = trim((string) ($data['jadwal'] ?? ($fallback['jadwal'] ?? '')));
    $password = (string) ($data['password'] ?? '');
    $errors = [];

    if (mb_strlen($name) < 3) {
        $errors['name'] = ['Nama minimal 3 karakter.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = ['Email tidak valid.'];
    }
    if ($position === '') {
        $errors['jabatan'] = ['Jabatan wajib diisi.'];
    }
    if ($phone === '') {
        $errors['nomor_hp'] = ['Nomor HP wajib diisi.'];
    }
    if ($passwordRequired && mb_strlen($password) < 6) {
        $errors['password'] = ['Password minimal 6 karakter.'];
    }
    if (!$passwordRequired && $password !== '' && mb_strlen($password) < 6) {
        $errors['password'] = ['Password minimal 6 karakter.'];
    }
    if ($email !== '') {
        $sql = 'SELECT id FROM users WHERE LOWER(email) = ?';
        $params = [$email];
        if ($ignoreId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }
        if (fetchOne($sql . ' LIMIT 1', $params)) {
            $errors['email'] = ['Email sudah digunakan.'];
        }
    }
    if ($errors) {
        throw new ApiException('Data user belum benar.', 422, $errors);
    }

    return compact('name', 'email', 'position', 'phone', 'schedule', 'password') + [
        'jabatan' => $position,
        'nomor_hp' => $phone,
        'jadwal' => $schedule ?: null,
    ];
}

function validateReferralPayload(array $data): array
{
    $payload = [
        'nama_rujukan' => trim((string) ($data['nama_rujukan'] ?? '')),
        'alamat_rujukan' => trim((string) ($data['alamat_rujukan'] ?? '')),
        'perihal' => trim((string) ($data['perihal'] ?? 'Rujukan Pasien')),
        'dasar_surat' => trim((string) ($data['dasar_surat'] ?? '')) ?: null,
        'menimbang' => trim((string) ($data['menimbang'] ?? '')) ?: null,
        'waktu' => trim((string) ($data['waktu'] ?? '08.00 WIB s.d selesai')),
        'tempat' => trim((string) ($data['tempat'] ?? '')),
        'biaya_perdin' => (int) ($data['biaya_perdin'] ?? 70000),
        'alat_angkut' => trim((string) ($data['alat_angkut'] ?? 'Roda Empat')),
        'tanggal_berangkat' => (string) ($data['tanggal_berangkat'] ?? date('Y-m-d')),
        'tanggal_kembali' => (string) ($data['tanggal_kembali'] ?? date('Y-m-d')),
    ];
    $rawParticipants = $data['participants'] ?? '[]';
    if (is_string($rawParticipants)) {
        $participants = json_decode($rawParticipants, true);
    } else {
        $participants = $rawParticipants;
    }
    $errors = [];
    if ($payload['nama_rujukan'] === '') {
        $errors['nama_rujukan'] = ['Nama tujuan wajib diisi.'];
    }
    if (mb_strlen($payload['alamat_rujukan']) < 6) {
        $errors['alamat_rujukan'] = ['Alamat tujuan minimal 6 karakter.'];
    }
    if ($payload['tempat'] === '') {
        $errors['tempat'] = ['Tempat wajib diisi.'];
    }
    if (!validDate($payload['tanggal_berangkat']) || !validDate($payload['tanggal_kembali'])) {
        $errors['tanggal_berangkat'] = ['Tanggal perjalanan tidak valid.'];
    } elseif ($payload['tanggal_kembali'] < $payload['tanggal_berangkat']) {
        $errors['tanggal_kembali'] = ['Tanggal kembali tidak boleh sebelum tanggal berangkat.'];
    }
    if (!is_array($participants) || !$participants) {
        $errors['participants'] = ['Pilih minimal satu peserta.'];
    }

    $clean = [];
    if (is_array($participants)) {
        foreach ($participants as $participant) {
            $userId = (int) ($participant['user_id'] ?? 0);
            $pnsId = !empty($participant['pns_id']) ? (int) $participant['pns_id'] : null;
            if ($userId <= 0 || !fetchOne('SELECT id FROM users WHERE id = ?', [$userId])) {
                $errors['participants'] = ['Terdapat peserta yang tidak valid.'];
                break;
            }
            if ($pnsId !== null && !fetchOne('SELECT id FROM pns WHERE id = ?', [$pnsId])) {
                $errors['participants'] = ['Data PNS yang dipilih tidak valid.'];
                break;
            }
            $clean[$userId] = ['user_id' => $userId, 'pns_id' => $pnsId];
        }
    }
    if ($errors) {
        throw new ApiException('Data perjalanan dinas belum benar.', 422, $errors);
    }
    return [$payload, array_values($clean)];
}

function syncReferralParticipants(int $referralId, array $participants): void
{
    executeStatement('DELETE FROM rujukan_user WHERE rujukan_id = ?', [$referralId]);
    foreach ($participants as $participant) {
        executeStatement('INSERT INTO rujukan_user (rujukan_id, user_id, pns_id) VALUES (?, ?, ?)', [$referralId, $participant['user_id'], $participant['pns_id']]);
    }
}

function attachParticipants(array &$items): void
{
    if (!$items) {
        return;
    }
    $ids = array_map(static fn (array $item): int => (int) $item['id'], $items);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $rows = fetchAll(
        "SELECT ru.rujukan_id, ru.user_id, ru.pns_id, u.name AS user_name, u.email AS user_email, u.jabatan AS user_jabatan, p.nama AS pns_nama, p.nip AS pns_nip, p.pangkat_golongan, p.jabatan AS pns_jabatan FROM rujukan_user ru JOIN users u ON u.id = ru.user_id LEFT JOIN pns p ON p.id = ru.pns_id WHERE ru.rujukan_id IN ({$placeholders}) ORDER BY u.name",
        $ids
    );
    $grouped = [];
    foreach ($rows as $row) {
        $grouped[(int) $row['rujukan_id']][] = $row;
    }
    foreach ($items as &$item) {
        $item['participants'] = $grouped[(int) $item['id']] ?? [];
    }
}

function accessibleReferral(array $user, int $id): array
{
    $item = fetchOne('SELECT * FROM rujukan_models WHERE id = ?', [$id]);
    if (!$item) {
        throw new ApiException('Perjalanan dinas tidak ditemukan.', 404);
    }
    if (!hasPermission($user, 'manage-referrals')) {
        $linked = fetchOne('SELECT id FROM rujukan_user WHERE rujukan_id = ? AND user_id = ?', [$id, (int) $user['id']]);
        if (!$linked) {
            throw new ApiException('Anda tidak berhak mengakses perjalanan dinas ini.', 403);
        }
    }
    return $item;
}

function generateLetterNumber(): string
{
    $year = date('Y');
    $last = fetchOne('SELECT nomor_surat FROM rujukan_models WHERE nomor_surat LIKE ? ORDER BY id DESC LIMIT 1', ['800.1.11.1/%/' . $year . '/RSUD_Malangbong']);
    $next = 1;
    if ($last && preg_match('/800\.1\.11\.1\/\s*(\d+)\/' . $year . '\/RSUD_Malangbong/', (string) $last['nomor_surat'], $matches)) {
        $next = (int) $matches[1] + 1;
    }
    return sprintf('800.1.11.1/ %03d/%s/RSUD_Malangbong', $next, $year);
}

function shiftDefinitions(): array
{
    return [
        'nonshift' => ['label' => 'Non shift', 'icon' => 'sunrise', 'times' => ['07:30', '16:00']],
        'pagi' => ['label' => 'Pagi', 'icon' => 'sunrise', 'times' => ['07:30', '14:00']],
        'siang' => ['label' => 'Siang', 'icon' => 'sun', 'times' => ['14:00', '20:00']],
        'malam' => ['label' => 'Malam', 'icon' => 'moon', 'times' => ['20:00', '07:30']],
        'pagi-cs' => ['label' => 'Pagi CS', 'icon' => 'sunrise', 'times' => ['06:00', '14:00']],
        'midle-cs' => ['label' => 'Middle CS', 'icon' => 'sun', 'times' => ['14:00', '22:00']],
        'malam-cs' => ['label' => 'Malam CS', 'icon' => 'moon', 'times' => ['22:00', '06:00']],
        'pagi-satpam' => ['label' => 'Pagi Satpam', 'icon' => 'sunrise', 'times' => ['07:00', '19:00']],
        'malam-satpam' => ['label' => 'Malam Satpam', 'icon' => 'moon', 'times' => ['19:00', '07:00']],
        'pagi-secwan' => ['label' => 'Pagi Secwan', 'icon' => 'sunrise', 'times' => ['07:00', '15:00']],
        'sore-secwan' => ['label' => 'Sore Secwan', 'icon' => 'sunset', 'times' => ['14:00', '22:00']],
        'ambulance' => ['label' => 'Ambulance', 'icon' => 'ambulance', 'times' => ['08:00', '08:00']],
    ];
}

function requireUser(): array
{
    ensureTokenTable();
    $token = bearerToken();
    if ($token === '') {
        throw new ApiException('Silakan login terlebih dahulu.', 401);
    }
    $hash = hash('sha256', $token);
    $user = fetchOne(
        'SELECT u.*, t.id AS api_token_id FROM api_tokens t JOIN users u ON u.id = t.user_id WHERE t.token_hash = ? AND t.expires_at > ? LIMIT 1',
        [$hash, nowString()]
    );
    if (!$user) {
        throw new ApiException('Sesi telah berakhir. Silakan login kembali.', 401);
    }
    executeStatement('UPDATE api_tokens SET last_used_at = ? WHERE id = ?', [nowString(), (int) $user['api_token_id']]);
    unset($user['api_token_id']);
    return $user;
}

function requireManager(array $user): void
{
    if (!isManager($user)) {
        throw new ApiException('Fitur ini hanya dapat diakses manajemen.', 403);
    }
}

function hasPermission(array $user, string $permission): bool
{
    return isManager($user) || in_array($permission, userPermissions((int) $user['id']), true);
}

function requirePermission(array $user, string $permission): void
{
    if (!hasPermission($user, $permission)) {
        throw new ApiException('Anda tidak memiliki permission ' . $permission . '.', 403);
    }
}

function userRoles(int $userId): array
{
    try {
        return array_column(fetchAll("SELECT r.name FROM roles r JOIN model_has_roles mr ON mr.role_id = r.id WHERE mr.model_type = 'App\\Models\\User' AND mr.model_id = ? ORDER BY r.name", [$userId]), 'name');
    } catch (PDOException) {
        return [];
    }
}

function userPermissions(int $userId): array
{
    try {
        $direct = array_column(fetchAll("SELECT p.name FROM permissions p JOIN model_has_permissions mp ON mp.permission_id = p.id WHERE mp.model_type = 'App\\Models\\User' AND mp.model_id = ?", [$userId]), 'name');
        $throughRoles = array_column(fetchAll("SELECT DISTINCT p.name FROM permissions p JOIN role_has_permissions rp ON rp.permission_id = p.id JOIN model_has_roles mr ON mr.role_id = rp.role_id WHERE mr.model_type = 'App\\Models\\User' AND mr.model_id = ?", [$userId]), 'name');
        return array_values(array_unique(array_merge($direct, $throughRoles)));
    } catch (PDOException) {
        return [];
    }
}

function isManager(array $user): bool
{
    if (strtolower(trim((string) ($user['jabatan'] ?? ''))) === 'manajemen') {
        return true;
    }
    $roles = array_map('strtolower', userRoles((int) $user['id']));
    return (bool) array_intersect($roles, ['super-admin', 'manajemen', 'admin']);
}

function publicUser(array $user): array
{
    $roles = userRoles((int) $user['id']);
    $permissions = userPermissions((int) $user['id']);
    return [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'jabatan' => $user['jabatan'] ?? null,
        'foto' => $user['foto'] ?? null,
        'nomor_hp' => $user['nomor_hp'] ?? null,
        'jadwal' => $user['jadwal'] ?? null,
        'email_verified' => !empty($user['email_verified_at']),
        'roles' => $roles,
        'permissions' => $permissions,
        'is_manager' => isManager($user),
    ];
}

function ensureTokenTable(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }
    $driver = database()->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        database()->exec('CREATE TABLE IF NOT EXISTS api_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, token_hash VARCHAR(64) NOT NULL UNIQUE, expires_at DATETIME NOT NULL, last_used_at DATETIME NULL, created_at DATETIME NOT NULL)');
        database()->exec('CREATE INDEX IF NOT EXISTS api_tokens_user_id_index ON api_tokens (user_id)');
    } else {
        database()->exec('CREATE TABLE IF NOT EXISTS api_tokens (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL, token_hash VARCHAR(64) NOT NULL UNIQUE, expires_at DATETIME NOT NULL, last_used_at DATETIME NULL, created_at DATETIME NOT NULL, INDEX api_tokens_user_id_index (user_id), INDEX api_tokens_expires_at_index (expires_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }
    executeStatement('DELETE FROM api_tokens WHERE expires_at <= ?', [nowString()]);
    $ready = true;
}

function ensureEmailVerificationTable(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }
    $driver = database()->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        database()->exec('CREATE TABLE IF NOT EXISTS api_email_verifications (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, token_hash VARCHAR(64) NOT NULL UNIQUE, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL)');
        database()->exec('CREATE INDEX IF NOT EXISTS api_email_verifications_user_id_index ON api_email_verifications (user_id)');
    } else {
        database()->exec('CREATE TABLE IF NOT EXISTS api_email_verifications (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL, token_hash VARCHAR(64) NOT NULL UNIQUE, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, INDEX api_email_verifications_user_id_index (user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }
    executeStatement('DELETE FROM api_email_verifications WHERE expires_at <= ?', [nowString()]);
    $ready = true;
}

function frontendUrl(string $path): string
{
    $configured = rtrim(envValue('FRONTEND_URL', ''), '/');
    if ($configured === '') {
        $origin = rtrim((string) ($_SERVER['HTTP_ORIGIN'] ?? ''), '/');
        $configured = $origin !== '' ? $origin : rtrim(envValue('APP_URL', 'http://localhost:5173'), '/');
    }
    return $configured . '/' . ltrim($path, '/');
}

function sendAppMail(string $recipient, string $subject, string $message): bool
{
    $mailer = strtolower(envValue('MAIL_MAILER', 'log'));
    $from = envValue('MAIL_FROM_ADDRESS', 'noreply@localhost');
    $fromName = envValue('MAIL_FROM_NAME', 'SiHadir');
    if ($mailer === 'log') {
        $directory = ROOT_PATH . '/storage/logs';
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        $entry = sprintf("[%s] To: %s | Subject: %s\n%s\n%s\n", date(DATE_ATOM), $recipient, $subject, $message, str_repeat('-', 70));
        return file_put_contents($directory . '/api-mail.log', $entry, FILE_APPEND | LOCK_EX) !== false;
    }
    if (!function_exists('mail')) {
        return false;
    }
    $headers = [
        'From: ' . $fromName . ' <' . $from . '>',
        'Content-Type: text/plain; charset=UTF-8',
        'X-Mailer: SiHadir PHP API',
    ];
    return mail($recipient, $subject, $message, implode("\r\n", $headers));
}

function database(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $connection = strtolower(envValue('DB_CONNECTION', 'sqlite'));
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    if ($connection === 'sqlite') {
        $path = envValue('DB_DATABASE', ROOT_PATH . '/database/database.sqlite');
        if ($path === '' || $path === 'laravel') {
            $path = ROOT_PATH . '/database/database.sqlite';
        }
        if (!str_starts_with($path, '/') && !preg_match('/^[A-Za-z]:[\\\\\/]/', $path)) {
            $path = ROOT_PATH . '/' . ltrim($path, '/');
        }
        $pdo = new PDO('sqlite:' . $path, null, null, $options);
        $pdo->exec('PRAGMA foreign_keys = ON');
        return $pdo;
    }

    if ($connection !== 'mysql' && $connection !== 'mariadb') {
        throw new ApiException('DB_CONNECTION harus sqlite, mysql, atau mariadb.', 503);
    }
    $host = envValue('DB_HOST', '127.0.0.1');
    $port = envValue('DB_PORT', '3306');
    $name = envValue('DB_DATABASE', 'laravel');
    $charset = envValue('DB_CHARSET', 'utf8mb4');
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
    $pdo = new PDO($dsn, envValue('DB_USERNAME', 'root'), envValue('DB_PASSWORD', ''), $options);
    return $pdo;
}

function fetchOne(string $sql, array $params = []): ?array
{
    $statement = database()->prepare($sql);
    $statement->execute($params);
    $row = $statement->fetch();
    return $row === false ? null : $row;
}

function fetchAll(string $sql, array $params = []): array
{
    $statement = database()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
}

function fetchValue(string $sql, array $params = []): mixed
{
    $statement = database()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchColumn();
}

function executeStatement(string $sql, array $params = []): int
{
    $statement = database()->prepare($sql);
    $statement->execute($params);
    return $statement->rowCount();
}

function requestData(): array
{
    static $data = null;
    if (is_array($data)) {
        return $data;
    }
    $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
    if (str_contains($contentType, 'application/json')) {
        $decoded = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($decoded)) {
            throw new ApiException('Body JSON tidak valid.', 400);
        }
        $data = $decoded;
        return $data;
    }
    $data = $_POST;
    return $data;
}

function requestMethod(): string
{
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($method === 'POST' && isset($_POST['_method'])) {
        $override = strtoupper((string) $_POST['_method']);
        if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
            return $override;
        }
    }
    return $method;
}

function bearerToken(): string
{
    $header = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
    if (preg_match('/^Bearer\s+(.+)$/i', trim($header), $matches)) {
        return trim($matches[1]);
    }
    // Elemen <img> tidak dapat mengirim header Authorization. Token query hanya
    // diterima oleh endpoint file dan tetap memiliki masa berlaku yang sama.
    if (trim((string) ($_GET['route'] ?? ''), '/') === 'files' && isset($_GET['token'])) {
        return trim((string) $_GET['token']);
    }
    return '';
}

function saveDataImage(string $value, string $directory, string $prefix): string
{
    if (!preg_match('#^data:image/(jpeg|jpg|png|webp);base64,(.+)$#s', $value, $matches)) {
        throw new ApiException('Foto absensi wajib diambil.', 422, ['photo' => ['Format foto tidak valid.']]);
    }
    $binary = base64_decode(str_replace(' ', '+', $matches[2]), true);
    if ($binary === false || strlen($binary) > MAX_UPLOAD_SIZE) {
        throw new ApiException('Ukuran foto maksimal 10 MB.', 422);
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($binary);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) {
        throw new ApiException('Foto harus berupa JPG, PNG, atau WebP.', 422);
    }
    return writeStoredFile($binary, $directory, $prefix, $extensions[$mime]);
}

function saveUploadedImage(array $file, string $directory, string $prefix): string
{
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK) {
        throw new ApiException(uploadErrorMessage($error), 422);
    }
    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > MAX_UPLOAD_SIZE) {
        throw new ApiException('Ukuran gambar maksimal 10 MB.', 422);
    }
    $temporary = (string) ($file['tmp_name'] ?? '');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporary);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) {
        throw new ApiException('File harus berupa gambar JPG, PNG, atau WebP.', 422);
    }
    $binary = file_get_contents($temporary);
    if ($binary === false) {
        throw new ApiException('File gagal dibaca.', 422);
    }
    return writeStoredFile($binary, $directory, $prefix, $extensions[$mime]);
}

function writeStoredFile(string $binary, string $directory, string $prefix, string $extension): string
{
    $directory = trim($directory, '/');
    $base = ROOT_PATH . '/storage/app/public/' . $directory;
    if (!is_dir($base) && !mkdir($base, 0775, true) && !is_dir($base)) {
        throw new ApiException('Folder penyimpanan tidak dapat dibuat.', 500);
    }
    $name = safeFileName($prefix) . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    if (file_put_contents($base . '/' . $name, $binary, LOCK_EX) === false) {
        throw new ApiException('File gagal disimpan.', 500);
    }
    return $directory . '/' . $name;
}

function serveStoredFile(string $path): never
{
    $path = normalizeStoredPath($path);
    if ($path === '') {
        throw new ApiException('File tidak ditemukan.', 404);
    }
    $base = realpath(ROOT_PATH . '/storage/app/public');
    $file = realpath(ROOT_PATH . '/storage/app/public/' . $path);
    if (!$base || !$file || !str_starts_with($file, $base . DIRECTORY_SEPARATOR) || !is_file($file)) {
        throw new ApiException('File tidak ditemukan.', 404);
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file) ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($file));
    header('Cache-Control: private, max-age=3600');
    readfile($file);
    exit;
}

function deleteStoredFile(string $path): void
{
    $path = normalizeStoredPath($path);
    if ($path === '') {
        return;
    }
    $base = realpath(ROOT_PATH . '/storage/app/public');
    $file = realpath(ROOT_PATH . '/storage/app/public/' . $path);
    if ($base && $file && str_starts_with($file, $base . DIRECTORY_SEPARATOR) && is_file($file)) {
        @unlink($file);
    }
}

function normalizeStoredPath(string $path): string
{
    $path = str_replace('\\', '/', trim($path));
    $path = preg_replace('#^/?storage/#', '', $path) ?? '';
    if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
        return '';
    }
    return ltrim($path, '/');
}

function uploadErrorMessage(int $error): string
{
    return match ($error) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran file terlalu besar.',
        UPLOAD_ERR_PARTIAL => 'File hanya terunggah sebagian.',
        UPLOAD_ERR_NO_FILE => 'File wajib dipilih.',
        default => 'File gagal diunggah.',
    };
}

function safeFileName(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9_-]+/i', '_', $value) ?? 'file';
    return trim($value, '_') ?: 'file';
}

function formatDuration(DateTimeImmutable $from, DateTimeImmutable $to): string
{
    $minutes = max(0, (int) floor(($to->getTimestamp() - $from->getTimestamp()) / 60));
    $hours = intdiv($minutes, 60);
    $remaining = $minutes % 60;
    if ($hours > 0 && $remaining > 0) {
        return "{$hours} jam {$remaining} menit";
    }
    if ($hours > 0) {
        return "{$hours} jam";
    }
    return "{$remaining} menit";
}

function monthInput(): array
{
    $year = (int) ($_GET['year'] ?? date('Y'));
    $month = (int) ($_GET['month'] ?? date('n'));
    if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
        throw new ApiException('Bulan atau tahun tidak valid.', 422);
    }
    return [$year, $month];
}

function validDate(string $date): bool
{
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}

function paginationInput(int $default = 15): array
{
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = min(100, max(1, (int) ($_GET['per_page'] ?? $default)));
    return [$page, $perPage, ($page - 1) * $perPage];
}

function paginationMeta(int $total, int $page, int $perPage): array
{
    return [
        'current_page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'last_page' => max(1, (int) ceil($total / $perPage)),
    ];
}

function nowString(): string
{
    return date('Y-m-d H:i:s');
}

function setCorsHeaders(): void
{
    $origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? '');
    $configured = trim(envValue('API_ALLOWED_ORIGINS', ''));
    $allowed = $configured === '' ? [] : array_map('trim', explode(',', $configured));
    $originHost = (string) parse_url($origin, PHP_URL_HOST);
    $developmentOrigin = in_array($originHost, ['localhost', '127.0.0.1'], true) || str_ends_with($originHost, '.e2b.app');

    if ($origin !== '' && ($configured === '*' || in_array($origin, $allowed, true) || $developmentOrigin)) {
        header('Access-Control-Allow-Origin: ' . ($configured === '*' ? '*' : $origin));
        header('Vary: Origin');
    }
    header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Max-Age: 600');
}

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

function loadEnvironment(string $path): array
{
    if (!is_file($path)) {
        return [];
    }
    $values = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        $values[$key] = $value;
        if (getenv($key) === false) {
            putenv($key . '=' . $value);
        }
    }
    return $values;
}

function envValue(string $key, string $default = ''): string
{
    $value = getenv($key);
    if ($value === false || $value === '' || strtolower($value) === 'null') {
        return $default;
    }
    return (string) $value;
}
