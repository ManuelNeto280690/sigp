<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Porto extends Model
{
    use HasFactory;

    protected $table = 'portos';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nome',
        'slug',
        'email',
        'telefone',
        'endereco',
        'responsavel',
        'telefone_responsavel',
        'num_funcionarios',
        'dominio',
        'db_nome',
        'db_usuario',
        'db_senha',
        'path',
        'worker_status',
    ];

    protected $hidden = [
        'db_usuario',
        'db_senha',
    ];

    protected $casts = [
        'num_funcionarios' => 'integer',
        'db_usuario' => 'encrypted',
        'db_senha' => 'encrypted',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            if (empty($model->slug) && !empty($model->nome)) {
                $baseSlug = Str::slug($model->nome);
                $slug = $baseSlug;
                $i = 1;

                while (self::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $i++;
                }

                $model->slug = $slug;
            }
        });
    }
}