<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Inspecao extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'inspecoes';

    protected $fillable = [
        'embarcacao_id',
        'inspetor_id',
        'numero_inspecao',
        'tipo_inspecao',
        'data_inspecao',
        'hora_inicio',
        'hora_fim',
        'checklist_itens',
        'nao_conformidades',
        'observacoes',
        'resultado',
        'restricoes',
        'acoes_corretivas',
        'prazo_correcao',
        'status',
        'documentos_verificados',
        'evidencias',
        'aprovado_por',
        'aprovado_em',
        'is_active'
    ];

    protected $casts = [
        'data_inspecao' => 'datetime',
        'hora_inicio' => 'datetime',
        'hora_fim' => 'datetime',
        'prazo_correcao' => 'datetime',
        'aprovado_em' => 'datetime',
        'checklist_itens' => 'array',
        'nao_conformidades' => 'array',
        'documentos_verificados' => 'array',
        'evidencias' => 'array',
        'is_active' => 'boolean'
    ];

    protected $attributes = [
        'resultado' => 'aprovado',
        'status' => 'agendada',
        'is_active' => true
    ];

    // Relacionamentos
    public function embarcacao(): BelongsTo
    {
        return $this->belongsTo(Embarcacao::class);
    }

    public function inspetor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspetor_id');
    }

    public function aprovadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    // Scopes
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_inspecao', $tipo);
    }

    public function scopePorResultado($query, $resultado)
    {
        return $query->where('resultado', $resultado);
    }

    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorEmbarcacao($query, $embarcacaoId)
    {
        return $query->where('embarcacao_id', $embarcacaoId);
    }

    public function scopePorInspetor($query, $inspetorId)
    {
        return $query->where('inspetor_id', $inspetorId);
    }

    public function scopeAgendadas($query)
    {
        return $query->where('status', 'agendada');
    }

    public function scopeEmAndamento($query)
    {
        return $query->where('status', 'em_andamento');
    }

    public function scopeConcluidas($query)
    {
        return $query->where('status', 'concluida');
    }

    public function scopeCanceladas($query)
    {
        return $query->where('status', 'cancelada');
    }

    public function scopeAprovadas($query)
    {
        return $query->where('resultado', 'aprovado');
    }

    public function scopeAprovadasComRestricoes($query)
    {
        return $query->where('resultado', 'aprovado_com_restricoes');
    }

    public function scopeReprovadas($query)
    {
        return $query->where('resultado', 'reprovado');
    }

    public function scopeIsps($query)
    {
        return $query->where('tipo_inspecao', 'isps');
    }

    public function scopeAmbientais($query)
    {
        return $query->where('tipo_inspecao', 'ambiental');
    }

    public function scopeCais($query)
    {
        return $query->where('tipo_inspecao', 'cais');
    }

    public function scopeSeguranca($query)
    {
        return $query->where('tipo_inspecao', 'seguranca');
    }

    public function scopeSanitarias($query)
    {
        return $query->where('tipo_inspecao', 'sanitaria');
    }

    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_inspecao', [$dataInicio, $dataFim]);
    }

    public function scopeAprovadasSistema($query)
    {
        return $query->whereNotNull('aprovado_por');
    }

    public function scopePendentesAprovacao($query)
    {
        return $query->whereNull('aprovado_por')->where('status', 'concluida');
    }

    public function scopeVencendoEm($query, $dias)
    {
        return $query->where('prazo_correcao', '<=', now()->addDays($dias))
                    ->whereIn('resultado', ['aprovado_com_restricoes', 'reprovado'])
                    ->where('status', 'concluida');
    }

    public function scopeComNaoConformidades($query)
    {
        return $query->whereNotNull('nao_conformidades');
    }

    public function scopeComEvidencias($query)
    {
        return $query->whereNotNull('evidencias');
    }

    public function scopeAtivas($query)
    {
        return $query->where('is_active', true);
    }

    // Métodos auxiliares
    public function isAgendada(): bool
    {
        return $this->status === 'agendada';
    }

    public function isEmAndamento(): bool
    {
        return $this->status === 'em_andamento';
    }

    public function isConcluida(): bool
    {
        return $this->status === 'concluida';
    }

    public function isCancelada(): bool
    {
        return $this->status === 'cancelada';
    }

    public function isAprovada(): bool
    {
        return $this->resultado === 'aprovado';
    }

    public function isAprovadasComRestricoes(): bool
    {
        return $this->resultado === 'aprovado_com_restricoes';
    }

    public function isReprovada(): bool
    {
        return $this->resultado === 'reprovado';
    }

    public function isAprovadasSistema(): bool
    {
        return !is_null($this->aprovado_por);
    }

    public function temNaoConformidades(): bool
    {
        return !empty($this->nao_conformidades);
    }

    public function temEvidencias(): bool
    {
        return !empty($this->evidencias);
    }

    public function temRestricoes(): bool
    {
        return !empty($this->restricoes);
    }

    public function isVencida(): bool
    {
        return $this->prazo_correcao && 
               $this->prazo_correcao->isPast() && 
               in_array($this->resultado, ['aprovado_com_restricoes', 'reprovado']);
    }

    public function getDuracaoInspecao(): ?int
    {
        if ($this->hora_inicio && $this->hora_fim) {
            return $this->hora_inicio->diffInMinutes($this->hora_fim);
        }
        return null;
    }

    public function diasParaVencimento(): ?int
    {
        if (!$this->prazo_correcao || $this->isAprovada()) {
            return null;
        }
        
        return now()->diffInDays($this->prazo_correcao, false);
    }

    // Gestão de checklist
    public function adicionarItemChecklist(array $item): void
    {
        $checklist = $this->checklist_itens ?? [];
        $checklist[] = array_merge($item, [
            'verificado_em' => now()->toISOString(),
            'verificado_por' => Auth::id()
        ]);
        $this->checklist_itens = $checklist;
        $this->save();
    }

    public function atualizarItemChecklist(int $indice, array $item): void
    {
        $checklist = $this->checklist_itens ?? [];
        if (isset($checklist[$indice])) {
            $checklist[$indice] = array_merge($checklist[$indice], $item, [
                'atualizado_em' => now()->toISOString(),
                'atualizado_por' => Auth::id()
            ]);
            $this->checklist_itens = $checklist;
            $this->save();
        }
    }

    public function getQuantidadeItensChecklist(): int
    {
        return count($this->checklist_itens ?? []);
    }

    public function getItensAprovados(): int
    {
        $checklist = $this->checklist_itens ?? [];
        return count(array_filter($checklist, fn($item) => $item['conforme'] ?? false));
    }

    public function getItensReprovados(): int
    {
        $checklist = $this->checklist_itens ?? [];
        return count(array_filter($checklist, fn($item) => !($item['conforme'] ?? true)));
    }

    public function getPercentualConformidade(): float
    {
        $total = $this->getQuantidadeItensChecklist();
        if ($total === 0) return 0;
        
        return ($this->getItensAprovados() / $total) * 100;
    }

    // Gestão de não conformidades
    public function adicionarNaoConformidade(array $naoConformidade): void
    {
        $naoConformidades = $this->nao_conformidades ?? [];
        $naoConformidades[] = array_merge($naoConformidade, [
            'registrada_em' => now()->toISOString(),
            'registrada_por' => Auth::id()
        ]);
        $this->nao_conformidades = $naoConformidades;
        $this->save();
    }

    public function removerNaoConformidade(int $indice): void
    {
        $naoConformidades = $this->nao_conformidades ?? [];
        if (isset($naoConformidades[$indice])) {
            unset($naoConformidades[$indice]);
            $this->nao_conformidades = array_values($naoConformidades);
            $this->save();
        }
    }

    public function getQuantidadeNaoConformidades(): int
    {
        return count($this->nao_conformidades ?? []);
    }

    // Gestão de evidências
    public function adicionarEvidencia(array $evidencia): void
    {
        $evidencias = $this->evidencias ?? [];
        $evidencias[] = array_merge($evidencia, [
            'adicionada_em' => now()->toISOString(),
            'adicionada_por' => Auth::id()
        ]);
        $this->evidencias = $evidencias;
        $this->save();
    }

    public function removerEvidencia(int $indice): void
    {
        $evidencias = $this->evidencias ?? [];
        if (isset($evidencias[$indice])) {
            unset($evidencias[$indice]);
            $this->evidencias = array_values($evidencias);
            $this->save();
        }
    }

    public function getQuantidadeEvidencias(): int
    {
        return count($this->evidencias ?? []);
    }

    // Classes CSS para badges
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'agendada' => 'bg-blue-100 text-blue-800',
            'em_andamento' => 'bg-yellow-100 text-yellow-800',
            'concluida' => 'bg-green-100 text-green-800',
            'cancelada' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getResultadoBadgeClass(): string
    {
        return match($this->resultado) {
            'aprovado' => 'bg-green-100 text-green-800',
            'aprovado_com_restricoes' => 'bg-yellow-100 text-yellow-800',
            'reprovado' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getTipoInspecaoLabel(): string
    {
        return match($this->tipo_inspecao) {
            'isps' => 'ISPS',
            'ambiental' => 'Ambiental',
            'cais' => 'Cais',
            'seguranca' => 'Segurança',
            'sanitaria' => 'Sanitária',
            default => 'Não Definido'
        };
    }

    // Métodos de ação
    public function iniciar(): bool
    {
        if ($this->isAgendada()) {
            $this->status = 'em_andamento';
            $this->hora_inicio = now();
            return $this->save();
        }
        return false;
    }

    public function concluir(string $resultado = 'aprovado'): bool
    {
        if ($this->isEmAndamento()) {
            $this->status = 'concluida';
            $this->hora_fim = now();
            $this->resultado = $resultado;
            return $this->save();
        }
        return false;
    }

    public function cancelar(): bool
    {
        if (in_array($this->status, ['agendada', 'em_andamento'])) {
            $this->status = 'cancelada';
            return $this->save();
        }
        return false;
    }

    public function aprovarSistema(string $aprovadorId = null): bool
    {
        if ($this->isConcluida()) {
            $this->aprovado_por = $aprovadorId ?? Auth::id();
            $this->aprovado_em = now();
            return $this->save();
        }
        return false;
    }

    public function reagendar(\DateTime $novaData): bool
    {
        if ($this->isAgendada() || $this->isCancelada()) {
            $this->data_inspecao = $novaData;
            $this->status = 'agendada';
            return $this->save();
        }
        return false;
    }

    // Métodos estáticos para contadores e estatísticas
    public static function totalPorStatus(): array
    {
        return self::selectRaw('status, COUNT(*) as total')
                  ->groupBy('status')
                  ->pluck('total', 'status')
                  ->toArray();
    }

    public static function totalPorTipo(): array
    {
        return self::selectRaw('tipo_inspecao, COUNT(*) as total')
                  ->groupBy('tipo_inspecao')
                  ->pluck('total', 'tipo_inspecao')
                  ->toArray();
    }

    public static function totalPorResultado(): array
    {
        return self::selectRaw('resultado, COUNT(*) as total')
                  ->groupBy('resultado')
                  ->pluck('total', 'resultado')
                  ->toArray();
    }

    public static function mediaConformidade(): float
    {
        $inspecoes = self::concluidas()->get();
        if ($inspecoes->isEmpty()) return 0;
        
        $totalConformidade = $inspecoes->sum(fn($inspecao) => $inspecao->getPercentualConformidade());
        return $totalConformidade / $inspecoes->count();
    }

    public static function tempoMedioInspecao(): float
    {
        $inspecoes = self::concluidas()
                        ->whereNotNull('hora_inicio')
                        ->whereNotNull('hora_fim')
                        ->get();
        
        if ($inspecoes->isEmpty()) return 0;
        
        $tempoTotal = $inspecoes->sum(fn($inspecao) => $inspecao->getDuracaoInspecao());
        return $tempoTotal / $inspecoes->count();
    }

    public static function inspecoesHoje(): int
    {
        return self::whereDate('data_inspecao', today())->count();
    }

    public static function inspecoesMes(): int
    {
        return self::whereMonth('data_inspecao', now()->month)
                  ->whereYear('data_inspecao', now()->year)
                  ->count();
    }

    public static function inspecoesVencendoEm($dias = 7): int
    {
        return self::vencendoEm($dias)->count();
    }

    public static function inspecoesEmAndamento(): int
    {
        return self::emAndamento()->count();
    }

    // Geração automática de número da inspeção
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($inspecao) {
            if (empty($inspecao->numero_inspecao)) {
                $inspecao->numero_inspecao = self::gerarNumeroInspecao();
            }
            
            if (empty($inspecao->inspetor_id)) {
                $inspecao->inspetor_id = Auth::id();
            }
        });
    }

    private static function gerarNumeroInspecao(): string
    {
        $ano = date('Y');
        $ultimoNumero = self::whereYear('created_at', $ano)
                           ->max('numero_inspecao');
        
        if ($ultimoNumero) {
            $numero = intval(substr($ultimoNumero, -6)) + 1;
        } else {
            $numero = 1;
        }
        
        return 'INS' . $ano . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    // Validações
    public static function validationRules($id = null): array
    {
        return [
            'embarcacao_id' => 'required|exists:embarcacoes,id',
            'inspetor_id' => 'required|exists:users,id',
            'numero_inspecao' => [
                'required',
                'string',
                'max:255',
                Rule::unique('inspecoes')->ignore($id)
            ],
            'tipo_inspecao' => 'required|in:isps,ambiental,cais,seguranca,sanitaria',
            'data_inspecao' => 'required|date',
            'hora_inicio' => 'nullable|date',
            'hora_fim' => 'nullable|date|after:hora_inicio',
            'checklist_itens' => 'required|array',
            'nao_conformidades' => 'nullable|array',
            'observacoes' => 'nullable|string',
            'resultado' => 'required|in:aprovado,aprovado_com_restricoes,reprovado',
            'restricoes' => 'nullable|string',
            'acoes_corretivas' => 'nullable|string',
            'prazo_correcao' => 'nullable|date|after:data_inspecao',
            'status' => 'required|in:agendada,em_andamento,concluida,cancelada',
            'documentos_verificados' => 'nullable|array',
            'evidencias' => 'nullable|array',
            'aprovado_por' => 'nullable|exists:users,id',
            'is_active' => 'boolean'
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'embarcacao_id.required' => 'A embarcação é obrigatória.',
            'embarcacao_id.exists' => 'A embarcação selecionada não existe.',
            'inspetor_id.required' => 'O inspetor é obrigatório.',
            'inspetor_id.exists' => 'O inspetor selecionado não existe.',
            'numero_inspecao.required' => 'O número da inspeção é obrigatório.',
            'numero_inspecao.unique' => 'Este número de inspeção já está em uso.',
            'tipo_inspecao.required' => 'O tipo de inspeção é obrigatório.',
            'tipo_inspecao.in' => 'Tipo de inspeção inválido.',
            'data_inspecao.required' => 'A data da inspeção é obrigatória.',
            'hora_fim.after' => 'A hora de fim deve ser posterior à hora de início.',
            'checklist_itens.required' => 'Os itens do checklist são obrigatórios.',
            'checklist_itens.array' => 'Os itens do checklist devem ser um array.',
            'resultado.required' => 'O resultado é obrigatório.',
            'resultado.in' => 'Resultado inválido.',
            'prazo_correcao.after' => 'O prazo de correção deve ser posterior à data da inspeção.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'Status inválido.',
            'aprovado_por.exists' => 'O aprovador selecionado não existe.'
        ];
    }
}