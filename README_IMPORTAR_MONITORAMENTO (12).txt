Otimizações aplicadas sem alterar função nem estilo visual:

1. config.php
- Removida inicialização duplicada de sessão do fluxo de configuração.
- Adicionado cache de metadados para table_exists() e column_exists() na mesma requisição.
- Adicionado cache de verificação de schema em storage/cache/schema_setup.cache.php.
- Rotinas pesadas de setup/migração agora só rodam quando necessário, e não em toda requisição.

2. includes/distribuicao.php
- Adicionado cache em memória para:
  - distribuicao_all_clients()
  - distribuicao_client_permission_table()
  - distribuicao_company_permission_table()
  - distribuicao_company_options_by_client()
- Reduz repetição de consultas e checagens estruturais.

3. index.php
- Consolidada leitura dos totais de computadores e celulares com SUM(CASE WHEN ...).
- Consolidado resumo financeiro anual/mensal em uma única consulta.
- Mantido o mesmo resultado visual e funcional.

Observação:
- O cache estrutural é seguro para ambiente já implantado.
- Se precisar forçar nova checagem estrutural, basta apagar o arquivo:
  storage/cache/schema_setup.cache.php
