<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Factura;
use App\Models\FacturaItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BillingService
{
    public static function generateForEntradaSaida($movimento, string $evento): ?Factura
    {
        $terminal = $movimento->terminal ?? null;
        $embarcacao = $movimento->embarcacao ?? null;
        $concessionariaId = ($terminal && $terminal->concessionaria_id) ? $terminal->concessionaria_id
            : (($embarcacao && $embarcacao->concessionaria_id) ? $embarcacao->concessionaria_id
            : ($movimento->concessionaria_id ?? null));
        if (!$concessionariaId) { Log::warning('Billing: sem concessionaria_id para movimentacao', ['movimento_id'=>$movimento->id,'terminal_id'=>$terminal->id ?? null,'embarcacao_id'=>$embarcacao->id ?? null]); return null; }

        // Seleção de contrato por contexto NAVIO: primeiro título exato, depois like; sem fallback genérico
        $contrato = Contrato::where('concessionaria_id', $concessionariaId)
            ->whereIn('status', ['ativo','active','Ativo','ATIVO'])
            ->where(function($q){ $q->whereNull('data_inicio')->orWhere('data_inicio','<=', now()); })
            ->where(function($q){ $q->whereNull('data_fim')->orWhere('data_fim','>=', now()); })
            ->where('titulo', 'Tarifas aplicadas ao NAVIO')
            ->orderByDesc('created_at')
            ->first();

        if (!$contrato) {
            $contrato = Contrato::where('concessionaria_id', $concessionariaId)
                ->whereIn('status', ['ativo','active','Ativo','ATIVO'])
                ->where(function($q){ $q->whereNull('data_inicio')->orWhere('data_inicio','<=', now()); })
                ->where(function($q){ $q->whereNull('data_fim')->orWhere('data_fim','>=', now()); })
                ->where('titulo','like','%NAVIO%')
                ->orderByDesc('created_at')
                ->first();
        }

    

        if (!$contrato) { Log::warning('Billing: nenhum contrato NAVIO encontrado para concessionaria', ['concessionaria_id'=>$concessionariaId]); return null; }

        if (mb_strtolower($evento) === 'estadia') {
            if ($contrato->modo_faturacao === 'consolidado_por_navio') {
                return self::appendEstadiaDailyItems($contrato, $concessionariaId, $movimento);
            }
            return self::appendEstadiaDailyItems($contrato, $concessionariaId, $movimento);
        }

        $tarifas = self::resolveTarifas($contrato, $evento);
        
        // FILTRO ESTRITO: Garante que so processa tarifas do evento solicitado
        // Se o evento for 'Entrada', remove qualquer tarifa que nao seja explicitamente de Entrada
        if (mb_strtolower($evento) === 'entrada') {
            $tarifas = $tarifas->filter(function($t) {
                $evt = mb_strtolower(trim($t->evento_disparo ?? ''));
                return in_array($evt, ['entrada', 'taxa de entrada', 'entrada de navio']);
            });
        }
        
        if (in_array(mb_strtolower($evento), ['atracação', 'atracacao'])) {
            $tarifas = $tarifas->filter(function($t) {
                $evt = mb_strtolower(trim($t->evento_disparo ?? ''));
                // Exclui explicitamente desatracacao para nao pegar "carona" no like
                return strpos($evt, 'desatrac') === false;
            });
        }

        $atracacaoVar = self::filterAtracacaoVariableTarifas($tarifas);
        $otherTarifas = $tarifas->reject(function($t){ return self::isAtracacaoVariable($t); });
        Log::info('Billing: entrada tarifas', ['atracacaoVar'=>$atracacaoVar->count(),'other'=>$otherTarifas->count(),'evento'=>$evento,'contrato_id'=>$contrato->id]);
        if ($tarifas->isEmpty()) { Log::info('Billing: sem tarifas ativas para evento', ['evento'=>$evento,'contrato_id'=>$contrato->id]); return null; }

        if (in_array(mb_strtolower($evento), ['entrada','atracação','atracacao']) && $atracacaoVar->isNotEmpty()) {
            self::appendAtracacaoVariableItems($contrato, $concessionariaId, $movimento, $atracacaoVar);
        }

        if ($contrato->modo_faturacao === 'consolidado_por_navio') {
            $ft = self::appendToConsolidated($contrato, $concessionariaId, $otherTarifas, $movimento->id, null, ['evento' => $evento]);
            if ($ft) Log::info('Billing: agregado em fatura consolidada', ['factura_id'=>$ft->id,'numero'=>$ft->numero]);
            return $ft;
        }

        $ft = self::createInvoiceFromTarifas($contrato, $concessionariaId, $otherTarifas, [
            'entrada_saida_id' => $movimento->id,
            'metadados' => ['evento' => $evento],
        ], 1.0);
        if ($ft) Log::info('Billing: fatura criada', ['factura_id'=>$ft->id,'numero'=>$ft->numero]);
        return $ft;
    }

    public static function generateForMovimentoCarga($mc): ?Factura
    {
        $mov = $mc->entradaSaida ?? null;
        $terminal = $mov ? $mov->terminal : null;
        $concessionariaId = $terminal->concessionaria_id ?? null;
        if (!$concessionariaId) return null;

        // Seleção de contrato por contexto CARGA: primeiro título exato, depois like; sem fallback genérico
        $contrato = Contrato::where('concessionaria_id', $concessionariaId)
            ->whereIn('status', ['ativo','active','Ativo','ATIVO'])
            ->where(function($q){ $q->whereNull('data_inicio')->orWhere('data_inicio','<=', now()); })
            ->where(function($q){ $q->whereNull('data_fim')->orWhere('data_fim','>=', now()); })
            ->where('titulo', 'Tarifas aplicadas à CARGA')
            ->orderByDesc('created_at')
            ->first();
        if (!$contrato) {
            Log::warning('Billing: nenhum contrato ativo CARGA para concessionaria, tentando título similar', ['concessionaria_id'=>$concessionariaId]);
            $contrato = Contrato::where('concessionaria_id', $concessionariaId)
                ->whereIn('status', ['ativo','active','Ativo','ATIVO'])
                ->where(function($q){ $q->whereNull('data_inicio')->orWhere('data_inicio','<=', now()); })
                ->where(function($q){ $q->whereNull('data_fim')->orWhere('data_fim','>=', now()); })
                ->where('titulo','like','%CARGA%')
                ->orderByDesc('created_at')
                ->first();
            if (!$contrato) return null;
        }

        $tarifas = self::resolveTarifas($contrato, 'movimentacao');
        
        // Filtro estrito de unidade
        if (!empty($mc->unidade)) {
            $tarifas = $tarifas->filter(function($t) use ($mc) {
                $tUnit = mb_strtolower(trim($t->unidade ?? ''));
                $mcUnit = mb_strtolower(trim($mc->unidade));
                
                if (empty($tUnit) || $tUnit === 'naoaplicavel') return true;
                return $tUnit === $mcUnit;
            });
        }

        // Filtro estrito de Tipo de Operação (Carga vs Descarga)
        if (!empty($mc->tipo_operacao)) {
            $op = mb_strtolower(trim($mc->tipo_operacao));
            $tarifas = $tarifas->filter(function($t) use ($op) {
                $evt = mb_strtolower(trim($t->evento_disparo ?? ''));
                
                // Se for movimentação genérica ou transbordo/outros, mantem (ou filtra se necessario)
                // Aqui focamos na distincao critica Carga vs Descarga
                
                if ($op === 'carga') {
                    // Se operação é Carga, rejeita Descarga
                    if (strpos($evt, 'descarga') !== false) return false;
                }
                
                if ($op === 'descarga') {
                    // Se operação é Descarga, rejeita Carga (cuidado: descarga contem carga)
                    // Rejeita se tiver 'carga' mas NAO 'descarga'
                    if (strpos($evt, 'carga') !== false && strpos($evt, 'descarga') === false) return false;
                }
                
                return true;
            });
        }

        if ($tarifas->isEmpty()) return null;

        if ($contrato->modo_faturacao === 'consolidado_por_navio') {
            return self::appendToConsolidated($contrato, $concessionariaId, $tarifas, $mc->entrada_saida_id, $mc->id, [
                'evento' => 'movimentacao',
                'quantidade' => (float)$mc->quantidade,
                'tipo_produto' => $mc->tipo_produto
            ], (float)$mc->quantidade);
        }

        return self::createInvoiceFromTarifas($contrato, $concessionariaId, $tarifas, [
            'entrada_saida_id' => $mc->entrada_saida_id,
            'movimento_carga_id' => $mc->id,
            'metadados' => [
                'evento' => 'movimentacao',
                'quantidade' => (float)$mc->quantidade,
                'tipo_produto' => $mc->tipo_produto,
                'tipo_operacao' => $mc->tipo_operacao,
                'unidade' => $mc->unidade
            ],
        ], (float)$mc->quantidade);
    }

    protected static function resolveTarifas(Contrato $contrato, string $evento)
    {
        $eventKeys = self::mapEventoKeys($evento);
        Log::info('Billing: resolveTarifas keys', ['evento'=>$evento, 'keys'=>$eventKeys]);
        
        $q = $contrato->tarifas()
            ->where(function($w){ $w->where('is_active',true)->orWhereNull('is_active'); });
        
        $q->where(function($w) use ($eventKeys){
            foreach ($eventKeys as $index => $ek) {
                if ($index === 0) {
                    $w->where(DB::raw('LOWER(TRIM(evento_disparo))'), mb_strtolower($ek))
                      ->orWhere('evento_disparo','like','%'.$ek.'%');
                } else {
                    $w->orWhere(DB::raw('LOWER(TRIM(evento_disparo))'), mb_strtolower($ek))
                      ->orWhere('evento_disparo','like','%'.$ek.'%');
                }
            }
        });
        
        $tarifas = $q->get();
        Log::info('Billing: resolveTarifas result', ['count'=>$tarifas->count(), 'tarifas'=>$tarifas->pluck('descricao')]);
        
        if ($tarifas->isEmpty() && isset($contrato->titulo) && stripos($contrato->titulo,'NAVIO') !== false) {
            $tarifas = $contrato->tarifas()
                ->where(function($w){ $w->where('is_active',true)->orWhereNull('is_active'); })
                ->whereNull('evento_disparo')->get();
            Log::info('Billing: resolveTarifas fallback NAVIO', ['count'=>$tarifas->count()]);
        }
        return $tarifas;
    }

    protected static function mapEventoKeys(string $evento): array
    {
        $e = mb_strtolower($evento);
        switch ($e) {
            case 'entrada':
                return [
                    'entrada','Entrada','ENTRADA',
                    'entrada de navio','Entrada de navio','Taxa de entrada','taxa de entrada',
                    'Atracação','Atracacao','ATRACAÇÃO','ATRACACAO',
                    'Escala','ESCALA',
                    'Acostagem','acostagem'
                ];
            case 'saida':
                return [
                    'saida','Saída','Saida','SAÍDA','SAIDA',
                    'saida de navio','Saída de navio','Taxa de saída','taxa de saída',
                    'Desatracação','Desatracacao','DESATRACAÇÃO','DESATRACACAO',
                    'Escala','ESCALA',
                    'Partida','partida'
                ];
            case 'movimentacao':
                return ['movimentacao','Movimentação interna','Movimentacao interna','MOVIMENTAÇÃO INTERNA','MOVIMENTACAO INTERNA','Carga','CARGA','Descarga','DESCARGA','Transbordo','TRANSBORDO','Pesagem','PESAGEM','Inspeção','INSPEÇÃO','Inspecao','INSPECAO'];
            case 'operacao':
                return ['Uso de guindaste','Guindaste','USO DE GUINDASTE','GUINDASTE','Hora máquina','HORA MÁQUINA','Hora maquina'];
            case 'estadia':
                return ['Estadia','ESTADIA'];
            case 'atracação':
                return ['Atracação','Atracacao','ATRACAÇÃO','ATRACACAO','Taxa de atracação','Taxa de atracacao'];
            case 'desatracação':
                return ['Desatracação','Desatracacao','DESATRACAÇÃO','DESATRACACAO','Taxa de desatracação','Taxa de desatracacao'];
            default:
                return [$evento];
        }
    }

    public static function appendGuindasteUsage($mc): ?Factura
    {
        // 1. Validações iniciais
        if (!$mc->guindaste_id) return null;
        
        $mov = $mc->entradaSaida ?? null;
        $terminal = $mov ? $mov->terminal : null;
        $concessionariaId = $terminal->concessionaria_id ?? null;
        if (!$concessionariaId) return null;

        // 2. Busca Contrato de OPERAÇÃO
        $contrato = Contrato::where('concessionaria_id', $concessionariaId)
            ->whereIn('status', ['ativo','active','Ativo','ATIVO'])
            ->where(function($q){ $q->whereNull('data_inicio')->orWhere('data_inicio','<=', now()); })
            ->where(function($q){ $q->whereNull('data_fim')->orWhere('data_fim','>=', now()); })
            ->where('titulo', 'Tarifas aplicadas à OPERAÇÃO')
            ->orderByDesc('created_at')
            ->first();

        if (!$contrato) {
            $contrato = Contrato::where('concessionaria_id', $concessionariaId)
                ->whereIn('status', ['ativo','active','Ativo','ATIVO'])
                ->where(function($q){ $q->whereNull('data_inicio')->orWhere('data_inicio','<=', now()); })
                ->where(function($q){ $q->whereNull('data_fim')->orWhere('data_fim','>=', now()); })
                ->where('titulo','like','%OPERAÇÃO%')
                ->orderByDesc('created_at')
                ->first();
            if (!$contrato) return null;
        }

        // 3. Resolve e Filtra Tarifas de Guindaste
        // Busca tarifas de 'operacao' (geral) ou 'guindaste' (específico)
        $tarifas = self::resolveTarifas($contrato, 'operacao');
        
        // Se não encontrou, tenta buscar com evento 'guindaste' explicitamente
        if ($tarifas->isEmpty()) {
             $tarifas = self::resolveTarifas($contrato, 'guindaste');
        }

        // Filtra para garantir que são relevantes, mas relaxa a busca por string
        $tarifas = $tarifas->filter(function($t){
            $e = mb_strtolower($t->evento_disparo ?? '');
            $d = mb_strtolower($t->descricao ?? '');
            // Aceita guindaste, equipamento, uso, operação, ou se vier de um evento explícito de guindaste
            return strpos($e,'guindaste') !== false || strpos($d,'guindaste') !== false ||
                   strpos($e,'equipamento') !== false || strpos($d,'equipamento') !== false ||
                   strpos($e,'operacao') !== false || strpos($d,'operacao') !== false ||
                   strpos($e,'operação') !== false || strpos($d,'operação') !== false;
        });

        if ($tarifas->isEmpty()) return null;

        // 4. Prepara dados de Tempo (para caso seja necessário)
        $inicio = $mc->inicio ? Carbon::parse($mc->inicio) : null;
        $fim = $mc->fim ? Carbon::parse($mc->fim) : null;
        $hours = 0;
        if ($inicio && $fim) {
            $minutes = $inicio->diffInMinutes($fim);
            if ($minutes > 0) $hours = round($minutes / 60, 2);
        }

        // 5. Processamento Transacional
        return DB::transaction(function() use ($contrato,$concessionariaId,$mc,$tarifas,$hours,$inicio,$fim) {
            // Busca ou cria fatura específica para Operação de Guindaste
            // Mudado para 'operacao_guindaste' para não misturar com movimentação de carga pura
            $factura = Factura::where('contrato_id',$contrato->id)
                ->where('entrada_saida_id',$mc->entrada_saida_id)
                ->where('metadados->evento','operacao_guindaste')
                ->whereIn('status',['emitida','EMITIDA','aberta','ABERTA','open','OPEN'])
                ->first();

            if (!$factura) {
                $factura = Factura::create([
                    'concessionaria_id' => $concessionariaId,
                    'contrato_id' => $contrato->id,
                    'entrada_saida_id' => $mc->entrada_saida_id,
                    'movimento_carga_id' => $mc->id,
                    'numero' => self::nextNumber(),
                    'valor_total' => 0,
                    'status' => 'emitida',
                    'metadados' => ['evento' => 'operacao_guindaste'],
                ]);
            }

            $total = (float)$factura->valor_total;
            $itemsAdicionados = 0;

            foreach ($tarifas as $tarifa) {
                $uTarifa = mb_strtolower(trim($tarifa->unidade ?? ''));
                $uMovimento = mb_strtolower(trim($mc->unidade ?? ''));
                
                $qtdAplicada = 0;
                $detalhe = '';
                $isTimeBased = false;

                // Lógica de Matching Híbrida
                
                // Caso 1: Match exato de unidade (ex: Contêiner 20, Tonelada) -> Usa Quantidade do Movimento
                if ($uTarifa === $uMovimento && !empty($uMovimento)) {
                    $qtdAplicada = (float)$mc->quantidade;
                    $detalhe = " ($qtdAplicada $mc->unidade)";
                } 
                // Caso 2: Tarifa é por Hora -> Usa Tempo calculado
                elseif (in_array($uTarifa, ['hora', 'h', 'hour'])) {
                    if ($hours > 0) {
                        $qtdAplicada = max(1.0, $hours); // Mínimo 1 hora
                        $detalhe = " ($qtdAplicada horas)";
                        $isTimeBased = true;
                    }
                } 
                // Caso 3: Tarifa sem unidade ou 'unidade'/'taxa' genérica -> Taxa fixa (1x)
                // Assumimos que é uma taxa de uso do equipamento por operação
                elseif (empty($uTarifa) || in_array($uTarifa, ['unidade', 'taxa', 'uso', 'fixo'])) {
                    $qtdAplicada = 1;
                    $detalhe = " (Taxa Fixa)";
                }

                if ($qtdAplicada <= 0) continue;

                $unitPrice = (float)($tarifa->valor_unitario ?? $tarifa->valor_fixo ?? 0);
                $subtotal = round($qtdAplicada * $unitPrice, 4);
                
                // Monta descrição
                $baseDesc = trim($tarifa->descricao ?? 'Uso de guindaste');
                if ($isTimeBased && $inicio && $fim) {
                     $desc = "$baseDesc - " . $inicio->format('d/m H:i') . ' a ' . $fim->format('H:i') . $detalhe;
                } else {
                     $desc = "$baseDesc$detalhe";
                }

                // Evita duplicidade exata
                $existingItem = FacturaItem::where('factura_id', $factura->id)
                    ->where('descricao', $desc)
                    ->first();

                if ($existingItem) {
                    // Atualiza apenas se houver diferença significativa (correção)
                    if (abs($existingItem->subtotal - $subtotal) > 0.01) {
                        $total -= $existingItem->subtotal;
                        $existingItem->update([
                            'quantidade' => $qtdAplicada,
                            'preco_unitario' => $unitPrice,
                            'subtotal' => $subtotal
                        ]);
                        $total += $subtotal;
                    }
                } else {
                    FacturaItem::create([
                        'factura_id' => $factura->id,
                        'descricao' => $desc,
                        'quantidade' => $qtdAplicada,
                        'preco_unitario' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);
                    $total += $subtotal;
                    $itemsAdicionados++;
                }
            }
            
            if ($itemsAdicionados > 0) {
                $factura->update(['valor_total' => $total]);
            }
            
            return $factura;
        });
    }

    protected static function appendToConsolidated(Contrato $contrato, string $concessionariaId, $tarifas, string $entradaSaidaId, ?string $movimentoCargaId, array $metadados, ?float $quantidade = null): ?Factura
    {
        return DB::transaction(function() use ($contrato,$concessionariaId,$tarifas,$entradaSaidaId,$movimentoCargaId,$metadados,$quantidade) {
            $factura = Factura::where('contrato_id',$contrato->id)
                ->where('entrada_saida_id',$entradaSaidaId)
                ->whereIn('status',['emitida','EMITIDA','aberta','ABERTA','open','OPEN'])
                ->first();

            if (!$factura) {
                $factura = Factura::create([
                    'concessionaria_id' => $concessionariaId,
                    'contrato_id' => $contrato->id,
                    'entrada_saida_id' => $entradaSaidaId,
                    'movimento_carga_id' => $movimentoCargaId,
                    'numero' => self::nextNumber(),
                    'valor_total' => 0,
                    'status' => 'emitida',
                    'metadados' => $metadados,
                ]);
            }

            $total = (float)$factura->valor_total;
            foreach ($tarifas as $tarifa) {
                $eventoCtx = mb_strtolower($metadados['evento'] ?? '');
                if ($eventoCtx === 'movimentacao' && isset($metadados['unidade']) && !empty($tarifa->unidade)) {
                    if (mb_strtolower($tarifa->unidade) !== mb_strtolower($metadados['unidade'])) {
                        continue;
                    }
                }

                $qty = 1.0;
                $unitPrice = 0.0;

                if ($eventoCtx === 'movimentacao') {
                    $qty = (float)($quantidade ?? 1.0);
                    $unitPrice = (float)($tarifa->valor_unitario ?? $tarifa->valor_fixo ?? 0);
                } else {
                    $isFixed = mb_strtolower($tarifa->tipo ?? '') === 'fixa';
                    $fixo = (float)($tarifa->valor_fixo ?? 0);
                    $unit = (float)($tarifa->valor_unitario ?? 0);
                    $unitPrice = $isFixed ? ($fixo > 0 ? $fixo : $unit) : $unit;
                    
                    Log::info('Billing: tarifa details', [
                        'desc' => $tarifa->descricao,
                        'tipo' => $tarifa->tipo,
                        'fixo_db' => $tarifa->valor_fixo,
                        'unit_db' => $tarifa->valor_unitario,
                        'isFixed' => $isFixed,
                        'calc_unit_price' => $unitPrice
                    ]);
                }

                $subtotal = round($qty * $unitPrice, 4);
                    
                Log::info('Billing: item processado', [
                    'descricao' => $tarifa->descricao,
                    'tipo' => $tarifa->tipo,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal
                ]);

                FacturaItem::create([
                    'factura_id' => $factura->id,
                    'descricao' => $tarifa->descricao,
                    'quantidade' => $qty,
                    'preco_unitario' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $factura->update(['valor_total' => $total]);

            return $factura;
        });
    }

    protected static function appendEstadiaDailyItems(Contrato $contrato, string $concessionariaId, $movimento): ?Factura
    {
        $tarifas = self::resolveTarifas($contrato, 'estadia');
        Log::info('Billing: tarifas estadia encontradas', ['count'=>$tarifas->count(), 'movimento'=>$movimento->id]);
        
        if ($tarifas->isEmpty()) return null;
        
        return DB::transaction(function() use ($contrato,$concessionariaId,$tarifas,$movimento) {
            $factura = Factura::where('contrato_id',$contrato->id)
                ->where('entrada_saida_id',$movimento->id)
                ->whereIn('status',['emitida','EMITIDA','aberta','ABERTA','open','OPEN'])
                ->first(); 
            
            if (!$factura) {
                 Log::info('Billing: criando nova fatura para estadia', ['movimento'=>$movimento->id]);
                $factura = Factura::create([
                    'concessionaria_id' => $concessionariaId,
                    'contrato_id' => $contrato->id,
                    'entrada_saida_id' => $movimento->id,
                    'numero' => self::nextNumber(),
                    'valor_total' => 0,
                    'status' => 'emitida',
                    'metadados' => ['evento' => 'estadia'],
                ]);
            } else {
                 Log::info('Billing: usando fatura existente para estadia', ['factura_id'=>$factura->id]);
            }

            // Prioriza ATA (Chegada Real). Se nao houver, usa Data Programada (ETA) como melhor estimativa que Data Efetiva (que pode ser o momento do clique)
            $startRef = $movimento->ata ?? $movimento->data_programada ?? $movimento->data_efetiva ?? now();
            $inicio = $startRef->copy()->startOfDay();

            $endRef = $movimento->atd ?? now();
            $fim = $endRef->copy()->startOfDay();
            
            // Log detalhado para depuracao
            Log::info('Billing: calculando estadia debug', [
                'ata_db' => $movimento->ata,
                'eta_db' => $movimento->data_programada,
                'efetiva_db' => $movimento->data_efetiva,
                'atd_db' => $movimento->atd,
                'start_used' => $inicio->toDateTimeString(),
                'end_used' => $fim->toDateTimeString()
            ]);
            
            // Se as datas forem iguais, cobra pelo menos 1 dia
            if ($inicio->gt($fim)) { $fim = $inicio->copy(); }
            
            Log::info('Billing: calculando estadia (ATA/ATD)', ['ata'=>$movimento->ata, 'atd'=>$movimento->atd, 'inicio'=>$inicio->toDateString(), 'fim'=>$fim->toDateString()]);

            // Calculo de dias (inclusivo)
            $days = $inicio->diffInDays($fim) + 1;
            
            Log::info('Billing: calculando estadia (agrupado)', ['inicio'=>$inicio->toDateString(), 'fim'=>$fim->toDateString(), 'dias'=>$days]);

            $total = (float)$factura->valor_total;
            
            foreach ($tarifas as $tarifa) {
                // Remove itens anteriores dessa tarifa para evitar duplicatas (ex: mudanca de logica diaria para agrupada)
                $itemsToDelete = FacturaItem::where('factura_id', $factura->id)
                    ->where('descricao', 'like', trim($tarifa->descricao) . '%')
                    ->get();
                    
                foreach($itemsToDelete as $it) {
                    $total -= $it->subtotal;
                    $it->delete();
                }

                $desc = trim(($tarifa->descricao ?? 'Estadia')) . ' (' . $days . ' dias: ' . $inicio->format('d/m/Y') . ' a ' . $fim->format('d/m/Y') . ')';
                
                $isFixed = mb_strtolower($tarifa->tipo ?? '') === 'fixa';
                $fixo = (float)($tarifa->valor_fixo ?? 0);
                $unit = (float)($tarifa->valor_unitario ?? 0);
                $unitPrice = $isFixed ? ($fixo > 0 ? $fixo : $unit) : $unit;
                
                $subtotal = round($days * $unitPrice, 4);

                FacturaItem::create([
                    'factura_id' => $factura->id,
                    'descricao' => $desc,
                    'quantidade' => (float)$days,
                    'preco_unitario' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
                Log::info('Billing: item estadia criado/atualizado', ['desc'=>$desc, 'valor'=>$subtotal]);
            }
            $factura->update(['valor_total' => $total]);
            return $factura;
        });
    }

    

    protected static function isAtracacaoVariable($tarifa): bool
    {
        $e = mb_strtolower(trim($tarifa->evento_disparo ?? ''));
        $d = mb_strtolower(trim($tarifa->descricao ?? ''));
        $u = mb_strtolower(trim($tarifa->unidade ?? ''));
        $isAtra = (strpos($e, 'atrac') !== false) || (strpos($d, 'atrac') !== false);
        $isVarUnit = in_array($u, ['hora','dia']);
        return $isAtra && $isVarUnit;
    }

    protected static function filterAtracacaoVariableTarifas($tarifas)
    {
        return $tarifas->filter(function($t){ return self::isAtracacaoVariable($t); });
    }

    protected static function appendAtracacaoVariableItems(Contrato $contrato, string $concessionariaId, $movimento, $tarifas)
    {
        return DB::transaction(function() use ($contrato,$concessionariaId,$movimento,$tarifas) {
            $factura = Factura::where('contrato_id',$contrato->id)
                ->where('entrada_saida_id',$movimento->id)
                ->where('metadados->evento','entrada')
                ->whereIn('status',['emitida','EMITIDA','aberta','ABERTA','open','OPEN'])
                ->first();
            if (!$factura) {
                $factura = Factura::create([
                    'concessionaria_id' => $concessionariaId,
                    'contrato_id' => $contrato->id,
                    'entrada_saida_id' => $movimento->id,
                    'numero' => self::nextNumber(),
                    'valor_total' => 0,
                    'status' => 'emitida',
                    'metadados' => ['evento' => 'entrada','tipo' => 'atracacao'],
                ]);
            }
            $start = ($movimento->data_efetiva ?? $movimento->data_programada) ? ($movimento->data_efetiva ?? $movimento->data_programada)->copy() : Carbon::now();
            $end = $movimento->atd ? $movimento->atd->copy() : Carbon::now();
            $total = (float)$factura->valor_total;
            foreach ($tarifas as $tarifa) {
                $u = strtolower(trim($tarifa->unidade ?? ''));
                if ($u === 'hora') {
                    $cursor = $start->copy()->startOfHour();
                    $limit = $end->copy()->startOfHour();
                    while ($cursor->lte($limit)) {
                        $label = $cursor->format('Y-m-d H:00');
                        $desc = (trim($tarifa->descricao ?? 'Atracação')) . ' - ' . $label;
                        $exists = FacturaItem::where('factura_id',$factura->id)->where('descricao',$desc)->exists();
                        if (!$exists) {
                            $unit = (float)($tarifa->valor_unitario ?? $tarifa->valor_fixo ?? 0);
                            $sub = round(1.0 * $unit, 4);
                            FacturaItem::create([
                                'factura_id' => $factura->id,
                                'descricao' => $desc,
                                'quantidade' => 1.0,
                                'preco_unitario' => $unit,
                                'subtotal' => $sub,
                            ]);
                            $total += $sub;
                        }
                        $cursor->addHour();
                    }
                } elseif ($u === 'dia') {
                    $cursor = $start->copy()->startOfDay();
                    $limit = $end->copy()->startOfDay();
                    while ($cursor->lte($limit)) {
                        $label = $cursor->toDateString();
                        $desc = (trim($tarifa->descricao ?? 'Atracação')) . ' - ' . $label;
                        $exists = FacturaItem::where('factura_id',$factura->id)->where('descricao',$desc)->exists();
                        if (!$exists) {
                            $unit = (float)($tarifa->valor_unitario ?? $tarifa->valor_fixo ?? 0);
                            $sub = round(1.0 * $unit, 4);
                            FacturaItem::create([
                                'factura_id' => $factura->id,
                                'descricao' => $desc,
                                'quantidade' => 1.0,
                                'preco_unitario' => $unit,
                                'subtotal' => $sub,
                            ]);
                            $total += $sub;
                        }
                        $cursor->addDay();
                    }
                }
            }
            $factura->update(['valor_total' => $total]);
            return $factura;
        });
    }

    protected static function createInvoiceFromTarifas(Contrato $contrato, string $concessionariaId, $tarifas, array $context, ?float $quantidade = null): ?Factura
    {
        $exists = Factura::where('contrato_id',$contrato->id)
            ->when(isset($context['entrada_saida_id']), function($q) use ($context){ $q->where('entrada_saida_id',$context['entrada_saida_id']); })
            ->when(isset($context['movimento_carga_id']), function($q) use ($context){ $q->where('movimento_carga_id',$context['movimento_carga_id']); })
            ->where('metadados->evento', $context['metadados']['evento'] ?? null)
            ->whereIn('status',['emitida','EMITIDA','aberta','ABERTA','open','OPEN'])
            ->first();
        if ($exists) {
            $factura = $exists;
            $total = (float)$factura->valor_total;
            foreach ($tarifas as $tarifa) {
                $eventoCtx = mb_strtolower($context['metadados']['evento'] ?? '');
                if ($eventoCtx === 'movimentacao' && isset($context['metadados']['unidade']) && !empty($tarifa->unidade)) {
                    if (mb_strtolower($tarifa->unidade) !== mb_strtolower($context['metadados']['unidade'])) {
                        continue;
                    }
                }
                if ($eventoCtx === 'movimentacao') {
                    $qty = (float)($context['metadados']['quantidade'] ?? $quantidade ?? 1.0);
                    $unitPrice = (float)($tarifa->valor_unitario ?? $tarifa->valor_fixo ?? 0);
                } else {
                    $qty = $tarifa->tipo === 'fixa' ? 1.0 : ($quantidade ?? 1.0);
                    $unitPrice = $tarifa->tipo === 'fixa' ? (float)($tarifa->valor_fixo ?? 0) : (float)($tarifa->valor_unitario ?? 0);
                }
                $subtotal = round($qty * $unitPrice, 4);
                FacturaItem::create([
                    'factura_id' => $factura->id,
                    'descricao' => $tarifa->descricao,
                    'quantidade' => $qty,
                    'preco_unitario' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }
            $factura->update(['valor_total' => $total]);
            return $factura;
        }

        return DB::transaction(function() use ($contrato,$concessionariaId,$tarifas,$context,$quantidade) {
            $factura = Factura::create([
                'concessionaria_id' => $concessionariaId,
                'contrato_id' => $contrato->id,
                'entrada_saida_id' => $context['entrada_saida_id'] ?? null,
                'movimento_carga_id' => $context['movimento_carga_id'] ?? null,
                'numero' => self::nextNumber(),
                'valor_total' => 0,
                'status' => 'emitida',
                'metadados' => $context['metadados'] ?? [],
            ]);

            $total = 0.0;
            foreach ($tarifas as $tarifa) {
                $eventoCtx = mb_strtolower(($context['metadados']['evento'] ?? ''));
                if ($eventoCtx === 'movimentacao' && isset($context['metadados']['unidade']) && !empty($tarifa->unidade)) {
                    if (mb_strtolower($tarifa->unidade) !== mb_strtolower($context['metadados']['unidade'])) {
                        continue;
                    }
                }
                if ($eventoCtx === 'movimentacao') {
                    $qty = (float)($context['metadados']['quantidade'] ?? $quantidade ?? 1.0);
                    $unitPrice = (float)($tarifa->valor_unitario ?? $tarifa->valor_fixo ?? 0);
                } else {
                    $qty = $tarifa->tipo === 'fixa' ? 1.0 : ($quantidade ?? 1.0);
                    $unitPrice = $tarifa->tipo === 'fixa' ? (float)($tarifa->valor_fixo ?? 0) : (float)($tarifa->valor_unitario ?? 0);
                }
                $subtotal = round($qty * $unitPrice, 4);

                FacturaItem::create([
                    'factura_id' => $factura->id,
                    'descricao' => $tarifa->descricao,
                    'quantidade' => $qty,
                    'preco_unitario' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $factura->update(['valor_total' => $total]);

            return $factura;
        });
    }

    protected static function nextNumber(): string
    {
        $year = date('Y');
        $count = Factura::whereYear('created_at', $year)->count() + 1;
        return 'FT-' . $year . '-' . str_pad((string)$count, 5, '0', STR_PAD_LEFT);
    }
}