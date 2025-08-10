<?php
session_start();
require_once '../../models/Usuario.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if ($login && $senha) {
        $usuario = Usuario::autenticar($login, $senha);
        if ($usuario) {
            $_SESSION['usuario_id'] = $usuario['id'];
            header('Location: ../chat/index.php');
            exit;
        } else {
            $erro = 'Login ou senha inválidos.';
        }
    } else {
        $erro = 'Preencha todos os campos.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Chat Global</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0088cc;
            --primary-dark: #006699;
            --secondary-color: #f5f5f5;
            --text-primary: #000000;
            --text-secondary: #707579;
            --border-color: #e1e5e9;
            --success-color: #4caf50;
            --danger-color: #f44336;
            --chat-bg: #ffffff;
            --message-bg: #f1f3f4;
            --header-bg: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: var(--chat-bg);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
        }

        .login-header {
            background: var(--primary-color);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .login-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 24px;
        }

        .login-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            font-size: 14px;
            opacity: 0.9;
        }

        .login-form {
            padding: 2rem;
        }

        .error-message {
            background: #ffebee;
            color: var(--danger-color);
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #ffcdd2;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 14px;
            background: var(--secondary-color);
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 136, 204, 0.1);
        }

        .form-input::placeholder {
            color: var(--text-secondary);
        }

        .login-button {
            width: 100%;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .login-button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 136, 204, 0.3);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .login-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .login-footer {
            padding: 1.5rem 2rem;
            background: var(--secondary-color);
            text-align: center;
            border-top: 1px solid var(--border-color);
        }

        .login-footer p {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0;
        }

        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .login-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Input com ícone */
        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 16px;
            pointer-events: none;
            transition: color 0.3s ease;
        }

        .input-group .form-input {
            padding-left: 48px;
        }

        .input-group .form-input:focus + .input-icon {
            color: var(--primary-color);
        }

        /* Responsividade */
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .login-container {
                border-radius: 12px;
            }

            .login-header {
                padding: 1.5rem;
            }

            .login-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .login-title {
                font-size: 20px;
            }

            .login-form {
                padding: 1.5rem;
            }

            .login-footer {
                padding: 1rem 1.5rem;
            }
        }

        /* Animações */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-container {
            animation: fadeInUp 0.5s ease;
        }

        /* Estados de loading */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <div class="login-icon">
                <i class="bi bi-chat-dots-fill"></i>
            </div>
            <h1 class="login-title">Chat Global</h1>
            <p class="login-subtitle">Entre para conversar com todos</p>
        </div>

        <!-- Formulário -->
        <div class="login-form">
            <?php if ($erro): ?>
                <div class="error-message">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <form method="post" id="loginForm">
                <div class="form-group">
                    <label for="login" class="form-label">
                        <i class="bi bi-person me-1"></i>
                        Login
                    </label>
                    <div class="input-group">
                        <input type="text" 
                               id="login"
                               name="login" 
                               class="form-input"
                               placeholder="Digite seu login"
                               required
                               autocomplete="username">
                        <i class="bi bi-person input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="senha" class="form-label">
                        <i class="bi bi-lock me-1"></i>
                        Senha
                    </label>
                    <div class="input-group">
                        <input type="password" 
                               id="senha"
                               name="senha" 
                               class="form-input"
                               placeholder="Digite sua senha"
                               required
                               autocomplete="current-password">
                        <i class="bi bi-lock input-icon"></i>
                    </div>
                </div>

                <button type="submit" class="login-button" id="loginBtn">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Entrar
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="login-footer">
            <p>
                Não tem conta? 
                <a href="../cadastro/index.php">Cadastre-se aqui</a>
            </p>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Efeito de loading no botão
        document.getElementById('loginForm').addEventListener('submit', function() {
            const button = document.getElementById('loginBtn');
            const originalContent = button.innerHTML;
            
            button.innerHTML = '<div class="spinner"></div> Entrando...';
            button.disabled = true;
            
            // Se houver erro, restaurar o botão após um tempo
            setTimeout(() => {
                if (button.disabled) {
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
            }, 5000);
        });

        // Remover mensagem de erro ao digitar
        const inputs = document.querySelectorAll('.form-input');
        const errorMessage = document.querySelector('.error-message');
        
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (errorMessage) {
                    errorMessage.style.opacity = '0.5';
                }
            });
        });

        // Enter para submeter
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                const form = document.getElementById('loginForm');
                const activeElement = document.activeElement;
                
                if (activeElement.tagName === 'INPUT') {
                    form.submit();
                }
            }
        });

        // Auto-focus no primeiro campo
        window.addEventListener('load', function() {
            document.getElementById('login').focus();
        });
    </script>
</body>
</html>
