<?php
session_start();
require_once '../../models/Usuario.php';
require_once '../../models/Mensagem.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login/index.php');
    exit;
}

$usuario = Usuario::buscarPorId($_SESSION['usuario_id']);
$erro = '';

// Endpoint para buscar novas mensagens via AJAX
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_messages') {
    header('Content-Type: application/json');
    $ultimaId = (int)($_GET['ultima_id'] ?? 0);
    try {
        $novasMensagens = Mensagem::buscarNovasMensagens($ultimaId);
        echo json_encode($novasMensagens);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Envio de nova mensagem
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensagem = trim($_POST['mensagem'] ?? '');
    $imagem = null;

    // Upload da imagem, se houver
    if (!empty($_FILES['imagem']['name'])) {
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nomeImagem = uniqid('img_') . '.' . $ext;
        $caminho = '../../uploads/' . $nomeImagem;
        
        if (!is_dir('../../uploads')) {
            mkdir('../../uploads', 0777, true);
        }
        
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
            $imagem = 'uploads/' . $nomeImagem;
        } else {
            $erro = 'Falha ao enviar imagem.';
        }
    }

    if (!$erro && ($mensagem || $imagem)) {
        Mensagem::enviar($usuario['id'], $mensagem, $imagem);
        header('Location: index.php'); // evitar reenvio ao atualizar
        exit;
    }
}

