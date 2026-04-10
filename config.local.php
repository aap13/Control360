<?php
function module_labels(): array
{
    return [
        'dashboard'    => 'Dashboard',
        'computadores' => 'Computadores',
        'celulares'    => 'Celulares',
        'faturas'      => 'Faturas',
        'fornecedores' => 'Fornecedores',
        'distribuicao'         => 'Distribuição',
        'impressao_financeiro' => 'Financeiro impressão',
        'usuarios'            => 'Usuários',
    ];
}

function all_module_keys(): array
{
    return array_keys(module_labels());
}
