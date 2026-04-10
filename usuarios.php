-- =========================================================
-- SUPORTE A MODELO DE COBRANÇA
--   1) Sem franquia  -> valor fixo + valor por páginas
--   2) Com franquia  -> valor da franquia + valor excedente
-- =========================================================

-- 1) Cadastro de clientes
ALTER TABLE distribuicao_clientes
    ADD COLUMN modelo_cobranca VARCHAR(30) NOT NULL DEFAULT 'sem_franquia' AFTER cnpj;

-- 2) Financeiro de impressão
ALTER TABLE impressao_financeiro_equipamentos
    ADD COLUMN modelo_cobranca VARCHAR(30) NOT NULL DEFAULT 'sem_franquia' AFTER paginas_excedente,
    ADD COLUMN valor_franquia DECIMAL(14,4) NOT NULL DEFAULT 0 AFTER valor_fixo,
    ADD COLUMN valor_excedente DECIMAL(14,4) NOT NULL DEFAULT 0 AFTER valor_franquia;

-- 3) Ajustar clientes existentes
-- Exemplo: clientes com franquia
UPDATE distribuicao_clientes
SET modelo_cobranca = 'com_franquia'
WHERE nome LIKE '%IFMA%';

-- Exemplo: clientes sem franquia
UPDATE distribuicao_clientes
SET modelo_cobranca = 'sem_franquia'
WHERE nome LIKE '%EQUATORIAL%';

-- 4) Opcional: alinhar registros financeiros já existentes com o cadastro do cliente
UPDATE impressao_financeiro_equipamentos e
INNER JOIN distribuicao_clientes c ON c.id = e.cliente_id
SET e.modelo_cobranca = c.modelo_cobranca;

-- 5) Observação operacional
-- Para clientes com franquia, o sistema calcula:
--   valor_franquia = Val.Franquia/Taxa Fixa * Págs. Franquia
--   valor_excedente = Val.Excedido/Produzido
--   valor_total = Valor Total ($)
--
-- Se o cliente já tem competências importadas antes dessa alteração,
-- o ideal é marcar o cliente como "com_franquia" e reimportar os arquivos.
