<?php
require_once '../../models/Usuario.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sexo = $_POST['sexo'] ?? '';
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if ($sexo && $login && $senha) {
        $ok = Usuario::cadastrar($sexo, $login, $senha);
        if ($ok) {
            $sucesso = 'Usuário cadastrado com sucesso! Faça login.';
        } else {
            $erro = 'Erro ao cadastrar. Esse login pode já estar em uso.';
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
    <title>Cadastro - Chat Global</title>
    
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

        .register-container {
            background: var(--chat-bg);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }

        .register-header {
            background: var(--primary-color);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .register-icon {
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

        .register-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .register-subtitle {
            font-size: 14px;
            opacity: 0.9;
        }

        .register-form {
            padding: 2rem;
        }

        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .error-message {
            background: #ffebee;
            color: var(--danger-color);
            border: 1px solid #ffcdd2;
        }

        .success-message {
            background: #e8f5e8;
            color: var(--success-color);
            border: 1px solid #c8e6c9;
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

        .form-input, .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 14px;
            background: var(--secondary-color);
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input:focus, .form-select:focus {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 136, 204, 0.1);
        }

        .form-input::placeholder {
            color: var(--text-secondary);
        }

        .form-select {
            cursor: pointer;
        }

        .form-select option {
            padding: 8px;
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

        .input-group .form-input,
        .input-group .form-select {
            padding-left: 48px;
        }

        .input-group .form-input:focus + .input-icon,
        .input-group .form-select:focus + .input-icon {
            color: var(--primary-color);
        }

        .register-button {
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

        .register-button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 136, 204, 0.3);
        }

        .register-button:active {
            transform: translateY(0);
        }

        .register-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .register-footer {
            padding: 1.5rem 2rem;
            background: var(--secondary-color);
            text-align: center;
            border-top: 1px solid var(--border-color);
        }

        .register-footer p {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0;
        }

        .register-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .register-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Indicadores de força da senha */
        .password-strength {
            margin-top: 0.5rem;
            font-size: 12px;
        }

        .strength-indicator {
            height: 4px;
            background: var(--border-color);
            border-radius: 2px;
            margin-top: 0.25rem;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background: var(--danger-color); width: 33%; }
        .strength-medium { background: #ff9800; width: 66%; }
        .strength-strong { background: var(--success-color); width: 100%; }

        /* Responsividade */
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .register-container {
                border-radius: 12px;
            }

            .register-header {
                padding: 1.5rem;
            }

            .register-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .register-title {
                font-size: 20px;
            }

            .register-form {
                padding: 1.5rem;
            }

            .register-footer {
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

        .register-container {
            animation: fadeInUp 0.5s ease;
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

        /* Estados de sucesso */
        .success-redirect {
            text-align: center;
            padding: 1rem;
            background: rgba(76, 175, 80, 0.1);
            border-radius: 8px;
            margin-top: 1rem;
        }

        .success-redirect p {
            margin: 0;
            color: var(--success-color);
            font-size: 14px;
        }

        /* Customização do select */
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px 12px;
            appearance: none;
        }

        .input-group .form-select {
            background-position: right 12px center;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <!-- Header -->
        <div class="register-header">
            <div class="register-icon">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <h1 class="register-title">Criar Conta</h1>
            <p class="register-subtitle">Junte-se ao Chat Global</p>
        </div>

        <!-- Formulário -->
        <div class="register-form">
            <?php if ($erro): ?>
                <div class="message error-message">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php elseif ($sucesso): ?>
                <div class="message success-message">
                    <i class="bi bi-check-circle-fill"></i>
                    <?= htmlspecialchars($sucesso) ?>
                </div>
                <div class="success-redirect">
                    <p><i class="bi bi-arrow-right me-1"></i>Redirecionando para o login...</p>
                </div>
            <?php endif; ?>

            <form method="post" id="registerForm">
                <div class="form-group">
                    <label for="sexo" class="form-label">
                        <i class="bi bi-gender-ambiguous me-1"></i>
                        Sexo
                    </label>
                    <div class="input-group">
                        <select name="sexo" id="sexo" class="form-select" required>
                            <option value="">Selecione seu sexo</option>
                            <option value="M" <?= (isset($_POST['sexo']) && $_POST['sexo'] === 'M') ? 'selected' : '' ?>>
                                👨 Masculino
                            </option>
                            <option value="F" <?= (isset($_POST['sexo']) && $_POST['sexo'] === 'F') ? 'selected' : '' ?>>
                                👩 Feminino
                            </option>
                        </select>
                        <i class="bi bi-gender-ambiguous input-icon"></i>
                    </div>
                </div>

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
                               placeholder="Escolha um nome de usuário"
                               value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
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
                               placeholder="Crie uma senha segura"
                               required
                               autocomplete="new-password">
                        <i class="bi bi-lock input-icon"></i>
                    </div>
                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <div class="strength-text">Força da senha: <span id="strengthText">Fraca</span></div>
                        <div class="strength-indicator">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="register-button" id="registerBtn">
                    <i class="bi bi-person-plus"></i>
                    Criar Conta
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="register-footer">
            <p>
                Já tem conta? 
                <a href="../login/index.php">Fazer login</a>
            </p>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Efeito de loading no botão
        document.getElementById('registerForm').addEventListener('submit', function() {
            const button = document.getElementById('registerBtn');
            const originalContent = button.innerHTML;
            
            button.innerHTML = '<div class="spinner"></div> Criando conta...';
            button.disabled = true;
            
            // Se houver erro, restaurar o botão após um tempo
            setTimeout(() => {
                if (button.disabled) {
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
            }, 5000);
        });

        // Indicador de força da senha
        const senhaInput = document.getElementById('senha');
        const strengthIndicator = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('strengthText');
        const strengthBar = document.getElementById('strengthBar');

        senhaInput.addEventListener('input', function() {
            const password = this.value;
            
            if (password.length === 0) {
                strengthIndicator.style.display = 'none';
                return;
            }
            
            strengthIndicator.style.display = 'block';
            
            let strength = 0;
            let strengthLabel = 'Fraca';
            let strengthClass = 'strength-weak';
            
            // Critérios de força
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            if (strength >= 3) {
                strengthLabel = 'Média';
                strengthClass = 'strength-medium';
            }
            if (strength >= 4) {
                strengthLabel = 'Forte';
                strengthClass = 'strength-strong';
            }
            
            strengthText.textContent = strengthLabel;
            strengthBar.className = 'strength-bar ' + strengthClass;
        });

        // Remover mensagem de erro ao digitar
        const inputs = document.querySelectorAll('.form-input, .form-select');
        const errorMessage = document.querySelector('.error-message');
        
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (errorMessage) {
                    errorMessage.style.opacity = '0.5';
                }
            });
        });

        // Auto-focus no primeiro campo
        window.addEventListener('load', function() {
            document.getElementById('sexo').focus();
        });

        // Auto-redirect se sucesso
        <?php if ($sucesso): ?>
            setTimeout(() => {
                window.location.href = '../login/index.php';
            }, 3000);
        <?php endif; ?>

        // Validação em tempo real
        const loginInput = document.getElementById('login');
        loginInput.addEventListener('input', function() {
            const value = this.value;
            
            // Remover caracteres especiais
            this.value = value.replace(/[^a-zA-Z0-9_]/g, '');
            
            // Limitar tamanho
            if (this.value.length > 20) {
                this.value = this.value.substring(0, 20);
            }
        });
    </script>
</body>
</html>
