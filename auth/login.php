<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role_code'] === 'ADM') {
        header('Location: ../admin/dashboard.php');
        exit;
    } elseif ($_SESSION['role_code'] === 'STF') {
        header('Location: ../staff/dashboard.php');
        exit;
    } else {
        // Role tidak dikenali, logout paksa
        session_destroy();
        header('Location: login.php?error=Akses tidak sah');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - B-LOG</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    * {
  font-family: 'Inter', sans-serif;
  box-sizing: border-box;
}
body {
  margin: 0;
  padding: 0;
  min-height: 100vh;
  position: relative;
}
    
    .bg-image {
      background: url('../assets/images/bg-books.jpeg') no-repeat center center/cover;
      position: fixed;
      top: 0; 
      left: 0;
      width: 100%; 
      height: 100%;
      filter: brightness(40%);
      z-index: -1;
    }

.login-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 24px;
}

   .login-container {
  width: 100%;
  max-width: 420px;
  background: rgba(255, 255, 255, 0.95);
  padding: 32px;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  z-index: 1;
}

@media (min-width: 1200px) {
  .login-container {
    max-width: 420px;
  }
}

    .brand-section {
      text-align: center;
      margin-bottom:28px;
    }

    .brand-logo {
      width: 100px;
      height: 100px;
      object-fit: contain;
      margin-bottom: 12px;
    }

    .brand-title {
      font-size: 24px;
      font-weight: 600;
      color: #1a73e8;
      margin-bottom: 4px;
    }

    .brand-subtitle {
      font-size: 14px;
      color: #5f6368;
      margin-bottom: 0;
    }

    .role-toggle {
      display: flex;
      background: #f8f9fa;
      border-radius: 8px;
      padding: 4px;
      margin-bottom: 24px;
      border: 1px solid #e5e5e5;
    }

    .role-toggle button {
      flex: 1;
      border: none;
      background: transparent;
      padding: 8px 16px;
      font-weight: 500;
      font-size: 14px;
      border-radius: 6px;
      transition: all 0.2s;
      color: #5f6368;
    }

    .role-toggle .active {
      background: #1a73e8;
      color: white;
      box-shadow: 0 1px 3px rgba(26, 115, 232, 0.3);
    }

    .form-control-compact {
      border: 1px solid #dadce0;
      border-radius: 8px;
      padding: 12px 16px;
      font-size: 14px;
      transition: all 0.2s;
    }

    .form-control-compact:focus {
      border-color: #1a73e8;
      box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
    }

    .form-label-compact {
      font-weight: 500;
      color: #202124;
      margin-bottom: 8px;
      font-size: 14px;
    }

    .btn-login {
      background: #1a73e8;
      color: white;
      border: none;
      border-radius: 8px;
      padding: 12px 24px;
      font-weight: 500;
      font-size: 14px;
      width: 100%;
      transition: all 0.2s;
    }

    .btn-login:hover {
      background: #1557b0;
      color: white;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(26, 115, 232, 0.3);
    }

    .btn-login:active {
      transform: translateY(0);
    }

    .alert-compact {
      border-radius: 8px;
      border: none;
      padding: 12px 16px;
      font-size: 14px;
      margin-bottom: 20px;
    }

    .alert-danger {
      background: #fef2f2;
      color: #dc2626;
      border-left: 4px solid #dc2626;
    }

    .input-group-compact {
      position: relative;
    }

    .input-group-compact .form-control-compact {
      padding-left: 44px;
    }

    .input-group-compact .input-icon {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #5f6368;
      z-index: 3;
    }

    .login-footer {
      text-align: center;
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid #f0f0f0;
    }

    .login-footer p {
      margin: 0;
      font-size: 13px;
      color: #5f6368;
    }

    .version-info {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: rgba(255, 255, 255, 0.9);
      padding: 8px 12px;
      border-radius: 6px;
      font-size: 12px;
      color: #5f6368;
      backdrop-filter: blur(10px);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .login-container {
        margin: 58px;
        padding: 58px;
      }
      
      .brand-title {
        font-size: 20px;
      }
      
      .version-info {
        display: none;
      }
    }

    /* Loading state */
    .btn-login:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .btn-login.loading {
      position: relative;
      color: transparent;
    }

    .btn-login.loading::after {
      content: "";
      position: absolute;
      width: 16px;
      height: 16px;
      top: 50%;
      left: 50%;
      margin-left: -8px;
      margin-top: -8px;
      border: 2px solid transparent;
      border-top-color: white;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Focus states */
    .role-toggle button:focus {
      outline: 2px solid #1a73e8;
      outline-offset: 2px;
    }

    .form-control-compact:focus {
      outline: none;
    }

    .btn-login:focus {
      outline: 2px solid #1a73e8;
      outline-offset: 2px;
    }
  </style>
</head>
<body>
  <div class="bg-image"></div>
  
  <div class="d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="login-container">
      <!-- Brand Section -->
      <div class="brand-section">
        <img src="../assets/images/logoblog-removebg-preview.png" alt="B-LOG" class="brand-logo">
        <p class="brand-subtitle">Sistem Manajemen Inventori Buku</p>
      </div>

      <!-- Error Alert -->
      <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-compact">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <?= htmlspecialchars($_GET['error']) ?>
        </div>
      <?php endif; ?>

      <!-- Role Toggle -->
      <div class="role-toggle">
        <button type="button" id="btn-staff" class="active">
          <i class="bi bi-person me-1"></i>Staff
        </button>
        <button type="button" id="btn-admin">
          <i class="bi bi-shield-check me-1"></i>Admin
        </button>
      </div>

      <!-- Login Form -->
      <form action="proses_login.php" method="POST" id="loginForm">
        <input type="hidden" name="role" id="role" value="staff" />
        
        <div class="mb-3">
          <label for="email" class="form-label form-label-compact">Email</label>
          <div class="input-group-compact">
            <i class="bi bi-envelope input-icon"></i>
            <input type="email" id="email" name="email" class="form-control form-control-compact" 
                   placeholder="Masukkan email Anda" required autocomplete="email">
          </div>
        </div>

        <div class="mb-4">
          <label for="password" class="form-label form-label-compact">Password</label>
          <div class="input-group-compact">
            <i class="bi bi-lock input-icon"></i>
            <input type="password" id="password" name="password" class="form-control form-control-compact" 
                   placeholder="Masukkan password" required autocomplete="current-password">
          </div>
        </div>

        <button type="submit" class="btn btn-login" id="loginBtn">
          <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
        </button>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Role toggle functionality
    const btnStaff = document.getElementById("btn-staff");
    const btnAdmin = document.getElementById("btn-admin");
    const roleInput = document.getElementById("role");
    
    btnStaff.addEventListener("click", () => {
      btnStaff.classList.add("active");
      btnAdmin.classList.remove("active");
      roleInput.value = "staff";
    });
    
    btnAdmin.addEventListener("click", () => {
      btnAdmin.classList.add("active");
      btnStaff.classList.remove("active");
      roleInput.value = "admin";
    });

    // Form submission with loading state
    const loginForm = document.getElementById("loginForm");
    const loginBtn = document.getElementById("loginBtn");
    
    loginForm.addEventListener("submit", function(e) {
      // Add loading state
      loginBtn.classList.add("loading");
      loginBtn.disabled = true;
      
      // Remove loading state after 3 seconds (fallback)
      setTimeout(() => {
        loginBtn.classList.remove("loading");
        loginBtn.disabled = false;
      }, 3000);
    });

    // Auto-focus email field
    document.getElementById("email").focus();
  </script>
</body>
</html>
