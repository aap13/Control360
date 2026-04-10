-- Execute apenas se a sua tabela distribuicao_equipamentos ainda nao tiver esses campos
ALTER TABLE distribuicao_equipamentos ADD COLUMN bairro VARCHAR(150) NULL AFTER logradouro;
ALTER TABLE distribuicao_equipamentos ADD COLUMN cep VARCHAR(20) NULL AFTER bairro;
ALTER TABLE distribuicao_equipamentos ADD COLUMN centro_custo VARCHAR(100) NULL AFTER cnpj;
