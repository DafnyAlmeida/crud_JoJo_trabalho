-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 31-Maio-2026 às 02:33
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `jojo_crud`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `partes`
--

CREATE TABLE `partes` (
  `id` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `partes`
--

INSERT INTO `partes` (`id`, `numero`, `nome`, `descricao`, `imagem`, `icon`) VALUES
(1, 1, 'Phantom Blood', 'É a primeira parte de JoJo\'s Bizarre Adventure, serializada na Weekly Shōnen Jump de janeiro a outubro de 1987. O arco acompanha Jonathan Joestar em sua luta contra Dio Brando.', 'assets/img/partes/phantom-blood.jpg', 'assets/img/icones/phantom-blood.png'),
(2, 2, 'Battle Tendency', 'É a segunda parte de JoJo\'s Bizarre Adventure, serializada na Weekly Shōnen Jump de novembro de 1987 a março de 1989. O arco acompanha Joseph Joestar enfrentando os Homens do Pilar.', 'assets/img/partes/battle-tendency.jpg', 'assets/img/icones/battle-tendency.png'),
(3, 3, 'Stardust Crusaders', 'É a terceira parte de JoJo\'s Bizarre Adventure, serializada na Weekly Shōnen Jump de março de 1989 a abril de 1992. O arco acompanha Jotaro Kujo em sua jornada até o Egito para derrotar Dio.', 'assets/img/partes/stardust-crusaders.jpg', 'assets/img/icones/stardust-crusaders.png'),
(4, 4, 'Diamond is Unbreakable', 'É a quarta parte de JoJo\'s Bizarre Adventure, serializada na Weekly Shōnen Jump de maio de 1992 a dezembro de 1995. O arco abrange 174 capítulos e é precedido por Stardust Crusaders, acompanhando o protagonista Josuke Higashikata.', 'assets/img/partes/imagem_parte4_diamond.png', 'assets/img/partes/icon_parte4_diamond.png'),
(5, 5, 'Golden Wind', 'É a quinta parte de JoJo\'s Bizarre Adventure, serializada na Weekly Shōnen Jump de novembro de 1995 a abril de 1999. O arco acompanha Giorno Giovanna em sua ascensão dentro da máfia italiana.', 'assets/img/partes/golden-wind.jpg', 'assets/img/icones/golden-wind.png'),
(6, 6, 'Stone Ocean', 'É a sexta parte de JoJo\'s Bizarre Adventure, serializada na Weekly Shōnen Jump de janeiro de 2000 a abril de 2003. O arco acompanha Jolyne Cujoh em sua luta contra Enrico Pucci.', 'assets/img/partes/stone-ocean.jpg', 'assets/img/icones/stone-ocean.png'),
(7, 7, 'Steel Ball Run', 'É a sétima parte de JoJo\'s Bizarre Adventure, serializada de janeiro de 2004 a abril de 2011. O arco acompanha Johnny Joestar e Gyro Zeppeli na corrida Steel Ball Run.', 'assets/img/partes/steel-ball-run.jpg', 'assets/img/icones/steel-ball-run.png'),
(8, 8, 'JoJolion', 'É a oitava parte de JoJo\'s Bizarre Adventure, serializada de maio de 2011 a agosto de 2021. O arco acompanha Josuke Higashikata em uma Morioh alternativa.', 'assets/img/partes/jojolion.jpg', 'assets/img/icones/jojolion.png'),
(9, 9, 'The JOJOLands', 'É a nona parte de JoJo\'s Bizarre Adventure, iniciada em fevereiro de 2023. O arco acompanha Jodio Joestar em uma aventura ambientada no Havaí.', 'assets/img/partes/the-jojolands.jpg', 'assets/img/icones/the-jojolands.png');

-- --------------------------------------------------------

--
-- Estrutura da tabela `personagens`
--

CREATE TABLE `personagens` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `infor_gerais` text DEFAULT NULL,
  `biografia` text DEFAULT NULL,
  `foto_biografia` varchar(255) DEFAULT NULL,
  `foto_anime` varchar(255) DEFAULT NULL,
  `foto_manga` varchar(255) DEFAULT NULL,
  `foto_catalogo` varchar(255) DEFAULT NULL,
  `descricao_foto_biografia` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `personagens_partes`
--

CREATE TABLE `personagens_partes` (
  `id` int(11) NOT NULL,
  `personagem_id` int(11) NOT NULL,
  `parte_id` int(11) NOT NULL,
  `idade` int(11) DEFAULT NULL,
  `papel` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `referencias`
--

CREATE TABLE `referencias` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `parte_id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `stands`
--

CREATE TABLE `stands` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `personagem_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `foto_manga` varchar(255) DEFAULT NULL,
  `foto_anime` varchar(255) DEFAULT NULL,
  `infor_gerais` text DEFAULT NULL,
  `foto_catalogo` varchar(255) DEFAULT NULL,
  `habilidade_texto_geral` text DEFAULT NULL,
  `tipo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `stand_habilidades`
--

CREATE TABLE `stand_habilidades` (
  `id` int(11) NOT NULL,
  `stand_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `forca` varchar(255) DEFAULT NULL,
  `tipo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`) VALUES
(1, 'Dafny', 'dafny.almeida@aluno.ce.gov.br', '$2y$10$BPfZYef/qqzxFC5a74wExeNKdrm79GEIjxddgEsIqEYUTqij7cFmi'),
(2, 'Senhor.aura', 'miguelangelo5258d3@gmail.com', '$2y$10$7u9mtN2nUmtwelk1XWzlsujRmsLpbIl7x2I9sFUby/9MjK1xcIx7S');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `partes`
--
ALTER TABLE `partes`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `personagens`
--
ALTER TABLE `personagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices para tabela `personagens_partes`
--
ALTER TABLE `personagens_partes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personagem_id` (`personagem_id`),
  ADD KEY `parte_id` (`parte_id`);

--
-- Índices para tabela `referencias`
--
ALTER TABLE `referencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `parte_id` (`parte_id`);

--
-- Índices para tabela `stands`
--
ALTER TABLE `stands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `personagem_id` (`personagem_id`);

--
-- Índices para tabela `stand_habilidades`
--
ALTER TABLE `stand_habilidades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stand_id` (`stand_id`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `partes`
--
ALTER TABLE `partes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `personagens`
--
ALTER TABLE `personagens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `personagens_partes`
--
ALTER TABLE `personagens_partes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `referencias`
--
ALTER TABLE `referencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `stands`
--
ALTER TABLE `stands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `stand_habilidades`
--
ALTER TABLE `stand_habilidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `personagens`
--
ALTER TABLE `personagens`
  ADD CONSTRAINT `personagens_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `personagens_partes`
--
ALTER TABLE `personagens_partes`
  ADD CONSTRAINT `personagens_partes_ibfk_1` FOREIGN KEY (`personagem_id`) REFERENCES `personagens` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `personagens_partes_ibfk_2` FOREIGN KEY (`parte_id`) REFERENCES `partes` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `referencias`
--
ALTER TABLE `referencias`
  ADD CONSTRAINT `referencias_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referencias_ibfk_2` FOREIGN KEY (`parte_id`) REFERENCES `partes` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `stands`
--
ALTER TABLE `stands`
  ADD CONSTRAINT `stands_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stands_ibfk_2` FOREIGN KEY (`personagem_id`) REFERENCES `personagens` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `stand_habilidades`
--
ALTER TABLE `stand_habilidades`
  ADD CONSTRAINT `stand_habilidades_ibfk_1` FOREIGN KEY (`stand_id`) REFERENCES `stands` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
