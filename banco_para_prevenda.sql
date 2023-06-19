-- --------------------------------------------------------
-- Servidor:                     bdhost0110.servidorwebfacil.com
-- Versão do servidor:           5.7.32-log - MySQL Community Server (GPL)
-- OS do Servidor:               Linux
-- HeidiSQL Versão:              12.4.0.6659
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para octavioscom_config_pdv
CREATE DATABASE IF NOT EXISTS `octavioscom_config_pdv` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `octavioscom_config_pdv`;

-- Copiando estrutura para tabela octavioscom_config_pdv.config
CREATE TABLE IF NOT EXISTS `config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) DEFAULT NULL,
  `senha` varchar(50) DEFAULT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `id_vendedor_sia` int(11) DEFAULT NULL,
  `cnpj` varchar(50) DEFAULT NULL,
  `chave-acesso` varchar(50) DEFAULT NULL,
  `razao` varchar(50) DEFAULT NULL,
  `id_empresa` int(11) DEFAULT NULL,
  `privilegio` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;

-- Copiando dados para a tabela octavioscom_config_pdv.config: ~2 rows (aproximadamente)
INSERT INTO `config` (`id`, `email`, `senha`, `usuario`, `id_vendedor_sia`, `cnpj`, `chave-acesso`, `razao`, `id_empresa`, `privilegio`) VALUES
	(4, 'desenvolvimento@inoveh.com', 'AxR256396dd', 'OTAVIO', 99999999, '20949563000138', '12345678', '', 0, 'ADMINISTRACAO'),
	(7, 'LOJA', '123', 'LOJA', 8, '20949563000138', '0000000000', '', 1, 'PRODUCAO');

-- Copiando estrutura para tabela octavioscom_config_pdv.entregas
CREATE TABLE IF NOT EXISTS `entregas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL DEFAULT '0',
  `endereco` varchar(200) NOT NULL DEFAULT '0',
  `valor_frete` float NOT NULL DEFAULT '0',
  `entregue` char(1) DEFAULT 'N',
  `vendedor` varchar(50) DEFAULT NULL,
  `numero_pedido` int(11) NOT NULL DEFAULT '0',
  `cnpj` varchar(200) NOT NULL DEFAULT '0',
  `data` date DEFAULT NULL,
  `hora_entrega` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Copiando dados para a tabela octavioscom_config_pdv.entregas: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela octavioscom_config_pdv.registro_criacao
CREATE TABLE IF NOT EXISTS `registro_criacao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fantasia` varchar(50) NOT NULL DEFAULT '',
  `cnpj` varchar(50) DEFAULT NULL,
  `dados_adicionais` varchar(50) DEFAULT NULL,
  `id_tipo_pedido` varchar(50) DEFAULT NULL,
  `id_cliente_padrao` varchar(50) DEFAULT NULL,
  `descricao_tipo_pedido` varchar(50) DEFAULT NULL,
  `descricao_cliente` varchar(50) DEFAULT NULL,
  `telefone` varchar(50) DEFAULT NULL,
  `endereco` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

-- Copiando dados para a tabela octavioscom_config_pdv.registro_criacao: ~1 rows (aproximadamente)
INSERT INTO `registro_criacao` (`id`, `fantasia`, `cnpj`, `dados_adicionais`, `id_tipo_pedido`, `id_cliente_padrao`, `descricao_tipo_pedido`, `descricao_cliente`, `telefone`, `endereco`) VALUES
	(1, 'inoveh', '20949563000138', 'VOLTE SEMPRE', '10', '10', 'VENDA', 'CLIENTE PADRAO', '(35)3421-7775', 'AVENIDA PREFEITO OLAVO GOMES DE OLIVEIRA, 2827');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
