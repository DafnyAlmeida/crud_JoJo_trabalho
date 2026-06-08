<?php

// Pega o caminho da pasta public a partir da pasta atual
function caminho_publico() {
    return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "public";
}

// Coloca uploads no final
function caminho_uploads() {
    return caminho_publico() . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR;
}

// Cria um nome para a pasta tirando espaços e letras maisculas
function criar_nome_pasta($nome_base) {
    // Tira espaço
    $nome_pasta = trim((string) $nome_base);

    // Tira acentos
    $convertido = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $nome_pasta);
    if ($convertido !== false) {
        $nome_pasta = $convertido;
    }

    // Tudo minusculo
    $nome_pasta = mb_strtolower($nome_pasta, "UTF-8");

    // Troca espaços por -
    $nome_pasta = preg_replace('/[^a-z0-9]+/', '-', $nome_pasta);
    $nome_pasta = trim($nome_pasta, '-');

    // Verifica se ficou algo
    return $nome_pasta !== '' ? $nome_pasta : 'sem-nome';
}

// Vê se veio arquivo e se ele esta no padrão aceito
function validar_imagem_enviada($arquivo, $campo) {
    
    if (!is_array($arquivo) || !isset($arquivo['error'])) {
        throw new RuntimeException("A imagem {$campo} não foi enviada.");
    }

    $mensagensUpload = [
        UPLOAD_ERR_INI_SIZE   => 'O arquivo excedeu o limite configurado no PHP.',
        UPLOAD_ERR_FORM_SIZE  => 'O arquivo excedeu o limite permitido pelo formulário.',
        UPLOAD_ERR_PARTIAL    => 'O upload do arquivo foi feito parcialmente.',
        UPLOAD_ERR_NO_FILE    => 'Nenhum arquivo foi selecionado.',
        UPLOAD_ERR_NO_TMP_DIR => 'A pasta temporária do servidor não foi encontrada.',
        UPLOAD_ERR_CANT_WRITE => 'O servidor não conseguiu gravar o arquivo.',
        UPLOAD_ERR_EXTENSION  => 'Uma extensão do PHP interrompeu o upload.'
    ];

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $motivo = $mensagensUpload[$arquivo['error']] ?? ('Código: ' . $arquivo['error']);
        throw new RuntimeException("Erro no upload de {$campo}: {$motivo}");
    }

    if (empty($arquivo['tmp_name']) || !is_uploaded_file($arquivo['tmp_name'])) {
        throw new RuntimeException("O arquivo enviado em {$campo} é inválido.");
    }

    if (($arquivo['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException("A imagem {$campo} ultrapassa o limite de 5 MB.");
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif'
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($arquivo['tmp_name']);

    if (!isset($tiposPermitidos[$mime])) {
        throw new RuntimeException("Formato inválido em {$campo}. Envie JPG, PNG, WEBP ou GIF.");
    }

    return $tiposPermitidos[$mime];
}

// Cria a pasta onde as imagens serão salvas e verifica se ela já não existe
function preparar_destino_upload($tipo_pasta, $nome_base) {

    $tipo_pasta = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $tipo_pasta);

    if ($tipo_pasta === '') {
        throw new RuntimeException('Tipo de pasta inválido para upload.');
    }

    $nome_pasta = criar_nome_pasta($nome_base);

    $pasta_destino = caminho_uploads()
        . $tipo_pasta
        . DIRECTORY_SEPARATOR
        . $nome_pasta
        . DIRECTORY_SEPARATOR;

    if (!is_dir($pasta_destino)) {
        if (!mkdir($pasta_destino, 0777, true) && !is_dir($pasta_destino)) {
            throw new RuntimeException("Não foi possível criar a pasta: {$pasta_destino}");
        }
    }

    return [
        'fisico' => $pasta_destino,
        'banco'  => 'uploads/' . $tipo_pasta . '/' . $nome_pasta . '/'
    ];
}

// Salva uma única imagem enviada - retorna caminho para salvar no banco
function salvar_imagem($campo, $tipo_pasta, $nome_base) {

    if (!isset($_FILES[$campo])) {
        throw new RuntimeException("A imagem {$campo} não chegou ao formulário.");
    }

    $arquivo = $_FILES[$campo];
    $extensao = validar_imagem_enviada($arquivo, $campo);
    $destino = preparar_destino_upload($tipo_pasta, $nome_base);

    $novo_nome = uniqid($campo . '_', true) . '.' . $extensao;
    $caminho_final = $destino['fisico'] . $novo_nome;

    if (!move_uploaded_file($arquivo['tmp_name'], $caminho_final)) {
        throw new RuntimeException("Não foi possível salvar a imagem {$campo}.");
    }

    return $destino['banco'] . $novo_nome;
}

// Percorre um array salvando as imagens enviadas - retorna caminho para salvar no banco
function salvar_imagem_array($campo, $index, $tipo_pasta, $nome_base) {
    if (!isset($_FILES[$campo]['name'][$index])) {
        throw new RuntimeException("A imagem {$campo}[{$index}] não chegou ao formulário.");
    }

    $arquivo = [
        'name'     => $_FILES[$campo]['name'][$index],
        'type'     => $_FILES[$campo]['type'][$index] ?? '',
        'tmp_name' => $_FILES[$campo]['tmp_name'][$index],
        'error'    => $_FILES[$campo]['error'][$index],
        'size'     => $_FILES[$campo]['size'][$index] ?? 0
    ];

    $extensao = validar_imagem_enviada($arquivo, $campo . '[' . $index . ']');
    $destino = preparar_destino_upload($tipo_pasta, $nome_base);

    $novo_nome = uniqid($campo . '_', true) . '.' . $extensao;
    $caminho_final = $destino['fisico'] . $novo_nome;

    if (!move_uploaded_file($arquivo['tmp_name'], $caminho_final)) {
        throw new RuntimeException("Não foi possível salvar a imagem {$campo}[{$index}].");
    }

    return $destino['banco'] . $novo_nome;
}

// Pega um caminho do banco e coloca em pratica
function caminho_fisico_publico($caminho) {

    $caminho = trim(str_replace('\\', '/', (string) $caminho));
    $publico = str_replace('\\', '/', caminho_publico());

    if ($caminho === $publico || strpos($caminho, $publico . '/') === 0) {
        return str_replace('/', DIRECTORY_SEPARATOR, $caminho);
    }

    // Compatibilidade com chamadas antigas usando ../../public/uploads/...
    $caminho = preg_replace('#^(?:\.\./)+public/#', '', $caminho);
    $caminho = ltrim($caminho, '/');

    if ($caminho === '' || strpos($caminho, '..') !== false) {
        throw new RuntimeException('Caminho de arquivo inválido.');
    }

    return caminho_publico()
        . DIRECTORY_SEPARATOR
        . str_replace('/', DIRECTORY_SEPARATOR, $caminho);
}

// Função para delatar um unico arquivo
function deletar_arquivo($arquivo) {

    if (empty($arquivo)) {
        return;
    }

    $caminho = caminho_fisico_publico($arquivo);

    if (is_file($caminho)) {
        unlink($caminho);
    }
}

// Função para deletar toda uma pasta
function deletar_pasta($pasta) {

    if (empty($pasta)) {
        return;
    }

    $caminho = caminho_fisico_publico($pasta);

    // Permite passar uma imagem salva no banco para apagar sua pasta.
    if (is_file($caminho)) {
        $caminho = dirname($caminho);
    }

    if (!is_dir($caminho)) {
        return;
    }

    $itens = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($caminho, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($itens as $item) {
        if ($item->isDir()) {
            rmdir($item->getPathname());
        } else {
            unlink($item->getPathname());
        }
    }

    rmdir($caminho);
}