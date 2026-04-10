PATCH - IMPORTADOR BASE DA DISTRIBUICAO

1) Copie os arquivos:
- distribuicao_importar_base.php
- includes/distribuicao_importacao.php

2) Adicione um link no menu ou na tela distribuicao_index.php apontando para:
- distribuicao_importar_base.php

3) O importador aceita CSV.
Preencha a planilha modelo e exporte a aba BASE_PARA_PREENCHER para CSV UTF-8.

4) Regras:
- usa cliente + serie como chave de atualização
- se a série não existir, insere novo
- se já existir, atualiza
- linhas sem série são ignoradas

5) Campos considerados:
cliente_nome, codigo_local, pp, uf, municipio, regional, setor_unidade, logradouro, bairro, cep, cnpj, centro_custo, tipo_equipamento, fabricante, modelo, serie, nome_impressora, status_operacional, data_instalacao, observacoes

6) Monitoramento:
não preencha nesta base. O monitoramento deve ser atualizado pelo CSV do NDD em distribuicao_importar_monitoramento.php
