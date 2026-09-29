<?php
/**
 * Catálogo base do "Monte seu Açaí".
 * Mantém Cardápio e Personalizador sincronizados.
 */
function getMonteTamanhos(?PDO $pdo): array
{
    if ($pdo && tableExists($pdo, 'tamanhos')) {
        try {
            $rows = $pdo->query(
                "SELECT id,tipo,nome,preco,volume_ml,
                        limite_frutas,limite_cremes,limite_adicionais,
                        limite_caldas,limite_acompanhamentos
                 FROM tamanhos
                 WHERE ativo=1
                 ORDER BY tipo, ordem, id"
            )->fetchAll();
            if ($rows) {
                return $rows;
            }
        } catch (Throwable $e) {
            // Compatibilidade com banco anterior sem as colunas novas.
            try {
                $rows = $pdo->query(
                    "SELECT id,tipo,nome,preco
                     FROM tamanhos
                     WHERE ativo=1
                     ORDER BY tipo, ordem, id"
                )->fetchAll();
                if ($rows) {
                    $cupLimits = [
                        '180ml' => [1,1,1,1,1],
                        '200ml' => [1,1,1,1,2],
                        '300ml' => [1,1,1,1,2],
                        '400ml' => [1,1,1,1,3],
                        '500ml' => [1,1,1,1,3],
                        '700ml' => [1,1,1,2,4],
                    ];
                    $barLimits = [
                        'PP' => [1,1,1,1,3],
                        'P'  => [2,1,1,2,4],
                        'M'  => [2,1,1,2,4],
                        'G'  => [3,1,1,3,5],
                        'GG' => [4,1,1,3,5],
                        'XG' => [5,1,1,4,6],
                    ];
                    $barVolume = ['PP'=>500,'P'=>700,'M'=>1000,'G'=>1500,'GG'=>2000,'XG'=>3000];
                    foreach ($rows as &$row) {
                        $row['tipo'] = (string)$row['tipo'];
                        $limits = $row['tipo'] === 'copo'
                            ? ($cupLimits[$row['nome']] ?? [1,1,1,1,1])
                            : ($barLimits[$row['nome']] ?? [1,1,1,1,1]);
                        $row['volume_ml'] = $row['tipo'] === 'copo'
                            ? (int)preg_replace('/[^0-9]/', '', (string)$row['nome'])
                            : ($barVolume[$row['nome']] ?? null);
                        [$row['limite_frutas'],$row['limite_cremes'],$row['limite_adicionais'],$row['limite_caldas'],$row['limite_acompanhamentos']] = $limits;
                    }
                    unset($row);
                    return $rows;
                }
            } catch (Throwable $ignored) {
                // Usa os dados abaixo.
            }
        }
    }

    return [
        ['id'=>1,'tipo'=>'copo','nome'=>'180ml','preco'=>5,'volume_ml'=>180,'limite_frutas'=>1,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>1,'limite_acompanhamentos'=>1,'ordem'=>1],
        ['id'=>2,'tipo'=>'copo','nome'=>'200ml','preco'=>7,'volume_ml'=>200,'limite_frutas'=>1,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>1,'limite_acompanhamentos'=>2,'ordem'=>2],
        ['id'=>3,'tipo'=>'copo','nome'=>'300ml','preco'=>10,'volume_ml'=>300,'limite_frutas'=>1,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>1,'limite_acompanhamentos'=>2,'ordem'=>3],
        ['id'=>4,'tipo'=>'copo','nome'=>'400ml','preco'=>13,'volume_ml'=>400,'limite_frutas'=>1,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>1,'limite_acompanhamentos'=>3,'ordem'=>4],
        ['id'=>5,'tipo'=>'copo','nome'=>'500ml','preco'=>16,'volume_ml'=>500,'limite_frutas'=>1,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>1,'limite_acompanhamentos'=>3,'ordem'=>5],
        ['id'=>6,'tipo'=>'copo','nome'=>'700ml','preco'=>20,'volume_ml'=>700,'limite_frutas'=>1,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>2,'limite_acompanhamentos'=>4,'ordem'=>6],
        ['id'=>7,'tipo'=>'barca','nome'=>'PP','preco'=>15,'volume_ml'=>500,'limite_frutas'=>1,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>1,'limite_acompanhamentos'=>3,'ordem'=>1],
        ['id'=>8,'tipo'=>'barca','nome'=>'P','preco'=>22,'volume_ml'=>700,'limite_frutas'=>2,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>2,'limite_acompanhamentos'=>4,'ordem'=>2],
        ['id'=>9,'tipo'=>'barca','nome'=>'M','preco'=>30,'volume_ml'=>1000,'limite_frutas'=>2,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>2,'limite_acompanhamentos'=>4,'ordem'=>3],
        ['id'=>10,'tipo'=>'barca','nome'=>'G','preco'=>40,'volume_ml'=>1500,'limite_frutas'=>3,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>3,'limite_acompanhamentos'=>5,'ordem'=>4],
        ['id'=>11,'tipo'=>'barca','nome'=>'GG','preco'=>50,'volume_ml'=>2000,'limite_frutas'=>4,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>3,'limite_acompanhamentos'=>5,'ordem'=>5],
        ['id'=>12,'tipo'=>'barca','nome'=>'XG','preco'=>70,'volume_ml'=>3000,'limite_frutas'=>5,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>4,'limite_acompanhamentos'=>6,'ordem'=>6],
    ];
}

