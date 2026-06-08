# JoJoDex

Sistema web desenvolvido em **PHP puro**, com foco em cadastro, organização e visualização de informações sobre o universo de *JoJo's Bizarre Adventure*.

O projeto permite gerenciar **Partes**, **Personagens**, **Stands** e **Referências**, com autenticação de usuários, upload de imagens e uma interface visual personalizada inspirada na estética de JoJo.

---

## Sobre o projeto

O **JoJoDex** é um sistema CRUD criado para organizar dados relacionados às partes de JoJo. Cada usuário pode cadastrar e visualizar informações como personagens, stands, habilidades, imagens e referências culturais presentes na obra.

O sistema foi desenvolvido como projeto acadêmico utilizando PHP, MySQL, Tailwind CSS e Docker.

---

## Funcionalidades

* Cadastro e login de usuários
* Proteção de páginas com sessão
* Listagem das partes de JoJo
* Cadastro, edição, visualização e exclusão de personagens
* Cadastro, edição, visualização e exclusão de stands
* Cadastro, edição, visualização e exclusão de referências
* Upload de imagens
* Organização de arquivos em pastas dentro de `public/uploads`
* Contagem de personagens, stands e referências por parte
* Uso de prepared statements com PDO para segurança no banco

---

## Estrutura do projeto

```txt
projeto/
│
├── public/
│   ├── index.php
│   ├── uploads/
│   ├── personagens/
│   ├── stands/
│   ├── referencias/
│   └── partes/
│
├── src/
│   ├── config/
│   │   └── conexao.php
│   │
│   ├── includes/
│   │   └── bloqueio.php
│   │
│   ├── functions/
│   │   ├── upload.php
│   │   └── gerais.php
│   │
│   └── classes/
│       └── personagem.class.php
│
├── database/
│   └── script.sql
│
├── docker-compose.yml
├── Dockerfile
└── README.md
```

---

## Banco de dados

O sistema utiliza tabelas para armazenar usuários, partes, personagens, stands, habilidades e referências.

Principais tabelas:

* `usuarios`
* `partes`
* `personagens`
* `personagens_partes`
* `stands`
* `stand_habilidades`
* `referencias`

A tabela `personagens_partes` permite relacionar um personagem com uma ou mais partes.

---

## Upload de imagens

O projeto possui funções específicas para upload de imagens.

As imagens são salvas dentro da pasta:

```txt
public/uploads/
```

Exemplo de organização:

```txt
public/uploads/stands/star-platinum/
public/uploads/personagens/jotaro-kujo/
public/uploads/referencias/referencia-x/
```

O sistema valida:

* Se o arquivo foi enviado corretamente
* Se o arquivo é realmente uma imagem
* Se o tamanho não ultrapassa o limite permitido
* Se o formato é JPG, PNG, WEBP ou GIF

---

## Segurança

O projeto utiliza algumas práticas de segurança, como:

* `password_hash()` para criptografar senhas
* `password_verify()` para validar senhas no login
* `filter_var()` para validar e-mails e IDs
* `htmlspecialchars()` para evitar execução de HTML ou JavaScript malicioso
* `prepare()` e `execute()` com PDO para evitar SQL Injection
* `session_start()` e verificação de sessão para páginas protegidas
* Validação de arquivos enviados por upload

---

## Como executar o projeto

### 1. Clone o repositório

```bash
git clone URL_DO_REPOSITORIO
```

### 2. Acesse a pasta do projeto

```bash
cd nome-do-projeto
```

### 3. Suba os containers com Docker

```bash
docker compose up -d
```

### 4. Acesse o sistema

```txt
http://localhost:8080
```

### 5. Acesse o phpMyAdmin

```txt
http://localhost:8081
```

---

## Configuração do banco

No arquivo de conexão, confira se as informações estão de acordo com o `docker-compose.yml`.

Exemplo:

```php
$host = "banco";
$dbname = "jojo";
$user = "root";
$password = "root";
```

O nome do host deve ser o mesmo nome do serviço do banco no Docker.

---

## Exemplos de uso

### Cadastro de usuário

O usuário informa nome, e-mail e senha.

Antes de salvar:

* O nome é formatado
* O e-mail é convertido para minúsculo
* A senha é criptografada com `password_hash()`

### Login

No login, o sistema busca o usuário pelo e-mail e usa `password_verify()` para conferir se a senha digitada corresponde à senha criptografada salva no banco.

### Cadastro de stand

O usuário pode cadastrar:

* Nome do stand
* Usuário do stand
* Parte relacionada
* Descrição
* Imagens
* Habilidades

As imagens são salvas na pasta `uploads`.

---

## Melhorias futuras

* Adicionar mensagens de sucesso e erro mais detalhadas
* Melhorar responsividade em telas pequenas
* Criar dashboard administrativo
* Melhorar a interface gráfica e a lógica do projeto
* Adicionar busca e filtros avançados
* Melhorar organização das funções
* Adicionar funções de conta para o user

---

## Licença

Este projeto foi criado para fins educacionais.
