<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Incidente extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'incidentes';

    protected $fillable = [
        'user_id',
        'embarcacao_id',
        'terminal_id',
        'numero_incidente',
        'titulo',
        'descricao',
        'tipo',
        'gravidade',
        'data_ocorrencia',
        'local_ocorrencia',
        'pessoas_envolvidas',
        'causas_identificadas',
        'acoes_imediatas',
        'acoes_corretivas',
        'status',
        'responsavel_investigacao',
        'data_fechamento',
        'requer_notificacao_autoridades',
        'is_active'
    ];

    protected $casts = [
        'pessoas_envolvidas' => 'array',
        'data_ocorrencia' => 'datetime',
        'data_fechamento' => 'datetime',
        'requer_notificacao_autoridades' => 'boolean',
        'is_active' => 'boolean'
    ];

    protected $attributes = [
        'gravidade' => 'media',
        'status' => 'aberto',
        'requer_notificacao_autoridades' => false,
        'is_active' => true
    ];

    // Relacionamentos
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function embarcacao(): BelongsTo
    {
        return $this->belongsTo(Embarcacao::class, 'embarcacao_id');
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'terminal_id');
    }

    public function responsavelInvestigacao(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_investigacao');
    }

    // Scopes
    public function scopeAtivo($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorGravidade($query, $gravidade)
    {
        return $query->where('gravidade', $gravidade);
    }

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePorEmbarcacao($query, $embarcacaoId)
    {
        return $query->where('embarcacao_id', $embarcacaoId);
    }

    public function scopePorTerminal($query, $terminalId)
    {
        return $query->where('terminal_id', $terminalId);
    }

    public function scopeAbertos($query)
    {
        return $query->where('status', 'aberto');
    }

    public function scopeInvestigando($query)
    {
        return $query->where('status', 'investigando');
    }

    public function scopeResolvidos($query)
    {
        return $query->where('status', 'resolvido');
    }

    public function scopeFechados($query)
    {
        return $query->where('status', 'fechado');
    }

    public function scopeCriticos($query)
    {
        return $query->where('gravidade', 'critica');
    }

    public function scopeAltos($query)
    {
        return $query->where('gravidade', 'alta');
    }

    public function scopeAcidentes($query)
    {
        return $query->where('tipo', 'acidente');
    }

    public function scopePoluicao($query)
    {
        return $query->where('tipo', 'poluicao');
    }

    public function scopeSeguranca($query)
    {
        return $query->where('tipo', 'seguranca');
    }

    public function scopeRequerNotificacao($query)
    {
        return $query->where('requer_notificacao_autoridades', true);
    }

    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_ocorrencia', [$dataInicio, $dataFim]);
    }

    public function scopeEmAndamento($query)
    {
        return $query->whereIn('status', ['aberto', 'investigando']);
    }

    public function scopeConcluidos($query)
    {
        return $query->whereIn('status', ['resolvido', 'fechado']);
    }

    // Métodos auxiliares
    public function isAberto(): bool
    {
        return $this->status === 'aberto';
    }

    public function isInvestigando(): bool
    {
        return $this->status === 'investigando';
    }

    public function isResolvido(): bool
    {
        return $this->status === 'resolvido';
    }

    public function isFechado(): bool
    {
        return $this->status === 'fechado';
    }

    public function isCritico(): bool
    {
        return $this->gravidade === 'critica';
    }

    public function isAlto(): bool
    {
        return $this->gravidade === 'alta';
    }

    public function isEmAndamento(): bool
    {
        return in_array($this->status, ['aberto', 'investigando']);
    }

    public function isConcluido(): bool
    {
        return in_array($this->status, ['resolvido', 'fechado']);
    }

    public function tempoInvestigacao(): ?int
    {
        if (!$this->data_fechamento) {
            return null;
        }
        
        return $this->data_ocorrencia->diffInDays($this->data_fechamento);
    }

    public function diasEmAberto(): int
    {
        $dataFim = $this->data_fechamento ?? now();
        return $this->data_ocorrencia->diffInDays($dataFim);
    }

    public function getGravidadeBadgeClass(): string
    {
        return match($this->gravidade) {
            'baixa' => 'bg-green-100 text-green-800',
            'media' => 'bg-yellow-100 text-yellow-800',
            'alta' => 'bg-orange-100 text-orange-800',
            'critica' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'aberto' => 'bg-red-100 text-red-800',
            'investigando' => 'bg-blue-100 text-blue-800',
            'resolvido' => 'bg-green-100 text-green-800',
            'fechado' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getTipoBadgeClass(): string
    {
        return match($this->tipo) {
            'acidente' => 'bg-red-100 text-red-800',
            'avaria' => 'bg-orange-100 text-orange-800',
            'poluicao' => 'bg-green-100 text-green-800',
            'seguranca' => 'bg-yellow-100 text-yellow-800',
            'operacional' => 'bg-blue-100 text-blue-800',
            'outros' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    // Métodos de ação
    public function iniciarInvestigacao($responsavelId): bool
    {
        if (!$this->isAberto()) {
            return false;
        }

        $this->update([
            'status' => 'investigando',
            'responsavel_investigacao' => $responsavelId
        ]);

        return true;
    }

    public function resolver($acoes_corretivas = null): bool
    {
        if (!in_array($this->status, ['aberto', 'investigando'])) {
            return false;
        }

        $this->update([
            'status' => 'resolvido',
            'acoes_corretivas' => $acoes_corretivas ?? $this->acoes_corretivas
        ]);

        return true;
    }

    public function fechar(): bool
    {
        if (!$this->isResolvido()) {
            return false;
        }

        $this->update([
            'status' => 'fechado',
            'data_fechamento' => now()
        ]);

        return true;
    }

    public function reabrir(): bool
    {
        if (!$this->isFechado()) {
            return false;
        }

        $this->update([
            'status' => 'aberto',
            'data_fechamento' => null,
            'responsavel_investigacao' => null
        ]);

        return true;
    }

    // Métodos para pessoas envolvidas
    public function adicionarPessoaEnvolvida($pessoa): void
    {
        $pessoas = $this->pessoas_envolvidas ?? [];
        $pessoas[] = $pessoa;
        $this->update(['pessoas_envolvidas' => $pessoas]);
    }

    public function removerPessoaEnvolvida($indice): void
    {
        $pessoas = $this->pessoas_envolvidas ?? [];
        if (isset($pessoas[$indice])) {
            unset($pessoas[$indice]);
            $this->update(['pessoas_envolvidas' => array_values($pessoas)]);
        }
    }

    public function getPessoasEnvolvidasTexto(): string
    {
        if (empty($this->pessoas_envolvidas)) {
            return 'Nenhuma pessoa especificada';
        }
        
        $nomes = array_map(function($pessoa) {
            return is_array($pessoa) ? ($pessoa['nome'] ?? 'Nome não informado') : $pessoa;
        }, $this->pessoas_envolvidas);
        
        return implode(', ', $nomes);
    }

    public function getQuantidadePessoasEnvolvidas(): int
    {
        return count($this->pessoas_envolvidas ?? []);
    }

    // Métodos estáticos
    public static function incidentesAbertos(): int
    {
        return static::abertos()->count();
    }

    public static function incidentesCriticos(): int
    {
        return static::criticos()->emAndamento()->count();
    }

    public static function incidentesPorTipo(): array
    {
        return static::ativo()
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->toArray();
    }

    public static function incidentesPorGravidade(): array
    {
        return static::ativo()
            ->selectRaw('gravidade, COUNT(*) as total')
            ->groupBy('gravidade')
            ->pluck('total', 'gravidade')
            ->toArray();
    }

    public static function tempoMedioResolucao(): ?float
    {
        $incidentes = static::fechados()
            ->whereNotNull('data_fechamento')
            ->get();
            
        if ($incidentes->isEmpty()) {
            return null;
        }
        
        $totalDias = $incidentes->sum(function($incidente) {
            return $incidente->tempoInvestigacao();
        });
        
        return $totalDias / $incidentes->count();
    }

    // Geração automática de número do incidente
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($incidente) {
            if (empty($incidente->numero_incidente)) {
                $incidente->numero_incidente = static::gerarNumeroIncidente();
            }
        });
    }

    public static function gerarNumeroIncidente(): string
    {
        $ano = date('Y');
        $ultimoIncidente = static::whereYear('created_at', $ano)
            ->orderBy('created_at', 'desc')
            ->first();

        $sequencial = 1;
        if ($ultimoIncidente && $ultimoIncidente->numero_incidente) {
            $ultimoNumero = (int) substr($ultimoIncidente->numero_incidente, -6);
            $sequencial = $ultimoNumero + 1;
        }

        return 'INC' . $ano . str_pad($sequencial, 6, '0', STR_PAD_LEFT);
    }

    // Validações
    public static function validationRules($id = null): array
    {
        return [
            'user_id' => 'required|uuid|exists:users,id',
            'embarcacao_id' => 'nullable|uuid|exists:embarcacoes,id',
            'terminal_id' => 'nullable|uuid|exists:terminais,id',
            'numero_incidente' => [
                'required',
                'string',
                'max:255',
                Rule::unique('incidentes')->ignore($id)
            ],
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string|max:2000',
            'tipo' => 'required|in:acidente,avaria,poluicao,seguranca,operacional,outros',
            'gravidade' => 'required|in:baixa,media,alta,critica',
            'data_ocorrencia' => 'required|date|before_or_equal:now',
            'local_ocorrencia' => 'required|string|max:255',
            'pessoas_envolvidas' => 'nullable|array',
            'pessoas_envolvidas.*.nome' => 'required_with:pessoas_envolvidas|string|max:255',
            'pessoas_envolvidas.*.cargo' => 'nullable|string|max:255',
            'pessoas_envolvidas.*.empresa' => 'nullable|string|max:255',
            'causas_identificadas' => 'nullable|string|max:2000',
            'acoes_imediatas' => 'nullable|string|max:2000',
            'acoes_corretivas' => 'nullable|string|max:2000',
            'status' => 'required|in:aberto,investigando,resolvido,fechado',
            'responsavel_investigacao' => 'nullable|uuid|exists:users,id',
            'data_fechamento' => 'nullable|date|after:data_ocorrencia',
            'requer_notificacao_autoridades' => 'boolean',
            'is_active' => 'boolean'
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'user_id.required' => 'O usuário reportador é obrigatório.',
            'user_id.exists' => 'O usuário selecionado não existe.',
            'embarcacao_id.exists' => 'A embarcação selecionada não existe.',
            'terminal_id.exists' => 'O terminal selecionado não existe.',
            'numero_incidente.required' => 'O número do incidente é obrigatório.',
            'numero_incidente.unique' => 'Este número de incidente já existe.',
            'titulo.required' => 'O título é obrigatório.',
            'titulo.max' => 'O título não pode ter mais de 255 caracteres.',
            'descricao.required' => 'A descrição é obrigatória.',
            'descricao.max' => 'A descrição não pode ter mais de 2000 caracteres.',
            'tipo.required' => 'O tipo do incidente é obrigatório.',
            'tipo.in' => 'O tipo deve ser: acidente, avaria, poluição, segurança, operacional ou outros.',
            'gravidade.required' => 'A gravidade é obrigatória.',
            'gravidade.in' => 'A gravidade deve ser: baixa, média, alta ou crítica.',
            'data_ocorrencia.required' => 'A data de ocorrência é obrigatória.',
            'data_ocorrencia.date' => 'A data de ocorrência deve ser uma data válida.',
            'data_ocorrencia.before_or_equal' => 'A data de ocorrência não pode ser no futuro.',
            'local_ocorrencia.required' => 'O local de ocorrência é obrigatório.',
            'local_ocorrencia.max' => 'O local de ocorrência não pode ter mais de 255 caracteres.',
            'pessoas_envolvidas.array' => 'As pessoas envolvidas devem ser uma lista.',
            'pessoas_envolvidas.*.nome.required_with' => 'O nome da pessoa é obrigatório.',
            'pessoas_envolvidas.*.nome.max' => 'O nome não pode ter mais de 255 caracteres.',
            'pessoas_envolvidas.*.cargo.max' => 'O cargo não pode ter mais de 255 caracteres.',
            'pessoas_envolvidas.*.empresa.max' => 'A empresa não pode ter mais de 255 caracteres.',
            'causas_identificadas.max' => 'As causas identificadas não podem ter mais de 2000 caracteres.',
            'acoes_imediatas.max' => 'As ações imediatas não podem ter mais de 2000 caracteres.',
            'acoes_corretivas.max' => 'As ações corretivas não podem ter mais de 2000 caracteres.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser: aberto, investigando, resolvido ou fechado.',
            'responsavel_investigacao.exists' => 'O responsável pela investigação não existe.',
            'data_fechamento.date' => 'A data de fechamento deve ser uma data válida.',
            'data_fechamento.after' => 'A data de fechamento deve ser posterior à data de ocorrência.'
        ];
    }

    public function validate(array $data = null): \Illuminate\Validation\Validator
    {
        $data = $data ?? $this->toArray();
        return Validator::make($data, static::validationRules($this->id), static::validationMessages());
    }
}