/**
 * Retorna combos e promoções disponíveis agora no catálogo.
 * Usa a própria tabela de produtos para evitar catálogo duplicado.
 */
function getCatalogoOfertas(?PDO $pdo): array
{
    if (!$pdo || !tableExists($pdo, 'produtos') || !tableExists($pdo, 'categorias')) {
        return [];
    }

    $hasOfferSchedule = colunaExiste($pdo, 'produtos', 'oferta_inicio') && colunaExiste($pdo, 'produtos', 'oferta_fim');

    try {
        $sql = $hasOfferSchedule
            ? "SELECT p.id,p.nome,p.descricao,p.preco,p.imagem,p.promocao,p.oferta_inicio,p.oferta_fim,c.nome AS categoria
               FROM produtos p
               INNER JOIN categorias c ON c.id=p.categoria_id
               WHERE p.ativo=1
                 AND (c.nome='Combos' OR p.promocao=1)
                 AND (p.oferta_inicio IS NULL OR p.oferta_inicio <= NOW())
                 AND (p.oferta_fim IS NULL OR p.oferta_fim >= NOW())
               ORDER BY CASE WHEN c.nome='Combos' THEN 0 ELSE 1 END, p.id DESC"
            : "SELECT p.id,p.nome,p.descricao,p.preco,p.imagem,p.promocao,c.nome AS categoria
               FROM produtos p
               INNER JOIN categorias c ON c.id=p.categoria_id
               WHERE p.ativo=1 AND (c.nome='Combos' OR p.promocao=1)
               ORDER BY CASE WHEN c.nome='Combos' THEN 0 ELSE 1 END, p.id DESC";

        return $pdo->query($sql)->fetchAll() ?: [];
    } catch (Throwable $e) {
        return [];
    }
}



/**
 * Configuração de montagem de um combo.
 * A configuração vive separada do produto para manter o catálogo normal
 * intacto e permitir que o administrador altere apenas as regras do combo.
 */
function getComboConfiguracao(?PDO $pdo, int $produtoId, ?array $produto = null): ?array
{
    if (!$pdo || $produtoId <= 0) return null;

    if (tableExists($pdo, 'combo_configuracoes')) {
        try {
            $st = $pdo->prepare('SELECT configuracao_json FROM combo_configuracoes WHERE produto_id=? AND ativo=1 LIMIT 1');
            $st->execute([$produtoId]);
            $json = $st->fetchColumn();
            if ($json) {
                $cfg = json_decode((string)$json, true);
                if (is_array($cfg) && !empty($cfg['porcoes']) && is_array($cfg['porcoes'])) {
                    return normalizarComboConfiguracao($cfg);
                }
            }
        } catch (Throwable $e) {
            // Continua para o fallback abaixo.
        }
    }

    // Fallback compatível com os combos entregues na instalação inicial.
    $nome = mb_strtolower((string)($produto['nome'] ?? ''));
    $todos = range(1, 12);
    if (str_contains($nome, 'combo casal')) {
        return normalizarComboConfiguracao([
            'porcoes' => [
                [
                    'nome' => 'Porção 1',
                    'tipos' => ['copo','barca'],
                    'tamanhos' => $todos,
                    'limites' => ['fruta'=>2,'creme'=>1,'adicional'=>1,'calda'=>2,'acompanhamento'=>5]
                ],
                [
                    'nome' => 'Porção 2',
                    'tipos' => ['copo','barca'],
                    'tamanhos' => $todos,
                    'limites' => ['fruta'=>2,'creme'=>1,'adicional'=>1,'calda'=>2,'acompanhamento'=>5]
                ]
            ]
        ]);
    }
    if (str_contains($nome, 'combo flow')) {
        return normalizarComboConfiguracao([
            'porcoes' => [[
                'nome' => 'Açaí Flow 500ml',
                'tipos' => ['copo'],
                'tamanhos' => [5],
                'limites' => ['fruta'=>1,'creme'=>1,'adicional'=>1,'calda'=>1,'acompanhamento'=>6]
            ]]
        ]);
    }

    return null;
}

