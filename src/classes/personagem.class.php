<?php 

class Personagem {
    
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function listar() {}

    // Conta quantos perosnagens um user criou em uma parte
    public function contarPersonagens(int $parte_id, int $usuario_id): int {
        $sql = "
            SELECT COUNT(DISTINCT p.id)
            FROM personagens p

            INNER JOIN personagens_partes pp
                ON pp.personagem_id = p.id

            WHERE pp.parte_id = :parte_id
            AND p.usuario_id = :usuario_id
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ":parte_id" => $parte_id,
            ":usuario_id" => $usuario_id
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function listarPorParte(int $parte_id, int $usuario_id, int $limite, int $offset): array {
        $sql = "
            SELECT DISTINCT
                p.id,
                p.usuario_id,
                p.nome,
                p.foto_catalogo,
                (
                    SELECT s.nome
                    FROM stands s
                    WHERE s.personagem_id = p.id
                    AND s.usuario_id = :usuario_stand
                    ORDER BY s.id ASC
                    LIMIT 1
                ) AS stand_nome
            FROM personagens p

            INNER JOIN personagens_partes pp
                ON pp.personagem_id = p.id

            WHERE pp.parte_id = :parte_id
            AND p.usuario_id = :usuario_personagem

            ORDER BY p.id DESC

            LIMIT :limite OFFSET :offset
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(":usuario_stand", $usuario_id, PDO::PARAM_INT);
        $stmt->bindValue(":parte_id", $parte_id, PDO::PARAM_INT);
        $stmt->bindValue(":usuario_personagem", $usuario_id, PDO::PARAM_INT);
        $stmt->bindValue(":limite", $limite, PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // FUNÇÕES MENORES

    public function formatar_papel(?string $papel): string {
        $papeis = [
            "vilao" => "Vilão",
            "protagonista" => "Protagonista",
            "jojobro" => "JoJoBro"
        ];

        return $papeis[$papel] ?? ucfirst((string) $papel);
    }

    public function buscarParteId(int $personagem_id, int $usuario_id) {

        $sql = "
            SELECT pp.parte_id
            FROM personagens_partes pp

            INNER JOIN personagens p
                ON p.id = pp.personagem_id

            WHERE pp.personagem_id = :personagem_id
            AND p.usuario_id = :usuario_id

            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ":personagem_id" => $personagem_id,
            ":usuario_id" => $usuario_id
        ]);

        $parte_id = $stmt->fetchColumn();

        return $parte_id ? (int) $parte_id : 0;
    }

    public function buscarDetalhes(int $personagem_id, int $usuario_id, int $parte_id): object|false {

        $sql = "
            SELECT
                p.*,
                pp.idade,
                pp.papel,
                pa.nome AS parte_nome,
                (
                    SELECT s.nome
                    FROM stands s
                    WHERE s.personagem_id = p.id
                    AND s.usuario_id = p.usuario_id
                    ORDER BY s.id ASC
                    LIMIT 1
                ) AS stand_nome
            FROM personagens p

            INNER JOIN personagens_partes pp
                ON pp.personagem_id = p.id

            INNER JOIN partes pa
                ON pa.id = pp.parte_id

            WHERE p.id = :personagem_id
            AND p.usuario_id = :usuario_id
            AND pp.parte_id = :parte_id

            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ":personagem_id" => $personagem_id,
            ":usuario_id" => $usuario_id,
            ":parte_id" => $parte_id
        ]);

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function buscarDeletar(int $id_personagem, int $usuario_id): object|false {

        $sql = "SELECT foto_anime
        FROM personagens
        WHERE id = :id
        AND usuario_id = :usuario_id
        LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ":id" => $id_personagem,
            ":usuario_id" => $usuario_id
        ]);

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function deletar(int $id_personagem, int $usuario_id): bool {

        $this->pdo->beginTransaction();

        try {

            $sql = "
                DELETE pp
                FROM personagens_partes pp

                INNER JOIN personagens p
                    ON p.id = pp.personagem_id

                WHERE pp.personagem_id = :personagem_id
                AND p.usuario_id = :usuario_id
            ";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ":personagem_id" => $id_personagem,
                ":usuario_id" => $usuario_id
            ]);

            $sql = "
                DELETE FROM personagens
                WHERE id = :personagem_id
                AND usuario_id = :usuario_id
            ";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ":personagem_id" => $id_personagem,
                ":usuario_id" => $usuario_id
            ]);

            if ($stmt->rowCount() !== 1) {
                throw new RuntimeException("Personagem não encontrado.");
            }

            $this->pdo->commit();

            return true;

        } catch (Throwable $erro) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $erro;
        }
    }

    public function buscarParaEditar(
        int $personagem_id,
        int $usuario_id
    ): object|false {
        $sql = "
            SELECT *
            FROM personagens
            WHERE id = :id
            AND usuario_id = :usuario_id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ":id" => $personagem_id,
            ":usuario_id" => $usuario_id
        ]);

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function editar(
        int $personagem_id,
        int $usuario_id,
        array $dados
    ): bool {
        $sql = "
            UPDATE personagens SET
                nome = :nome,
                biografia = :biografia,
                foto_anime = :foto_anime,
                foto_manga = :foto_manga,
                foto_catalogo = :foto_catalogo,
                foto_biografia = :foto_biografia,
                infor_gerais = :infor_gerais
            WHERE id = :id
            AND usuario_id = :usuario_id
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ":nome" => $dados["nome"],
            ":biografia" => $dados["biografia"],
            ":foto_anime" => $dados["foto_anime"],
            ":foto_manga" => $dados["foto_manga"],
            ":foto_catalogo" => $dados["foto_catalogo"],
            ":foto_biografia" => $dados["foto_biografia"],
            ":infor_gerais" => $dados["infor_gerais"],
            ":id" => $personagem_id,
            ":usuario_id" => $usuario_id
        ]);
    }


}