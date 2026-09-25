<?php
/**
 * Authentication Controller
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    public function getLogin(): void {
        if (Auth::isAuthenticated()) {
            header('Location: ' . (Auth::isAdmin() ? '/admin' : '/dashboard'));
            exit;
        }

        if (!empty($_GET['logged_out'])) {
            Flash::set('success_msg', 'You have been logged out successfully.');
        }

        $googleClientId = env('GOOGLE_CLIENT_ID');
        $hasGoogleAuth = !empty($googleClientId) && $googleClientId !== 'your_google_client_id_here';

        $showCaptcha = Security::isCaptchaRequired();
        $captchaQuestion = null;
        if ($showCaptcha) {
            if (empty($_SESSION['captchaQuestion']) || empty($_SESSION['captchaAnswer'])) {
                Security::generateCaptcha();
            }
            $captchaQuestion = $_SESSION['captchaQuestion'] ?? null;
        }

        view('auth/login', [
            'title' => 'Sign In - RACE FINANCE',
            'hasGoogleAuth' => $hasGoogleAuth,
            'showCaptcha' => $showCaptcha,
            'captchaQuestion' => $captchaQuestion
        ]);
    }

    public function postLogin(): void {
        $identifier = trim($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';

        $captchaRequired = Security::isCaptchaRequired($identifier);
        if ($captchaRequired) {
            $captchaInput = $_POST['captcha'] ?? '';
            if (!Security::validateCaptcha($captchaInput)) {
                Security::recordFailedAttempt($identifier);
                Flash::set('error_msg', 'Incorrect security verification answer. Please solve the math challenge to proceed.');
                header('Location: /auth/login');
                exit;
            }
        }

        try {
            $user = User::findByEmailOrPhone($identifier);
        } catch (Throwable $e) {
            Flash::set('error_msg', 'Database connection error: ' . $e->getMessage());
            header('Location: /auth/login');
            exit;
        }

        if (!$user) {
            Security::recordFailedAttempt($identifier);
            Flash::set('error_msg', 'Invalid login credentials.');
            header('Location: /auth/login');
            exit;
        }

        $hash = $user['password'] ?? '';
        if (!$hash || !password_verify($password, $hash)) {
            Security::recordFailedAttempt($identifier);
            Flash::set('error_msg', 'Invalid login credentials.');
            header('Location: /auth/login');
            exit;
        }

        if ($user['status'] === 'suspended') {
            Flash::set('error_msg', 'Your account has been suspended by the platform administrator.');
            header('Location: /auth/login');
            exit;
        }

        if ($user['role'] !== 'admin' && !empty($user['subscription_expires_at'])) {
            if (strtotime($user['subscription_expires_at']) < time()) {
                Flash::set('error_msg', 'Your subscription has expired. All your billing data and records are safely preserved. Please contact the administrator on WhatsApp to renew.');
                header('Location: /auth/login');
                exit;
            }
        }

        Auth::login($user);
        Security::clearFailedAttempts($identifier);

        Flash::set('success_msg', "Welcome back, {$user['name']}!");

        $defaultRedirect = ($user['role'] === 'admin') ? '/admin' : '/dashboard';
        $returnTo = $_SESSION['returnTo'] ?? null;
        unset($_SESSION['returnTo']);

        $redirectUrl = Security::getSafeRedirectUrl($returnTo, $defaultRedirect, $user['role']);
        header("Location: {$redirectUrl}");
        exit;
    }

    public function getRegister(): void {
        Flash::set('info_msg', 'Direct online registration is disabled. Accounts are manually provisioned by the administrator. Please contact support on WhatsApp to request your trial credentials.');
        header('Location: /auth/login');
        exit;
    }

    public function postRegister(): void {
        Flash::set('error_msg', 'Direct online registration is disabled. Please contact support on WhatsApp to request access.');
        header('Location: /auth/login');
        exit;
    }

    public function googleAuth(): void {
        $clientId = env('GOOGLE_CLIENT_ID');
        if (!$clientId || $clientId === 'your_google_client_id_here') {
            Flash::set('error_msg', 'Google OAuth is not configured yet. Please use Phone/Email login or configure GOOGLE_CLIENT_ID in .env');
            header('Location: /auth/login');
            exit;
        }

        $callback = env('GOOGLE_CALLBACK_URL', APP_URL . '/auth/google/callback');
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $callback,
            'response_type' => 'code',
            'scope' => 'openid email profile https://www.googleapis.com/auth/drive.file',
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];

        header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
        exit;
    }

    public function googleCallback(): void {
        $code = $_GET['code'] ?? null;
        if (!$code) {
            Flash::set('error_msg', 'Google sign-in was cancelled or failed.');
            header('Location: /auth/login');
            exit;
        }

        $clientId = env('GOOGLE_CLIENT_ID');
        $clientSecret = env('GOOGLE_CLIENT_SECRET');
        $callback = env('GOOGLE_CALLBACK_URL', APP_URL . '/auth/google/callback');

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $callback,
            'grant_type' => 'authorization_code'
        ]));
        $response = curl_exec($ch);
        curl_close($ch);

        $tokens = json_decode((string)$response, true);
        if (empty($tokens['access_token'])) {
            Flash::set('error_msg', 'Failed to retrieve access token from Google.');
            header('Location: /auth/login');
            exit;
        }

        // Fetch User Profile
        $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokens['access_token']]);
        $profileRes = curl_exec($ch);
        curl_close($ch);

        $profile = json_decode((string)$profileRes, true);
        if (empty($profile['email'])) {
            Flash::set('error_msg', 'Failed to retrieve Google profile information.');
            header('Location: /auth/login');
            exit;
        }

        $email = strtolower(trim($profile['email']));
        $user = User::findByGoogleId($profile['sub']) ?: User::findByEmail($email);

        if (!$user) {
            Flash::set('error_msg', 'No account found with this Google email. Accounts must be registered by the administrator before signing in.');
            header('Location: /auth/login');
            exit;
        }

        // Update google_id and avatar if missing
        User::updateGoogleId((int)$user['id'], $profile['sub'], $profile['picture'] ?? null);

        // Save Google Drive tokens for backups
        require_once __DIR__ . '/../models/Backup.php';
        Backup::saveGoogleToken((int)$user['id'], [
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'] ?? null,
            'scope' => $tokens['scope'] ?? 'drive.file',
            'token_type' => $tokens['token_type'] ?? 'Bearer',
            'expiry_date' => isset($tokens['expires_in']) ? (time() + $tokens['expires_in']) * 1000 : null,
            'email' => $email
        ]);

        Auth::login($user);
        Flash::set('success_msg', 'Signed in with Google successfully!');

        $defaultRedirect = ($user['role'] === 'admin') ? '/admin' : '/dashboard';
        $returnTo = $_SESSION['returnTo'] ?? null;
        unset($_SESSION['returnTo']);

        $redirectUrl = Security::getSafeRedirectUrl($returnTo, $defaultRedirect, $user['role']);
        header("Location: {$redirectUrl}");
        exit;
    }

    public function logout(): void {
        Auth::logout();
        header('Location: /auth/login?logged_out=1');
        exit;
    }
}