$mensagens = Mensagem::todasComUsuarios();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <meta name="theme-color" content="#0088cc">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>Chat Global</title>
    
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
            --primary-light: #e3f2fd;
            --secondary-color: #f8fafc;
            --text-primary: #1a202c;
            --text-secondary: #718096;
            --text-muted: #a0aec0;
            --border-color: #e2e8f0;
            --success-color: #48bb78;
            --danger-color: #f56565;
            --warning-color: #ed8936;
            --chat-bg: #ffffff;
            --message-bg: #f7fafc;
            --header-bg: #ffffff;
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --radius-full: 9999px;
            
            /* Altura dinâmica para mobile */
            --vh: 1vh;
            --safe-area-inset-top: env(safe-area-inset-top);
            --safe-area-inset-bottom: env(safe-area-inset-bottom);
            --safe-area-inset-left: env(safe-area-inset-left);
            --safe-area-inset-right: env(safe-area-inset-right);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--secondary-color);
            color: var(--text-primary);
            line-height: 1.6;
            overflow: hidden;
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Container principal com altura dinâmica */
        .chat-container {
            display: flex;
            flex-direction: column;
            height: calc(var(--vh, 1vh) * 100);
            max-height: calc(var(--vh, 1vh) * 100);
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            background: var(--chat-bg);
            position: relative;
            overflow: hidden;
            padding-top: var(--safe-area-inset-top);
            padding-bottom: var(--safe-area-inset-bottom);
            padding-left: var(--safe-area-inset-left);
            padding-right: var(--safe-area-inset-right);
        }

        /* Header otimizado */
        .chat-header {
            background: var(--header-bg);
            border-bottom: 1px solid var(--border-color);
            padding: clamp(8px, 2vw, 16px) clamp(12px, 3vw, 20px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: clamp(56px, 12vw, 72px);
            flex-shrink: 0;
            box-shadow: var(--shadow-sm);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: relative;
            z-index: 10;
        }

        .user-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            gap: clamp(8px, 2vw, 16px);
        }

        .user-welcome {
            display: flex;
            align-items: center;
            gap: clamp(8px, 2vw, 12px);
            flex: 1;
            min-width: 0;
        }

        .user-avatar {
            width: clamp(32px, 8vw, 44px);
            height: clamp(32px, 8vw, 44px);
            border-radius: var(--radius-full);
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: clamp(12px, 3vw, 16px);
            flex-shrink: 0;
            box-shadow: var(--shadow-md);
        }

        .user-welcome-text {
            flex: 1;
            min-width: 0;
        }

        .user-welcome h1 {
            font-size: clamp(14px, 3.5vw, 18px);
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-welcome small {
            font-size: clamp(11px, 2.5vw, 13px);
            color: var(--text-secondary);
            display: block;
            margin-top: 2px;
            line-height: 1;
        }

        .user-actions {
            display: flex;
            gap: clamp(4px, 1vw, 8px);
            align-items: center;
            flex-shrink: 0;
        }

        .user-actions a {
            color: var(--text-secondary);
            text-decoration: none;
            padding: clamp(6px, 1.5vw, 10px) clamp(8px, 2vw, 12px);
            border-radius: var(--radius-lg);
            background: var(--secondary-color);
            font-size: clamp(11px, 2.5vw, 13px);
            display: flex;
            align-items: center;
            gap: clamp(2px, 1vw, 6px);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            white-space: nowrap;
            min-width: clamp(32px, 8vw, auto);
            justify-content: center;
        }

        .user-actions a:hover {
            background: var(--primary-light);
            color: var(--primary-color);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .user-actions a:active {
            transform: translateY(0);
        }

        /* Área de mensagens otimizada */
        .chat-messages {
            flex: 1;
            display: flex;
            flex-direction: column-reverse;
            overflow-y: auto;
            overflow-x: hidden;
            padding: clamp(8px, 2vw, 16px) clamp(12px, 3vw, 20px);
            background: var(--secondary-color);
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
        }

        .message-item {
            margin-bottom: clamp(6px, 1.5vw, 12px);
            display: flex;
            gap: clamp(6px, 1.5vw, 10px);
            padding: clamp(2px, 0.5vw, 4px) 0;
            animation: messageSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        @keyframes messageSlideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message-item:hover {
            background: rgba(0, 0, 0, 0.02);
            border-radius: var(--radius-md);
            margin: clamp(2px, 0.5vw, 4px) clamp(-6px, -1.5vw, -8px);
            padding: clamp(4px, 1vw, 8px) clamp(6px, 1.5vw, 8px);
        }

        .message-item.own-message {
            flex-direction: row-reverse;
            margin-left: clamp(20px, 5vw, 60px);
        }

        .message-item.own-message .message-content {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border-radius: var(--radius-xl) var(--radius-xl) var(--radius-sm) var(--radius-xl);
            padding: clamp(8px, 2vw, 12px) clamp(12px, 3vw, 16px);
            margin-right: clamp(6px, 1.5vw, 8px);
            margin-left: 0;
            max-width: clamp(200px, 70vw, 400px);
            word-wrap: break-word;
            box-shadow: var(--shadow-md);
            position: relative;
        }

        .message-item.own-message .message-content::before {
            content: '';
            position: absolute;
            bottom: 0;
            right: -6px;
            width: 0;
            height: 0;
            border: 6px solid transparent;
            border-top-color: var(--primary-dark);
            border-left-color: var(--primary-dark);
        }

        .message-item.own-message .message-header {
            justify-content: flex-end;
            flex-direction: row-reverse;
        }

        .message-item.own-message .username {
            color: rgba(255, 255, 255, 0.9);
        }

        .message-item.own-message .timestamp {
            color: rgba(255, 255, 255, 0.7);
        }

        .message-item.own-message .message-text {
            color: white;
        }

        .message-item.own-message:hover {
            background: rgba(0, 136, 204, 0.05);
        }

        /* Mensagens de outros usuários */
        .message-item:not(.own-message) .message-content {
            background: white;
            border-radius: var(--radius-sm) var(--radius-xl) var(--radius-xl) var(--radius-xl);
            padding: clamp(8px, 2vw, 12px) clamp(12px, 3vw, 16px);
            margin-left: clamp(6px, 1.5vw, 8px);
            max-width: clamp(200px, 70vw, 400px);
            word-wrap: break-word;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            position: relative;
        }

        .message-item:not(.own-message) .message-content::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: -6px;
            width: 0;
            height: 0;
            border: 6px solid transparent;
            border-top-color: white;
            border-right-color: white;
        }

        .profile-img, .default-avatar {
            width: clamp(28px, 7vw, 36px);
            height: clamp(28px, 7vw, 36px);
            border-radius: var(--radius-full);
            flex-shrink: 0;
            margin-top: 2px;
        }

        .profile-img {
            object-fit: cover;
            border: 2px solid var(--border-color);
        }

        .default-avatar {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: clamp(10px, 2.5vw, 14px);
            box-shadow: var(--shadow-sm);
        }

        .message-content {
            flex: 1;
            min-width: 0;
        }

        .message-header {
            display: flex;
            align-items: baseline;
            gap: clamp(4px, 1vw, 8px);
            margin-bottom: clamp(2px, 0.5vw, 4px);
        }

        .username {
            font-weight: 600;
            color: var(--text-primary);
            font-size: clamp(12px, 3vw, 14px);
            line-height: 1.2;
        }

        .timestamp {
            color: var(--text-secondary);
            font-size: clamp(10px, 2.5vw, 12px);
            font-weight: 500;
        }

        .message-text {
            font-size: clamp(13px, 3.2vw, 15px);
            line-height: 1.5;
            color: var(--text-primary);
            margin-bottom: clamp(2px, 0.5vw, 4px);
            word-wrap: break-word;
            hyphens: auto;
        }

        .message-image {
            max-width: clamp(150px, 40vw, 250px);
            max-height: clamp(150px, 40vw, 250px);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: clamp(4px, 1vw, 8px);
            box-shadow: var(--shadow-md);
        }

        .message-image:hover {
            opacity: 0.9;
            transform: scale(1.02);
        }

        /* Área de input otimizada */
        .chat-input-section {
            background: var(--chat-bg);
            border-top: 1px solid var(--border-color);
            padding: clamp(8px, 2vw, 16px) clamp(12px, 3vw, 20px);
            flex-shrink: 0;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: relative;
            z-index: 10;
        }

        .error-message {
            background: linear-gradient(135deg, #fed7d7, #feb2b2);
            color: var(--danger-color);
            padding: clamp(6px, 1.5vw, 10px) clamp(8px, 2vw, 12px);
            border-radius: var(--radius-md);
            margin-bottom: clamp(6px, 1.5vw, 10px);
            font-size: clamp(11px, 2.8vw, 13px);
            display: flex;
            align-items: center;
            gap: clamp(4px, 1vw, 6px);
            font-weight: 500;
            border: 1px solid rgba(245, 101, 101, 0.2);
        }

        .input-container {
            display: flex;
            gap: clamp(6px, 1.5vw, 12px);
            align-items: flex-end;
        }

        .message-input-wrapper {
            flex: 1;
            background: var(--secondary-color);
            border-radius: var(--radius-xl);
            padding: clamp(8px, 2vw, 12px) clamp(12px, 3vw, 16px);
            border: 2px solid var(--border-color);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .message-input-wrapper:focus-within {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 136, 204, 0.1);
        }

        .form-control {
            border: none;
            background: transparent;
            resize: none;
            font-size: clamp(13px, 3.2vw, 15px);
            line-height: 1.5;
            max-height: clamp(80px, 20vw, 120px);
            min-height: clamp(18px, 4.5vw, 22px);
            padding: 0;
            outline: none;
            width: 100%;
            font-family: inherit;
            color: var(--text-primary);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .file-input-wrapper {
            display: flex;
            align-items: center;
            margin-top: clamp(4px, 1vw, 6px);
        }

        .file-input {
            display: none;
        }

        .file-input-label {
            color: var(--text-secondary);
            cursor: pointer;
            font-size: clamp(10px, 2.5vw, 12px);
            display: flex;
            align-items: center;
            gap: clamp(2px, 0.5vw, 4px);
            padding: clamp(2px, 0.5vw, 4px) 0;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            border-radius: var(--radius-sm);
            padding: clamp(2px, 0.5vw, 4px) clamp(4px, 1vw, 6px);
        }

        .file-input-label:hover {
            color: var(--primary-color);
            background: var(--primary-light);
        }

        .send-button {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            color: white;
            width: clamp(36px, 9vw, 44px);
            height: clamp(36px, 9vw, 44px);
            border-radius: var(--radius-full);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            flex-shrink: 0;
            box-shadow: var(--shadow-md);
            font-size: clamp(14px, 3.5vw, 16px);
        }

        .send-button:hover {
            background: linear-gradient(135deg, var(--primary-dark), #004d73);
            transform: translateY(-2px) scale(1.05);
            box-shadow: var(--shadow-lg);
        }

        .send-button:active {
            transform: translateY(0) scale(1);
        }

        .send-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Scrollbar personalizada */
        .chat-messages::-webkit-scrollbar {
            width: clamp(4px, 1vw, 6px);
        }

        .chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: var(--radius-full);
        }

        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        /* Estados vazios */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-secondary);
            text-align: center;
            padding: clamp(20px, 5vw, 40px);
        }

        .empty-state i {
            font-size: clamp(32px, 8vw, 48px);
            margin-bottom: clamp(12px, 3vw, 16px);
            opacity: 0.5;
            color: var(--primary-color);
        }

        .empty-state h3 {
            font-size: clamp(16px, 4vw, 20px);
            font-weight: 600;
            margin-bottom: clamp(4px, 1vw, 8px);
            color: var(--text-primary);
        }

        .empty-state p {
            font-size: clamp(13px, 3.2vw, 15px);
            color: var(--text-secondary);
        }

        /* Indicador de status */
        .connection-status {
            position: fixed;
            top: clamp(10px, 2.5vw, 15px);
            right: clamp(10px, 2.5vw, 15px);
            padding: clamp(3px, 0.8vw, 6px) clamp(6px, 1.5vw, 10px);
            border-radius: var(--radius-full);
            font-size: clamp(10px, 2.5vw, 12px);
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .connection-status.online {
            background: rgba(72, 187, 120, 0.9);
            color: white;
        }

        .connection-status.offline {
            background: rgba(245, 101, 101, 0.9);
            color: white;
        }

        /* Loading spinner */
        .spinner-border-sm {
            width: clamp(12px, 3vw, 16px);
            height: clamp(12px, 3vw, 16px);
            border-width: 2px;
        }

        /* Modal responsivo */
        .modal-dialog {
            margin: clamp(10px, 2.5vw, 20px);
            max-width: calc(100vw - clamp(20px, 5vw, 40px));
        }

        .modal-content {
            border-radius: var(--radius-lg);
            border: none;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: clamp(12px, 3vw, 20px);
        }

        .modal-body {
            padding: clamp(12px, 3vw, 20px);
        }

        #modalImage {
            border-radius: var(--radius-md);
            max-height: 70vh;
            object-fit: contain;
        }

        /* Responsividade extrema */
        @media (max-width: 480px) {
            .user-actions a span {
                display: none;
            }
            
            .user-actions a {
                min-width: 32px;
                padding: 8px;
            }
            
            .message-item.own-message {
                margin-left: 20px;
            }
            
            .message-content {
                max-width: calc(100vw - 80px);
            }
        }

        @media (max-width: 360px) {
            .user-welcome h1 {
                font-size: 14px;
            }
            
            .user-welcome small {
                font-size: 11px;
            }
            
            .message-item.own-message {
                margin-left: 15px;
            }
        }

        /* Landscape mobile */
        @media (max-height: 500px) and (orientation: landscape) {
            .chat-header {
                min-height: 48px;
                padding: 8px 12px;
            }
            
            .user-avatar {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }
            
            .profile-img, .default-avatar {
                width: 24px;
                height: 24px;
            }
            
            .chat-input-section {
                padding: 8px 12px;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --text-primary: #f7fafc;
                --text-secondary: #a0aec0;
                --text-muted: #718096;
                --secondary-color: #2d3748;
                --chat-bg: #1a202c;
                --header-bg: #2d3748;
                --border-color: #4a5568;
                --message-bg: #2d3748;
            }
            
            .message-item:not(.own-message) .message-content {
                background: #2d3748;
                border-color: #4a5568;
            }
            
            .message-input-wrapper {
                background: #2d3748;
            }
            
            .message-input-wrapper:focus-within {
                background: #4a5568;
            }
        }

        /* Animações de entrada */
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

        .chat-container {
            animation: fadeInUp 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Otimizações de performance */
        .message-item {
            contain: layout style paint;
        }

        .chat-messages {
            contain: layout style paint;
            will-change: scroll-position;
        }

        /* Suporte a notch e safe areas */
        @supports (padding: max(0px)) {
            .chat-container {
                padding-left: max(var(--safe-area-inset-left), 0px);
                padding-right: max(var(--safe-area-inset-right), 0px);
                padding-top: max(var(--safe-area-inset-top), 0px);
                padding-bottom: max(var(--safe-area-inset-bottom), 0px);
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- Header -->
        <header class="chat-header">
            <div class="user-info">
                <div class="user-welcome">
                    <div class="user-avatar">
                        <?= strtoupper(substr($usuario['nome_gerado'], 0, 1)) ?>
                    </div>
                    <div class="user-welcome-text">
                        <h1><?= htmlspecialchars($usuario['nome_gerado']) ?></h1>
                        <small>Chat Global</small>
                    </div>
                </div>
                <nav class="user-actions">
                    <a href="../perfil/index.php">
                        <i class="bi bi-person-gear"></i>
                        <span>Perfil</span>
                    </a>
                    <a href="../login/logout.php">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Sair</span>
                    </a>
                </nav>
            </div>
        </header>

        <!-- Mensagens -->
        <main class="chat-messages" id="chatMessages">
            <?php if (empty($mensagens)): ?>
                <div class="empty-state">
                    <i class="bi bi-chat-dots"></i>
                    <h3>Nenhuma mensagem ainda</h3>
                    <p>Seja o primeiro a enviar uma mensagem!</p>
                </div>
            <?php else: ?>
                <?php foreach ($mensagens as $msg): ?>
                    <div class="message-item <?= ($msg['usuario_id'] == $usuario['id']) ? 'own-message' : '' ?>" data-message-id="<?= $msg['id'] ?>">
                        <?php if ($msg['foto_perfil']): ?>
                            <img src="../../<?= htmlspecialchars($msg['foto_perfil']) ?>" class="profile-img" alt="<?= htmlspecialchars($msg['nome_gerado']) ?>">
                        <?php else: ?>
                            <div class="default-avatar">
                                <?= strtoupper(substr($msg['nome_gerado'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="message-content">
                            <div class="message-header">
                                <span class="username"><?= htmlspecialchars($msg['nome_gerado']) ?></span>
                                <span class="timestamp"><?= date('H:i', strtotime($msg['enviado_em'])) ?></span>
                            </div>
                            
                            <?php if (!empty($msg['mensagem'])): ?>
                                <div class="message-text"><?= nl2br(htmlspecialchars($msg['mensagem'])) ?></div>
                            <?php endif; ?>
                            
                            <?php if ($msg['imagem']): ?>
                                <img src="../../<?= htmlspecialchars($msg['imagem']) ?>" class="message-image" alt="Imagem" onclick="openImageModal(this.src)">
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>

        <!-- Input -->
        <section class="chat-input-section">
            <?php if ($erro): ?>
                <div class="error-message">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data" id="chatForm">
                <div class="input-container">
                    <div class="message-input-wrapper">
                        <textarea name="mensagem" id="mensagem" class="form-control" placeholder="Mensagem..." rows="1"></textarea>
                        <div class="file-input-wrapper">
                            <input type="file" name="imagem" id="imagem" class="file-input" accept="image/*">
                            <label for="imagem" class="file-input-label">
                                <i class="bi bi-paperclip"></i>
                                <span id="file-name">Anexar</span>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="send-button">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </form>
        </section>
    </div>

    <!-- Modal para visualizar imagens -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Visualizar Imagem</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="/placeholder.svg" class="img-fluid" alt="Imagem ampliada">
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Configuração de altura dinâmica para mobile
        function setVH() {
            let vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }

        // Configurar altura inicial e em redimensionamento
        setVH();
        window.addEventListener('resize', setVH);
        window.addEventListener('orientationchange', () => {
            setTimeout(setVH, 100);
        });

        let isUserScrolling = false;
        let scrollTimeout;

        // Auto-scroll para a última mensagem (suave)
        function scrollToBottom(smooth = true) {
            const chatMessages = document.getElementById('chatMessages');
            if (smooth) {
                chatMessages.scrollTo({
                    top: chatMessages.scrollHeight,
                    behavior: 'smooth'
                });
            } else {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }

        // Detectar se o usuário está fazendo scroll manual
        function handleUserScroll() {
            const chatMessages = document.getElementById('chatMessages');
            const isAtBottom = chatMessages.scrollTop + chatMessages.clientHeight >= chatMessages.scrollHeight - 50;
            
            if (!isAtBottom) {
                isUserScrolling = true;
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    isUserScrolling = false;
                }, 3000);
            } else {
                isUserScrolling = false;
            }
        }

        // Scroll para baixo ao carregar a página
        window.addEventListener('load', function() {
            const chatMessages = document.getElementById('chatMessages');
            const imgs = chatMessages.querySelectorAll('img');
            
            if (imgs.length === 0) {
                scrollToBottom(false);
            } else {
                let loaded = 0;
                imgs.forEach(img => {
                    img.onload = img.onerror = () => {
                        loaded++;
                        if (loaded === imgs.length) {
                            scrollToBottom(false);
                        }
                    };
                });
            }
        });

        // Detectar scroll do usuário
        document.getElementById('chatMessages').addEventListener('scroll', handleUserScroll);

        // Função para abrir modal de imagem
        function openImageModal(imageSrc) {
            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            document.getElementById('modalImage').src = imageSrc;
            modal.show();
        }

        // Preview do arquivo selecionado
        document.getElementById('imagem').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const label = document.querySelector('.file-input-label span');
            
            if (file) {
                label.textContent = `Imagem: ${file.name.substring(0, 15)}${file.name.length > 15 ? '...' : ''}`;
                label.parentElement.style.color = 'var(--success-color)';
            } else {
                label.textContent = 'Anexar';
                label.parentElement.style.color = 'var(--text-secondary)';
            }
        });

        // Auto-resize do textarea
        const textarea = document.getElementById('mensagem');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Envio com Enter e quebra de linha com Shift+Enter
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('chatForm').requestSubmit();
            }
        });

        // Indicador de loading no botão
        document.getElementById('chatForm').addEventListener('submit', function() {
            const button = document.querySelector('.send-button');
            button.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div>';
            button.disabled = true;
        });

        // Sistema de atualização automática
        let ultimaMensagemId = 0;
        let isUpdating = false;
        let updateInterval;

        function getLastMessageId() {
            const messages = document.querySelectorAll('.message-item[data-message-id]');
            if (messages.length > 0) {
                const lastMessage = messages[messages.length - 1];
                const messageId = lastMessage.dataset.messageId;
                if (messageId && !isNaN(messageId)) {
                    ultimaMensagemId = parseInt(messageId);
                }
            }
        }

        async function checkForNewMessages() {
            if (isUpdating) return;
            
            try {
                isUpdating = true;
                const response = await fetch(`index.php?ajax=get_messages&ultima_id=${ultimaMensagemId}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.error) {
                    console.error('Erro do servidor:', data.error);
                    return;
                }
                
                if (data && Array.isArray(data) && data.length > 0) {
                    addNewMessages(data);
                    if (!isUserScrolling) {
                        scrollToBottom(true);
                    }
                }
            } catch (error) {
                console.error('Erro ao buscar mensagens:', error);
            } finally {
                isUpdating = false;
            }
        }

        function addNewMessages(mensagens) {
            const chatMessages = document.getElementById('chatMessages');
            const currentUserId = <?= $usuario['id'] ?>;
            
            const emptyState = chatMessages.querySelector('.empty-state');
            if (emptyState) {
                emptyState.remove();
            }
            
            mensagens.forEach(msg => {
                const existingMessage = document.querySelector(`[data-message-id="${msg.id}"]`);
                if (existingMessage) {
                    return;
                }
                
                const isOwnMessage = msg.usuario_id == currentUserId;
                const messageElement = createMessageElement(msg, isOwnMessage);
                chatMessages.appendChild(messageElement);
                ultimaMensagemId = Math.max(ultimaMensagemId, parseInt(msg.id));
            });
        }

        function createMessageElement(msg, isOwnMessage) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message-item ${isOwnMessage ? 'own-message' : ''}`;
            messageDiv.dataset.messageId = msg.id;
            
            const avatarHtml = msg.foto_perfil 
                ? `<img src="../../${msg.foto_perfil}" class="profile-img" alt="${msg.nome_gerado}">`
                : `<div class="default-avatar">${msg.nome_gerado.charAt(0).toUpperCase()}</div>`;
            
            const imageHtml = msg.imagem 
                ? `<img src="../../${msg.imagem}" class="message-image" alt="Imagem" onclick="openImageModal(this.src)">`
                : '';
            
            const messageText = msg.mensagem 
                ? `<div class="message-text">${msg.mensagem.replace(/\n/g, '<br>')}</div>`
                : '';
            
            const timestamp = new Date(msg.enviado_em).toLocaleTimeString('pt-BR', {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            messageDiv.innerHTML = `
                ${avatarHtml}
                <div class="message-content">
                    <div class="message-header">
                        <span class="username">${msg.nome_gerado}</span>
                        <span class="timestamp">${timestamp}</span>
                    </div>
                    ${messageText}
                    ${imageHtml}
                </div>
            `;
            
            return messageDiv;
        }

        // Inicializar sistema
        window.addEventListener('load', function() {
            getLastMessageId();
            updateInterval = setInterval(checkForNewMessages, 3000);
            
            document.getElementById('chatForm').addEventListener('submit', function() {
                setTimeout(() => {
                    getLastMessageId();
                    checkForNewMessages();
                    setTimeout(() => scrollToBottom(true), 500);
                }, 1000);
            });
            
            setTimeout(checkForNewMessages, 2000);
        });

        // Pausar updates quando a aba não está ativa
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                if (updateInterval) {
                    clearInterval(updateInterval);
                }
            } else {
                updateInterval = setInterval(checkForNewMessages, 3000);
                checkForNewMessages();
            }
        });

        // Otimizações para touch devices
        if ('ontouchstart' in window) {
            document.body.style.webkitTouchCallout = 'none';
            document.body.style.webkitUserSelect = 'none';
            document.body.style.userSelect = 'none';
            
            // Permitir seleção apenas em mensagens
            document.querySelectorAll('.message-text').forEach(el => {
                el.style.webkitUserSelect = 'text';
                el.style.userSelect = 'text';
            });
        }

        // Prevenção de zoom em inputs no iOS
        if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
            document.querySelectorAll('input, textarea, select').forEach(el => {
                el.addEventListener('focus', function() {
                    if (parseFloat(el.style.fontSize) < 16) {
                        el.style.fontSize = '16px';
                    }
                });
            });
        }
    </script>
</body>
</html>
