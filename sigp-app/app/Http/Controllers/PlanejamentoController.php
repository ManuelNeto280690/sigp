<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Embarcacao;
use App\Models\Terminal;
use App\Models\EntradaSaidaEmbarcacao;
use App\Models\Berco;
use App\Models\Guindaste;

class PlanejamentoController extends Controller
{
    public function getEmbarcacaoInfo($id)
    {
        $embarcacao = Embarcacao::findOrFail($id);
        return response()->json([
            'tipo_embarcacao' => $embarcacao->tipo_embarcacao,
            'calado' => $embarcacao->calado,
        ]);
    }

    public function getTerminaisDisponiveis(Request $request)
    {
        $tipoEmbarcacao = $request->query('tipo_embarcacao');
        $caladoNavio = floatval($request->query('calado'));
        $dataPrevistaEntrada = str_replace('T', ' ', $request->query('data_entrada'));
        $dataPrevistaSaida = str_replace('T', ' ', $request->query('data_saida'));
        $ignorarMovimentoId = $request->query('ignorar_movimento_id');

        if (!$tipoEmbarcacao || !$dataPrevistaEntrada || !$dataPrevistaSaida) {
            return response()->json([], 400);
        }

        $terminais = Terminal::where('is_active', true)
            ->where('calado_maximo', '>=', $caladoNavio)
            ->get();

        $terminaisDisponiveis = [];

        foreach ($terminais as $terminal) {
            $movimentacoesConflitantes = EntradaSaidaEmbarcacao::where('terminal_id', $terminal->id)
                ->whereIn('status', ['programado', 'autorizado', 'em_andamento', 'atracado', 'operando'])
                ->when($ignorarMovimentoId, function ($q) use ($ignorarMovimentoId) {
                    return $q->where('id', '!=', $ignorarMovimentoId);
                })
                ->where(function ($query) use ($dataPrevistaEntrada, $dataPrevistaSaida) {
                    $query->whereBetween('data_programada', [$dataPrevistaEntrada, $dataPrevistaSaida])
                          ->orWhereBetween('data_efetiva', [$dataPrevistaEntrada, $dataPrevistaSaida])
                          ->orWhere(function($q) use ($dataPrevistaEntrada, $dataPrevistaSaida) {
                              $q->where('data_programada', '<=', $dataPrevistaEntrada)
                                ->where('data_efetiva', '>=', $dataPrevistaSaida);
                          });
                })
                ->get();

            $ocupados = $movimentacoesConflitantes->pluck('berco')->filter()->unique();

            $bercosLivres = Berco::where('terminal_id', $terminal->id)
                ->whereNotIn('nome', $ocupados)
                ->where('status', '!=', 'manutencao')
                ->orderBy('nome')
                ->pluck('nome');

            if ($bercosLivres->count() > 0) {
                $guindastesDisponiveis = Guindaste::where('terminal_id', $terminal->id)
                    ->where('status', 'disponivel')
                    ->orderBy('nome')
                    ->get(['id','nome']);

                $terminaisDisponiveis[] = [
                    'id' => $terminal->id,
                    'nome' => $terminal->nome,
                    'codigo' => $terminal->codigo,
                    'bercos_livres' => $bercosLivres->values()->all(),
                    'guindastes_disponiveis' => $guindastesDisponiveis->map(fn($g) => ['id' => $g->id, 'nome' => $g->nome])->values()->all(),
                ];
            }
        }

        return response()->json($terminaisDisponiveis);
    }

    public function getGuindastesPorTerminal(Request $request)
    {
        $terminalId = $request->query('terminal_id');
        if (!$terminalId) {
            return response()->json([], 400);
        }
        $guindastes = Guindaste::where('terminal_id', $terminalId)
            ->where('status', 'disponivel')
            ->orderBy('nome')
            ->get(['id','nome']);
        return response()->json($guindastes->map(fn($g) => ['id' => $g->id, 'nome' => $g->nome])->values()->all());
    }
}