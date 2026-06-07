<?php 
class Personagem {
    
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
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

    public function buscarParaEditar(int $personagem_id, int $usuario_id): object|false {

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

    public function editar(int $personagem_id, int $usuario_id, array $dados): bool {
        
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