<?php
declare(strict_types=1);

/**
 * Regras oficiais e fixas do configurador Açaí Flow.
 * Retorna [fruta, creme, adicional, calda, acompanhamento].
 */
function limitesPersonalizacao(string $tipo, string $nome): array
{
    if ($tipo === 'copo') {
        return match ($nome) {
            '180ml' => [1, 1, 1, 1, 1],
            '200ml' => [1, 1, 1, 1, 2],
            '300ml' => [1, 1, 1, 1, 2],
            '400ml' => [1, 1, 1, 1, 3],
            '500ml' => [1, 1, 1, 1, 3],
            '700ml' => [1, 1, 1, 2, 4],
            default => [1, 1, 1, 1, 1],
        };
    }

    return match ($nome) {
        'PP' => [1, 1, 1, 1, 3],
        'P'  => [2, 1, 1, 2, 4],
        'M'  => [2, 1, 1, 2, 4],
        'G'  => [3, 1, 1, 3, 5],
        'GG' => [4, 1, 1, 3, 5],
        'XG' => [5, 1, 1, 4, 6],
        default => [1, 1, 1, 1, 1],
    };
}
