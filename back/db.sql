create database financa;
use financa;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL
);

CREATE TABLE receitas_mensais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_registro DATE NOT NULL,
    hora_registro TIME NOT NULL
);

CREATE TABLE despesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo ENUM('fixa', 'variavel') NOT NULL,
    categoria_nome VARCHAR(100) NULL,
    FOREIGN KEY (categoria_nome) REFERENCES categorias(nome)
);

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NULL UNIQUE
);

CREATE TABLE transacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_transacao ENUM('receita', 'despesa') NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    tipo ENUM('fixa', 'variavel') NOT NULL,
    categoria_nome VARCHAR(100) NULL,
    data_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_nome) REFERENCES categorias(nome)
);

INSERT INTO categorias (nome)
VALUES
('Casa'),
('Carro'),
('Escola'),
('Saúde'),
('Alimentação'),
('Lazer');

ALTER TABLE transacoes MODIFY categoria_nome VARCHAR(100) NULL;
ALTER TABLE transacoes DROP FOREIGN KEY transacoes_ibfk_1;
	

insert into usuarios (nome) values ('felipe');

select * from receitas_mensais;
select * from transacoes;	
select * from despesas;
select * from categorias;

select nome from categorias;


DELETE  FROM receitas_mensais;
drop table categorias;