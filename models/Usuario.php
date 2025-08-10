<?php
require_once __DIR__ . '/../config/config.php';

class Usuario {
    public static function cadastrar($sexo, $login, $senha) {
        global $pdo;

        // Geração de nome aleatório
        $profissoes = [
            'M' => [
                'Programador', 'Engenheiro', 'Psicólogo', 'Arquiteto', 'Médico',
                'Veterinário', 'Nutricionista', 'Administrador', 'Desenvolvedor de software', 
                'Cientista de dados', 'Economista', 'Advogado', 'Professor', 'Biólogo', 
                'Fisioterapeuta', 'Fonoaudiólogo', 'Técnico em enfermagem', 'Farmacêutico',
                'Biomédico', 'Geólogo', 'Estatístico', 'Filósofo', 'Jornalista',
                'Designer gráfico', 'Fotógrafo', 'Analista de marketing', 'Gestor ambiental',
                'Oceanógrafo', 'Meteorologista', 'Psicoterapeuta', 'Bibliotecário',
                'Relações públicas', 'Publicitário', 'Turismólogo', 'Administrativo',
                'Piloto', 'Engenheiro civil', 'Engenheiro mecânico', 'Engenheiro elétrico',
                'Engenheiro químico', 'Engenheiro ambiental', 'Arqueólogo', 'Historiador',
                'Geógrafo', 'Economista domético', 'Consultor', 'Psicanalista', 'Coach'
            ],
            'F' => [
                'Programadora', 'Engenheira', 'Psicóloga', 'Arquiteta', 'Médica',
                'Veterinária', 'Nutricionista', 'Administradora', 'Desenvolvedora de software',
                'Cientista de dados', 'Economista', 'Advogada', 'Professora', 'Bióloga',
                'Fisioterapeuta', 'Fonoaudióloga', 'Técnica em enfermagem', 'Farmacêutica',
                'Biomédica', 'Geóloga', 'Estatística', 'Filósofa', 'Jornalista',
                'Designer gráfica', 'Fotógrafa', 'Analista de marketing', 'Gestora ambiental',
                'Oceanógrafa', 'Meteorologista', 'Psicoterapeuta', 'Bibliotecária',
                'Relações públicas', 'Publicitária', 'Turismóloga', 'Administrativa',
                'Pilota', 'Engenheira civil', 'Engenheira mecânica', 'Engenheira elétrica',
                'Engenheira química', 'Engenheira ambiental', 'Arqueóloga', 'Historiadora',
                'Geógrafa', 'Consultora', 'Psicanalista', 'Coach'
            ]
        ];
        $prefixo = $sexo === 'M' ? 'Dr. ' : 'Dra. ';
        $profissao = $profissoes[$sexo][array_rand($profissoes[$sexo])];
        $nomeGerado = $prefixo . $profissao;

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO usuarios (sexo, nome_gerado, login, senha) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$sexo, $nomeGerado, $login, $senhaHash]);
    }

    public static function autenticar($login, $senha) {
        global $pdo;

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE login = ?");
        $stmt->execute([$login]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            return $usuario;
        }
        return false;
    }

    public static function buscarPorId($id) {
        global $pdo;

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function atualizarFotoPerfil($id, $caminho) {
        global $pdo;

        $stmt = $pdo->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
        return $stmt->execute([$caminho, $id]);
    }
}
