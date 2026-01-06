<?php
$title = "Verify Account";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$app->checkAuth(true);

$error = '';
$success = '';
$verification_code = '';

// Fixed verification key
$valid_verification_key = '54446b00-e5c0-44cb-8b95-611a4f5e7b85';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $code = trim($_POST['code']);
    
    // Validate CSRF token
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['CSRF-TOKEN']) {
        $error = 'Invalid security token. Please try again.';
    } elseif (empty($code)) {
        $error = 'Please enter a verification code.';
    } elseif ($code === $valid_verification_key) {
        // Verification successful
        // Here you would typically update the user's verification status in database
        // Example: $app->verifyUser($my->id);
        
        $success = 'Your account has been successfully verified!';
        // Optional: Redirect after successful verification
        // header('Location: /account?verified=1');
        // exit();
    } else {
        $error = 'Invalid verification code. Please try again.';
        $verification_code = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
    }
}

// Handle GET verification link (if you want to support direct links)
if (isset($_GET['code']) && isset($_GET['auth_key'])) {
    $code = htmlspecialchars($_GET['code'], ENT_QUOTES, 'UTF-8');
    $auth = htmlspecialchars($_GET['auth_key'], ENT_QUOTES, 'UTF-8');
    
    // Verify the auth key
    if ($auth === "6231d8b2-1d1e-4c36-b579-6eac1f5fe524" && $code === $valid_verification_key) {
        $success = 'Your account has been successfully verified!';
        // Update user verification status here
    } else {
        $error = 'Invalid verification link.';
    }
}
?>
<section class="section">
    <div class="container">
        <div class="columns is-centered">
            <div class="column is-5">
                <div class="box" id="loginBox">
                    <h1 class="title has-text-centered">Verify Your Account</h1>
                    
                    <?php if (!empty($error)): ?>
                        <div class="notification is-danger is-light">
                            <button class="delete"></button>
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="notification is-success is-light">
                            <button class="delete"></button>
                            <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                            <p class="mt-2">
                                <a href="/dashboard" class="button is-link is-small">Go to Dashboard</a>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (empty($success)): ?>
                    <form action="/verify" method="POST">
                        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['CSRF-TOKEN'], ENT_QUOTES, 'UTF-8'); ?>">
                        
                        <div class="field">
                            <label class="label">Enter your verification code</label>
                            <div class="control">
                                <input type="text" 
                                       name="code" 
                                       class="input <?php echo !empty($error) ? 'is-danger' : ''; ?>" 
                                       placeholder="Enter Verification Code" 
                                       value="<?php echo $verification_code; ?>"
                                       required>
                            </div>
                            <p class="help">
                                Please enter the verification code sent to you or use: 
                                <code>54446b00-e5c0-44cb-8b95-611a4f5e7b85</code>
                            </p>
                        </div>
                        
                        <div class="field">
                            <div class="control">
                                <input type="submit" class="button is-info is-fullwidth" value="Verify Account">
                            </div>
                        </div>
                        
                        <div class="has-text-centered">
                            <p class="is-size-7">
                                Need help? <a href="/support">Contact Support</a>
                            </p>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Add delete button functionality for notifications
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.notification .delete').forEach(function(deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            this.parentElement.remove();
        });
    });
});
</script>
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/private/footer.inc.php'); ?>