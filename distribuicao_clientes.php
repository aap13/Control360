<?php
function add_error(array &$errors, string $message): void
{
    $errors[] = $message;
}

function validate_required($value, string $label, array &$errors): void
{
    if (trim((string)$value) === '') {
        add_error($errors, $label . ' é obrigatório.');
    }
}

function validate_max_length($value, int $max, string $label, array &$errors): void
{
    if (mb_strlen(trim((string)$value)) > $max) {
        add_error($errors, $label . ' deve ter no máximo ' . $max . ' caracteres.');
    }
}

function validate_in_list($value, array $allowed, string $label, array &$errors): void
{
    if ($value !== '' && !in_array($value, $allowed, true)) {
        add_error($errors, $label . ' inválido.');
    }
}

function validate_date_optional(?string $value, string $label, array &$errors): void
{
    if ($value === null || trim($value) === '') {
        return;
    }
    $dt = DateTime::createFromFormat('Y-m-d', $value);
    if (!$dt || $dt->format('Y-m-d') !== $value) {
        add_error($errors, $label . ' inválida.');
    }
}

function normalize_decimal_br($value): ?string
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }
    $value = str_replace(['R$', ' '], '', $value);
    if (substr_count($value, ',') > 1) {
        return null;
    }
    if (strpos($value, ',') !== false) {
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
    }
    return is_numeric($value) ? number_format((float)$value, 2, '.', '') : null;
}

function digits_only(?string $value): string
{
    return preg_replace('/\D+/', '', (string)$value) ?? '';
}

function validate_cnpj_optional(?string $value, array &$errors): void
{
    $digits = digits_only($value);
    if ($digits === '') {
        return;
    }
    if (strlen($digits) !== 14) {
        add_error($errors, 'CNPJ inválido.');
        return;
    }
    if (preg_match('/^(\d)\1{13}$/', $digits)) {
        add_error($errors, 'CNPJ inválido.');
        return;
    }
    $calc = function($base, $weights) {
        $sum = 0;
        foreach ($weights as $i => $w) {
            $sum += ((int)$base[$i]) * $w;
        }
        $rest = $sum % 11;
        return $rest < 2 ? 0 : 11 - $rest;
    };
    $d1 = $calc($digits, [5,4,3,2,9,8,7,6,5,4,3,2]);
    $d2 = $calc($digits, [6,5,4,3,2,9,8,7,6,5,4,3,2]);
    if ($digits[12] != (string)$d1 || $digits[13] != (string)$d2) {
        add_error($errors, 'CNPJ inválido.');
    }
}

function validate_imei_optional(?string $value, array &$errors): void
{
    $digits = digits_only($value);
    if ($digits === '') {
        return;
    }
    if (!in_array(strlen($digits), [14, 15, 16], true)) {
        add_error($errors, 'IMEI inválido.');
    }
}
