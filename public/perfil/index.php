<?php
session_start();
require_once '../../models/Usuario.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login/index.php');
    exit;
}

$usuario = Usuario::buscarPorId($_SESSION['usuario_id']);
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nomeFoto = uniqid('perfil_') . '.' . $ext;
        $caminho = '../../uploads/' . $nomeFoto;
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho)) {
            $relativo = 'uploads/' . $nomeFoto;
            if (Usuario::atualizarFotoPerfil($usuario['id'], $relativo)) {
                $sucesso = 'Foto de perfil atualizada!';
                header("Refresh: 2; URL=../chat/index.php");
            } else {
                $erro = 'Erro ao salvar no banco de dados.';
            }
        } else {
            $erro = 'Falha ao mover o arquivo.';
        }
    } else {
        $erro = 'Erro no upload da imagem.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Chat Global</title>
    
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

        .profile-container {
            background: var(--chat-bg);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
        }

        .profile-header {
            background: var(--primary-color);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .back-button {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-50%) scale(1.1);
        }

        .profile-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .profile-subtitle {
            font-size: 14px;
            opacity: 0.9;
        }

        .profile-content {
            padding: 2rem;
        }

        .current-photo-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .current-photo-label {
            font-size: 16px;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: block;
        }

        .current-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            border: 4px solid var(--primary-color);
            box-shadow: 0 4px 16px rgba(0, 136, 204, 0.2);
        }

        .current-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .default-avatar {
            background: var(--primary-color);
            color: white;
            font-size: 48px;
            font-weight: 600;
        }

        .no-photo-text {
            color: var(--text-secondary);
            font-size: 14px;
            margin-top: 1rem;
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

        .upload-section {
            background: var(--secondary-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .upload-label {
            font-size: 16px;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: block;
        }

        .file-upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            background: white;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .file-upload-area:hover {
            border-color: var(--primary-color);
            background: rgba(0, 136, 204, 0.02);
        }

        .file-upload-area.dragover {
            border-color: var(--primary-color);
            background: rgba(0, 136, 204, 0.05);
        }

        .upload-icon {
            font-size: 48px;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        .upload-text {
            font-size: 16px;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .upload-hint {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .preview-section {
            margin-top: 1rem;
            text-align: center;
            display: none;
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .preview-name {
            font-size: 14px;
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 120px;
            justify-content: center;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 136, 204, 0.3);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: var(--secondary-color);
            border: 2px solid var(--border-color);
            color: var(--text-primary);
            padding: 10px 24px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: var(--border-color);
            color: var(--text-primary);
            text-decoration: none;
        }

        /* Responsividade */
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .profile-container {
                border-radius: 12px;
            }

            .profile-header {
                padding: 1.5rem;
            }

            .back-button {
                left: 0.75rem;
                width: 36px;
                height: 36px;
            }

            .profile-title {
                font-size: 20px;
            }

            .profile-content {
                padding: 1.5rem;
            }

            .current-avatar {
                width: 100px;
                height: 100px;
            }

            .default-avatar {
                font-size: 40px;
            }

            .file-upload-area {
                padding: 1.5rem;
            }

            .upload-icon {
                font-size: 36px;
            }

            .action-buttons {
                flex-direction: column;
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

        .profile-container {
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
    </style>
</head>
<body>
    <div class="profile-container">
        <!-- Header -->
        <div class="profile-header">
            <a href="../chat/index.php" class="back-button">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="profile-title">Foto de Perfil</h1>
            <p class="profile-subtitle">Personalize seu avatar</p>
        </div>

        <!-- Conteúdo -->
        <div class="profile-content">
            <!-- Mensagens -->
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
                    <p><i class="bi bi-arrow-right me-1"></i>Redirecionando para o chat...</p>
                </div>
            <?php endif; ?>

            <!-- Foto Atual -->
            <div class="current-photo-section">
                <span class="current-photo-label">
                    <i class="bi bi-person-circle me-1"></i>
                    Foto Atual
                </span>
                
                <div class="current-avatar">
                    <?php if ($usuario['foto_perfil']): ?>
                        <img src="../../<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil atual">
                    <?php else: ?>
                        <div class="default-avatar">
                            <?= strtoupper(substr($usuario['nome_gerado'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!$usuario['foto_perfil']): ?>
                    <p class="no-photo-text">Nenhuma foto definida</p>
                <?php endif; ?>
            </div>

            <!-- Upload -->
            <form method="post" enctype="multipart/form-data" id="uploadForm">
                <div class="upload-section">
                    <label class="upload-label">
                        <i class="bi bi-cloud-upload me-1"></i>
                        Nova Foto
                    </label>
                    
                    <div class="file-upload-area" id="uploadArea">
                        <input type="file" 
                               name="foto" 
                               id="foto"
                               class="file-input" 
                               accept="image/*" 
                               required>
                        
                        <div class="upload-content">
                            <i class="bi bi-cloud-upload upload-icon"></i>
                            <div class="upload-text">Clique ou arraste uma imagem</div>
                            <div class="upload-hint">PNG, JPG ou GIF até 5MB</div>
                        </div>
                    </div>
                    
                    <div class="preview-section" id="previewSection">
                        <img id="previewImage" class="preview-image" alt="Preview">
                        <div id="previewName" class="preview-name"></div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn-primary" id="saveBtn">
                        <i class="bi bi-check-lg"></i>
                        Salvar Foto
                    </button>
                    <a href="../chat/index.php" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const fileInput = document.getElementById('foto');
        const uploadArea = document.getElementById('uploadArea');
        const previewSection = document.getElementById('previewSection');
        const previewImage = document.getElementById('previewImage');
        const previewName = document.getElementById('previewName');
        const saveBtn = document.getElementById('saveBtn');
        const uploadForm = document.getElementById('uploadForm');

        // Preview da imagem selecionada
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                showPreview(file);
            }
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                if (file.type.startsWith('image/')) {
                    fileInput.files = files;
                    showPreview(file);
                }
            }
        });

        function showPreview(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewName.textContent = file.name;
                previewSection.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }

        // Loading no botão
        uploadForm.addEventListener('submit', function() {
            saveBtn.innerHTML = '<div class="spinner"></div> Salvando...';
            saveBtn.disabled = true;
        });

        // Validação de arquivo
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Verificar tamanho (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Arquivo muito grande! Máximo 5MB.');
                    this.value = '';
                    previewSection.style.display = 'none';
                    return;
                }
                
                // Verificar tipo
                if (!file.type.startsWith('image/')) {
                    alert('Por favor, selecione apenas imagens.');
                    this.value = '';
                    previewSection.style.display = 'none';
                    return;
                }
            }
        });

        // Auto-redirect se sucesso
        <?php if ($sucesso): ?>
            setTimeout(() => {
                window.location.href = '../chat/index.php';
            }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>
