<?php
include '../layout/header.php';
//start a session to handle login
session_start();
session_regenerate_id(true);

//hardcoded users
$users = [
    'admin' => [
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'role' => 'Admin'
    ],
    'cashier' => [
        'password' => password_hash('cashier123', PASSWORD_DEFAULT),
        'role' => 'Cashier'
    ]
];
$errors = '';
$username = '';

//check credentials
if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    //GET form input
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

   //server-side validation
    if (empty($username) || empty($password)) {
        $errors = "Please fill in all fields";
    } elseif (!isset($users[$username])) {
        $errors = "Invalid username or password";
    } elseif (!password_verify($password, $users[$username]['password'])) {
        $errors = "Invalid username or password";
    } else {
        // Login successful
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $users[$username]['role'];

        //redirect
        if ($users[$username]['role'] === 'Admin') {
            header('Location: ../admin/adminDashboard.php');
        } elseif ($users[$username]['role'] === 'Cashier') {
            header('Location: ../cashier/cashierDashboard.php');
        }
        exit; // Only exit on successful login
    }
}

?>
<div class="flex justify-center min-h-screen items-center">
    <div class="card w-96 bg-base-100 card-xl shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-center text-2xl">Login</h2>

            <!-- General error (login failed)-->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error mb-4">
                    <span><?= htmlspecialchars($errors) ?></span>
                </div>
            <?php endif ?>

            <form method="post" onclick="return validateForm()">
                <!-- username -->
                <div class="form-control mb-4">
                    <input id="username" class="input input-bordered" type="text" name="username" placeholder="Username" required>
                    <span id="username-error" class="text-error text-sm mt-1 hidden"></span>
                </div>

                <!-- password -->
                <div class="form-control mb-4">
                    <input id="password" class="input input-bordered" type="password" name="password" placeholder="Password" required>
                    <span id="password-error" class="text-error text-sm mt-1 hidden"></span>
                </div>

                <!-- login -->
                <button type="submit" class="btn btn-primary w-full self-end">login</button>
            </form>
        </div>
    </div>
</div>
<script>
    function validateForm() {

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;

        const usernameError = document.getElementById('username-error');
        const passwordError = document.getElementById('password-error');

        //clear previous error
        usernameError.classList.add('hidden');
        passwordError.classList.add('hidden');
        usernameError.textContent = '';
        usernameError.textContent = '';

        let hasError = false;

        //validate username
        if (username === '') {
            usernameError.textContent = 'Username field is required.';
            usernameError.classList.remove('hidden');
            hasError = false;
        }

        //validate password
        if (password === '') {
            passwordError.textContent = 'Password field is required.';
            passwordError.classList.remove('hidden');
            hasError = false;
        }
        //return false to prevent form submission if there are errors
        return !hasError;
    }

    document.getElementById('username').addEventListener('blur', function() {
        const username = this.value.trim();
        const usernameError = document.getElementById('username-error');

        if (username === '') {
            usernameError.textContent = 'Username is required';
            usernameError.classList.remove('hidden');
        } else if (username.length < 3) {
            usernameError.textContent = 'Username must be at least 3 characters';
            usernameError.classList.remove('hidden');
        } else {
            usernameError.classList.add('hidden');
        }
    });
    document.getElementById('password').addEventListener('blur', function() {
        const password = this.value;
        const passwordError = document.getElementById('password-error');

        if (password === '') {
            passwordError.textContent = 'Password is required';
            passwordError.classList.remove('hidden');
        } else if (password.length < 6) {
            passwordError.textContent = 'Password must be at least 6 characters';
            passwordError.classList.remove('hidden');
        } else {
            passwordError.classList.add('hidden');
        }
    });
</script>

<?php
include '../layout/footer.php';
?>