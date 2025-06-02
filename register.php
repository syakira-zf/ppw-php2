<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem CRUD Mahasiswa</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Register</h1>
            <p>Buat Akun Baru</p>
        </div>
        
        <?php
        include_once("config.php");
        
        if (isLoggedIn()) {
            header("Location: index.php");
            exit();
        }
        
        if (isset($_POST['register'])) {
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            $errors = array();
            
            // Validasi
            if (empty($username)) {
                $errors[] = "Username tidak boleh kosong";
            }
            
            if (empty($email)) {
                $errors[] = "Email tidak boleh kosong";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format email tidak valid";
            }
            
            if (empty($full_name)) {
                $errors[] = "Nama lengkap tidak boleh kosong";
            }
            
            if (empty($password)) {
                $errors[] = "Password tidak boleh kosong";
            } elseif (strlen($password) < 6) {
                $errors[] = "Password minimal 6 karakter";
            }
            
            if ($password !== $confirm_password) {
                $errors[] = "Konfirmasi password tidak cocok";
            }
            
            // Cek username dan email sudah ada atau belum
            $check_query = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
            $check_result = mysqli_query($conn, $check_query);
            
            if (mysqli_num_rows($check_result) > 0) {
                $errors[] = "Username atau email sudah terdaftar";
            }
            
            if (empty($errors)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_query = "INSERT INTO users (username, email, full_name, password) 
                               VALUES ('$username', '$email', '$full_name', '$hashed_password')";
                
                if (mysqli_query($conn, $insert_query)) {
                    $success = "Registrasi berhasil! Silakan login.";
                } else {
                    $errors[] = "Error: " . mysqli_error($conn);
                }
            }
        }
        ?>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="full_name">Nama Lengkap</label>
                <input type="text" name="full_name" id="full_name" value="<?php echo isset($_POST['full_name']) ? $_POST['full_name'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            
            <button type="submit" name="register" class="btn">Register</button>
        </form>
        
        <div class="login-link">
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
</body>
</html>