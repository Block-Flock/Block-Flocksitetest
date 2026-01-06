<?php
$title = "Log-in";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

// Security: Validate CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['CSRF-TOKEN'], $_POST['csrf'])) {
        die('Invalid CSRF token');
    }
}

$app->checkAuth(false);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['bctdkio8'] ?? '');
    $password = $_POST['8q5EonNy'] ?? '';

    // Input validation
    if (empty($username) || empty($password)) {
        $err = 'Please enter both username and password.';
    } else {
        try {
            // Query user
            $stmt = $db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            
            if ($stmt->rowCount() === 0) {
                $err = 'Incorrect username or password.';
            } else {
                $user = $stmt->fetch(PDO::FETCH_OBJ);
                
                // Verify password
                if (!password_verify($password, $user->password)) {
                    $err = 'Incorrect username or password.';
                } else {
                    // Successful login
                    $_SESSION['id'] = $user->id;
                    $_SESSION['uagent'] = $_SERVER['HTTP_USER_AGENT'];
                    
                    // Log action
                    $logStmt = $db->prepare(
                        "INSERT INTO useractionlogs (UserID, Action, TimeLog, IP) 
                         VALUES (?, ?, ?, ?)"
                    );
                    $logStmt->execute([
                        $user->id,
                        'Logged Into Their Account.',
                        time(),
                        $_SERVER['REMOTE_ADDR']
                    ]);

                    // Redirect
                    header('Location: /dashboard');
                    exit;
                }
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $err = 'An error occurred during login. Please try again.';
        }
    }
}
?>

<section class="section">
    <div class="container">
        <div class="columns is-centered">
            <div class="column is-6">
                <div class="box">
                    <?php if (isset($err)): ?>
                        <div class="notification is-danger"><?= htmlspecialchars($err) ?></div>
                    <?php endif; ?>
                    
                    <h1 class="title is-size-4">Login</h1>
                    <p class="subtitle is-size-6">Continue where you left off</p>
                    
                    <form method="post">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['CSRF-TOKEN']) ?>">
                        
                        <!-- Username -->
                        <div class="field">
                            <label class="label">Username</label>
                            <div class="control has-icons-left">
                                <input type="text" 
                                       name="bctdkio8" 
                                       class="input" 
                                       placeholder="Enter your username" 
                                       required
                                       value="<?= htmlspecialchars($username ?? '') ?>">
                                <span class="icon is-small is-left">
                                    <i class="fas fa-user fa-lg"></i>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Password -->
                        <div class="field">
                            <label class="label">Password</label>
                            <div class="control has-icons-left">
                                <input type="password" 
                                       name="8q5EonNy" 
                                       autocomplete="current-password" 
                                       class="input" 
                                       placeholder="Enter your password" 
                                       required>
                                <span class="icon is-small is-left">
                                    <i class="fas fa-unlock-alt fa-lg"></i>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Submit -->
                        <div class="field">
                            <button type="submit" class="button is-info is-fullwidth">
                                <strong>Login</strong>
                            </button>
                        </div>
                    </form>
                </div>
                
                <p class="has-text-centered">
                    Forgot your password? 
                    <a href="/reset/">Click here to reset</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php $app->footer(); ?>