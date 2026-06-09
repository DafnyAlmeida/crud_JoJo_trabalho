<?php 

// --------------------------------------
// FUNÇÕES INDEX DA RAIZ (PÁGINA INICIAL)
// --------------------------------------

// Função para pegar a quantidade de stands, personagens e referencias em todas as partes
function listarTotaisPorParte(PDO $pdo, int $usuario_id): array
{
    $sql = "
        SELECT
            pt.id AS parte_id,
            COUNT(DISTINCT pp.personagem_id) AS personagens,
            COUNT(DISTINCT s.id) AS stands,
            COUNT(DISTINCT r.id) AS referencias
        FROM partes pt
        LEFT JOIN personagens_partes pp
            ON pp.parte_id = pt.id
        LEFT JOIN personagens p
            ON p.id = pp.personagem_id
        LEFT JOIN stands s
            ON s.personagem_id = p.id
            AND s.usuario_id = :usuario_stands
        LEFT JOIN referencias r
            ON r.parte_id = pt.id
            AND r.usuario_id = :usuario_referencias
        GROUP BY pt.id
        ORDER BY pt.id ASC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":usuario_stands" => $usuario_id,
        ":usuario_referencias" => $usuario_id
    ]);

    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totais = [];

    foreach ($resultado as $linha) {
        
        $totais[(int) $linha["parte_id"]] = [
            "stands" => (int) $linha["stands"],
            "personagens" => (int) $linha["personagens"],
            "referencias" => (int) $linha["referencias"]
        ];
    }

    return $totais;
}

// ------------------------------------------------------------------------------
// FUNÇÕES VISUALIZAR PARTES (PÁGINA ONDE LISTA STANDS, PERSONAGENS E REFERENCIAS)
// -------------------------------------------------------------------------------

function validar_id_get(String $campo_nome): int|false|null {
    return filter_input(INPUT_GET, $campo_nome, FILTER_VALIDATE_INT);
}

function validar_id_post(string $campo_nome): int|false|null {
    return filter_input(INPUT_POST, $campo_nome, FILTER_VALIDATE_INT);
}

// Função para buscar quantidade de stands, personagens e referencias de uma unica parte
function pegarTotal(PDO $pdo, int $id, String $consulta): int {
    $sql = "$consulta";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":parte_id" => $id
    ]);

    return (int) $stmt->fetchColumn();
}

// --------------------------------------
// FUNÇÕES VISUALIZAR DOS PERSONAGENS
// --------------------------------------
function formatar_papel(?string $papel): string {
    $papeis = [
        "vilao" => "Vilão",
        "protagonista" => "Protagonista",
        "jojobro" => "JoJoBro"
    ];

    return $papeis[$papel] ?? ucfirst((string) $papel);
}

function buscarParteId(PDO $pdo, int $personagem_id, int $usuario_id) {

    $sql = "
        SELECT pp.parte_id
        FROM personagens_partes pp

        INNER JOIN personagens p
            ON p.id = pp.personagem_id

        WHERE pp.personagem_id = :personagem_id
        AND p.usuario_id = :usuario_id

        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":personagem_id" => $personagem_id,
        ":usuario_id" => $usuario_id
    ]);

    $parte_id = $stmt->fetchColumn();

    return $parte_id ? (int) $parte_id : 0;
}

// --------------------------------------
// FUNÇÕES EXCLUIR DOS PERSONAGENS
// --------------------------------------

function buscarDeletar(PDO $pdo, int $id_personagem, int $usuario_id): object|false {

    $sql = "SELECT foto_anime
    FROM personagens
    WHERE id = :id
    AND usuario_id = :usuario_id
    LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":id" => $id_personagem,
        ":usuario_id" => $usuario_id
    ]);

    return $stmt->fetch(PDO::FETCH_OBJ);
}


function deletar(PDO $pdo, int $id_personagem, int $usuario_id): bool {

    $pdo->beginTransaction();

    try {

        $sql = "
            DELETE pp
            FROM personagens_partes pp

            INNER JOIN personagens p
                ON p.id = pp.personagem_id

            WHERE pp.personagem_id = :personagem_id
            AND p.usuario_id = :usuario_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":personagem_id" => $id_personagem,
            ":usuario_id" => $usuario_id
        ]);

        $sql = "
            DELETE FROM personagens
            WHERE id = :personagem_id
            AND usuario_id = :usuario_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":personagem_id" => $id_personagem,
            ":usuario_id" => $usuario_id
        ]);

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException("Personagem não encontrado.");
        }

        $pdo->commit();

        return true;

    } catch (Throwable $erro) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $erro;
    }
}

