<?php
require_once "db.php";

function authCookieOptions(int $expires): array
{
    return [
        'expires' => $expires,
        'path' => '/',
        'domain' => '',
        'secure' => appIsHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function authRedirect(string $location): void
{
    header("Location: {$location}");
    exit;
}

function authAlertRedirect(string $message, string $location): void
{
    echo "<script>alert(" . json_encode($message) . "); window.location.href = " . json_encode($location) . ";</script>";
    exit;
}

function ensureRememberTokensTable(mysqli $conn): void
{
    static $tableChecked = false;

    if ($tableChecked) {
        return;
    }

    $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    selector CHAR(24) NOT NULL UNIQUE,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_remember_user_id (user_id),
    INDEX idx_remember_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
SQL;

    if (!$conn->query($sql)) {
        die("Remember-me setup failed: " . $conn->error);
    }

    $tableChecked = true;
}

function setLoggedInUserSession(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user'] = $user['email'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_name'] = $user['name'] !== '' ? $user['name'] : $user['username'];
}

function clearRememberMeCookie(): void
{
    setcookie(APP_REMEMBER_ME_COOKIE, '', authCookieOptions(time() - 3600));
    unset($_COOKIE[APP_REMEMBER_ME_COOKIE]);
}

function deleteRememberTokenBySelector(mysqli $conn, string $selector): void
{
    ensureRememberTokensTable($conn);

    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE selector = ?");
    if (!$stmt) {
        return;
    }

    $stmt->bind_param("s", $selector);
    $stmt->execute();
    $stmt->close();
}

function deleteRememberTokensByUserId(mysqli $conn, int $userId): void
{
    ensureRememberTokensTable($conn);

    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
    if (!$stmt) {
        return;
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}

function forgetRememberedLogin(mysqli $conn, ?int $userId = null): void
{
    $cookieValue = $_COOKIE[APP_REMEMBER_ME_COOKIE] ?? '';

    if ($cookieValue !== '') {
        $parts = explode(':', $cookieValue, 2);
        if (count($parts) === 2 && ctype_xdigit($parts[0])) {
            deleteRememberTokenBySelector($conn, $parts[0]);
        }
    }

    if ($userId !== null) {
        deleteRememberTokensByUserId($conn, $userId);
    }

    clearRememberMeCookie();
}

function createRememberMeToken(mysqli $conn, int $userId): void
{
    ensureRememberTokensTable($conn);
    deleteRememberTokensByUserId($conn, $userId);

    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $validator);
    $expiresAt = date('Y-m-d H:i:s', time() + (APP_REMEMBER_ME_DAYS * 24 * 60 * 60));

    $stmt = $conn->prepare(
        "INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)"
    );

    if (!$stmt) {
        die("Remember-me token creation failed: " . $conn->error);
    }

    $stmt->bind_param("isss", $userId, $selector, $tokenHash, $expiresAt);

    if (!$stmt->execute()) {
        $statementError = $stmt->error;
        $stmt->close();
        die("Remember-me token save failed: " . $statementError);
    }

    $stmt->close();

    setcookie(
        APP_REMEMBER_ME_COOKIE,
        $selector . ':' . $validator,
        authCookieOptions(time() + (APP_REMEMBER_ME_DAYS * 24 * 60 * 60))
    );
}

function attemptAutoLogin(mysqli $conn): bool
{
    if (isset($_SESSION['user_id'])) {
        return true;
    }

    $cookieValue = $_COOKIE[APP_REMEMBER_ME_COOKIE] ?? '';
    if ($cookieValue === '') {
        return false;
    }

    $parts = explode(':', $cookieValue, 2);
    if (
        count($parts) !== 2 ||
        strlen($parts[0]) !== 24 ||
        strlen($parts[1]) !== 64 ||
        !ctype_xdigit($parts[0]) ||
        !ctype_xdigit($parts[1])
    ) {
        clearRememberMeCookie();
        return false;
    }

    [$selector, $validator] = $parts;
    ensureRememberTokensTable($conn);

    $stmt = $conn->prepare(
        "SELECT rt.user_id, rt.token_hash, rt.expires_at, u.id, u.username, u.name, u.email
         FROM remember_tokens rt
         INNER JOIN users u ON u.id = rt.user_id
         WHERE rt.selector = ?
         LIMIT 1"
    );

    if (!$stmt) {
        clearRememberMeCookie();
        return false;
    }

    $stmt->bind_param("s", $selector);
    $stmt->execute();
    $stmt->bind_result($tokenUserId, $tokenHash, $expiresAt, $id, $username, $name, $email);

    if (!$stmt->fetch()) {
        $stmt->close();
        clearRememberMeCookie();
        return false;
    }

    $stmt->close();

    if (strtotime($expiresAt) < time() || !hash_equals($tokenHash, hash('sha256', $validator))) {
        deleteRememberTokenBySelector($conn, $selector);
        clearRememberMeCookie();
        return false;
    }

    setLoggedInUserSession([
        'id' => $id,
        'username' => $username,
        'name' => $name,
        'email' => $email,
    ]);

    createRememberMeToken($conn, (int)$tokenUserId);
    deleteRememberTokenBySelector($conn, $selector);

    return true;
}

function requireLogin(mysqli $conn): void
{
    if (isset($_SESSION['user_id'])) {
        return;
    }

    if (attemptAutoLogin($conn)) {
        return;
    }

    authRedirect('login.html');
}

function logoutUser(mysqli $conn): void
{
    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    forgetRememberedLogin($conn, $userId);
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
}
?>
