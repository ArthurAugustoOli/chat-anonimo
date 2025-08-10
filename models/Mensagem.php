<?php
require_once __DIR__ . '/../config/config.php';

class Mensagem {
    public static function enviar($usuario_id, $mensagem = null, $imagem = null) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO mensagens (usuario_id, mensagem, imagem) VALUES (?, ?, ?)");
        return $stmt->execute([$usuario_id, $mensagem, $imagem]);
    }

    public static function todasComUsuarios() {
        global $pdo;
        $stmt = $pdo->query("
            SELECT m.*, u.nome_gerado, u.foto_perfil 
            FROM mensagens m
            JOIN usuarios u ON m.usuario_id = u.id
            ORDER BY m.enviado_em DESC
        ");
        return $stmt->fetchAll();
    }

    // Método para buscar apenas mensagens novas (necessário para AJAX)
    public static function buscarNovasMensagens($ultimaId) {
        global $pdo;
        $sql = "SELECT m.*, u.nome_gerado, u.foto_perfil, m.usuario_id 
                FROM mensagens m 
                JOIN usuarios u ON m.usuario_id = u.id 
                WHERE m.id > :ultima_id 
                ORDER BY m.enviado_em ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':ultima_id', $ultimaId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