// --------------------------------------
// FUNÇÕES EDITAR DOS PERSONAGENS
// --------------------------------------

function buscarParaEditar(PDO $pdo, int $personagem_id, int $usuario_id): object|false {

    $sql = "
        SELECT *
        FROM personagens
        WHERE id = :id
        AND usuario_id = :usuario_id
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":id" => $personagem_id,
        ":usuario_id" => $usuario_id
    ]);

    return $stmt->fetch(PDO::FETCH_OBJ);
}

function editar(PDO $pdo, int $personagem_id, int $usuario_id, array $dados): bool {
    
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

    $stmt = $pdo->prepare($sql);

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

// --------------------------------------
// FUNÇÕES ADICIONAR STAND
// --------------------------------------

// Função que busca personagens que ainda não tem stands
function buscarPersonagensDisponiveis(PDO $pdo): array
{
    $sql = "SELECT DISTINCT p.id, p.nome FROM personagens p INNER JOIN personagens_partes pp ON p.id = pp.personagem_id INNER JOIN partes pt ON pp.parte_id = pt.id LEFT JOIN stands s ON p.id = s.personagem_id WHERE s.id IS NULL AND pt.numero NOT IN (1, 2)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

// --------------------------------------
// FUNÇÕES INDEX STAND
// --------------------------------------

function contarStands(PDO $pdo, int $parte_id, int $usuario_id): int {
    $sql = "
        SELECT COUNT(DISTINCT p.id)
        FROM personagens p

        INNER JOIN personagens_partes pp
            ON pp.personagem_id = p.id

        WHERE pp.parte_id = :parte_id
        AND p.usuario_id = :usuario_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":parte_id" => $parte_id,
        ":usuario_id" => $usuario_id
    ]);

    return (int) $stmt->fetchColumn();
}

function listarPorParte(PDO $pdo, int $parte_id, int $usuario_id, int $limite, int $offset): array {
    $sql = "
        SELECT DISTINCT
            s.id,
            s.nome,
            s.descricao,
            s.foto_catalogo,
            s.foto_manga,
            p.nome AS personagem_nome
        FROM stands s
        INNER JOIN personagens p
            ON p.id = s.personagem_id
        INNER JOIN personagens_partes pp
            ON pp.personagem_id = p.id
        WHERE pp.parte_id = :parte_id
        AND s.usuario_id = :usuario_id
        ORDER BY s.id DESC
        LIMIT :limite OFFSET :offset
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(":parte_id", $parte_id, PDO::PARAM_INT);
    $stmt->bindValue(":usuario_id", $usuario_id, PDO::PARAM_INT);
    $stmt->bindValue(":limite", $limite, PDO::PARAM_INT);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

// Função que garante a exibição de texto
function escapar(?string $valor): string  {
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function link_pagina(int $parte_id, int $pagina): string {
    return "index.php?" . http_build_query([
        "parte_id" => $parte_id,
        "pagina" => $pagina
    ]);
}

function pegar_pagina_atual(): int {
    $pagina = filter_input(INPUT_GET, "pagina", FILTER_VALIDATE_INT);

    if (!$pagina || $pagina < 1) {
        return 1;
    }

    return $pagina;
}

// Formata para ja estar do jeito certo
function caminho_foto(?string $foto, string $prefixo = "../"): string {

    if (empty($foto)) {
        return "";
    }

    return $prefixo . ltrim($foto, "/");
}

// Vê se o campo é nulo ou não
function texto_ou_padrao(?string $texto, string $mensagem_padrao): string
{
    $texto = trim((string) $texto);

    if ($texto === "") {
        return $mensagem_padrao;
    }

    return $texto;
}

function processar_upload_foto(
    string $campo,
    string $pasta,
    string $nome,
    ?string $foto_antiga,
    array &$fotos_novas_para_remover,
    array &$fotos_antigas_para_apagar
): ?string {
    if (empty($_FILES[$campo]["name"])) {
        return $foto_antiga;
    }

    $nova_foto = salvar_imagem(
        $campo,
        $pasta,
        $nome
    );

    $fotos_novas_para_remover[] = $nova_foto;

    if (!empty($foto_antiga)) {
        $fotos_antigas_para_apagar[] = $foto_antiga;
    }

    return $nova_foto;
}

function imagemStand(?string $caminho): string {
    
    $caminho = trim((string) $caminho);

    if ($caminho === "") {
        return "";
    }

    if (str_starts_with($caminho, "../") || str_starts_with($caminho, "http")) {
        return $caminho;
    }

    return "../" . $caminho;
}