function normalizarComboConfiguracao(array $config): array
{
    $groups = ['fruta','creme','adicional','calda','acompanhamento'];
    $porcoes = [];
    foreach (array_slice($config['porcoes'] ?? [], 0, 3) as $i => $slot) {
        if (!is_array($slot)) continue;
        $tipos = array_values(array_intersect(['copo','barca'], array_map('strval', (array)($slot['tipos'] ?? []))));
        $tamanhos = array_values(array_unique(array_filter(array_map('intval', (array)($slot['tamanhos'] ?? [])), fn($v) => $v > 0)));
        $limites = [];
        foreach ($groups as $group) {
            $max = $group === 'acompanhamento' ? 20 : 10;
            $limites[$group] = max(0, min($max, (int)($slot['limites'][$group] ?? 1)));
        }
        if (!$tipos || !$tamanhos) continue;
        $porcoes[] = [
            'nome' => trim((string)($slot['nome'] ?? ('Porção '.($i+1)))) ?: ('Porção '.($i+1)),
            'tipos' => $tipos,
            'tamanhos' => $tamanhos,
            'limites' => $limites
        ];
    }
    return ['porcoes' => $porcoes];
}

function salvarComboConfiguracao(PDO $pdo, int $produtoId, ?array $config): void
{
    if (!tableExists($pdo, 'combo_configuracoes')) {
        if ($config !== null) throw new RuntimeException('A estrutura do banco precisa da migração banco/migracao_combos_personalizaveis.sql.');
        return;
    }

    if ($config === null) {
        $pdo->prepare('DELETE FROM combo_configuracoes WHERE produto_id=?')->execute([$produtoId]);
        return;
    }

    $config = normalizarComboConfiguracao($config);
    if (empty($config['porcoes'])) throw new InvalidArgumentException('Configure ao menos uma porção para o combo.');

    $json = json_encode($config, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    $st = $pdo->prepare('SELECT COUNT(*) FROM combo_configuracoes WHERE produto_id=?');
    $st->execute([$produtoId]);
    $exists = (int)$st->fetchColumn() > 0;
    if ($exists) {
        $pdo->prepare('UPDATE combo_configuracoes SET configuracao_json=?, ativo=1 WHERE produto_id=?')->execute([$json, $produtoId]);
    } else {
        $pdo->prepare('INSERT INTO combo_configuracoes(produto_id,configuracao_json,ativo) VALUES(?,?,1)')->execute([$produtoId,$json]);
    }
}

function comboConfiguracaoPost(array $post): ?array
{
    if (empty($post['combo_config_ativo'])) return null;
    $slotsRaw = $post['combo_slot'] ?? [];
    if (!is_array($slotsRaw)) return null;
    $slotCount = max(1, min(3, (int)($post['combo_slots_count'] ?? 1)));

    $porcoes = [];
    foreach (array_slice($slotsRaw, 0, $slotCount) as $i => $raw) {
        if (!is_array($raw)) continue;
        $tipos = array_values(array_intersect(['copo','barca'], array_map('strval', (array)($raw['tipos'] ?? []))));
        $tamanhos = array_values(array_unique(array_filter(array_map('intval', (array)($raw['tamanhos'] ?? [])), fn($v) => $v > 0)));
        if (!$tipos || !$tamanhos) continue;
        $porcoes[] = [
            'nome' => trim((string)($raw['nome'] ?? ('Porção '.((int)$i+1)))) ?: ('Porção '.((int)$i+1)),
            'tipos' => $tipos,
            'tamanhos' => $tamanhos,
            'limites' => [
                'fruta' => max(0, min(10, (int)($raw['limites']['fruta'] ?? 1))),
                'creme' => max(0, min(10, (int)($raw['limites']['creme'] ?? 1))),
                'adicional' => max(0, min(10, (int)($raw['limites']['adicional'] ?? 1))),
                'calda' => max(0, min(10, (int)($raw['limites']['calda'] ?? 1))),
                'acompanhamento' => max(0, min(20, (int)($raw['limites']['acompanhamento'] ?? 1))),
            ]
        ];
    }
    return ['porcoes' => $porcoes];
}
