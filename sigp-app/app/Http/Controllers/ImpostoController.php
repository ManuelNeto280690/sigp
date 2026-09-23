<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Imposto;
use App\Models\Configuracao;
use Illuminate\Validation\Rule;

class ImpostoController extends Controller
{
    public function index()
    {
        $impostos = Imposto::orderBy('tipo')->orderBy('sigla')->get();
        $moeda = Configuracao::obter('faturacao_moeda', 'AOA');
        $serie = Configuracao::obter('faturacao_serie', date('Y'));
        return view('impostos.index', compact('impostos', 'moeda', 'serie'));
    }

    public function salvarConfiguracao(Request $request)
    {
        $request->validate([
            'moeda' => 'required|string|max:10',
            'serie' => 'required|string|max:20'
        ]);
        
        Configuracao::definir('faturacao_moeda', $request->moeda, 'string', 'faturacao', 'Moeda base da faturação');
        Configuracao::definir('faturacao_serie', $request->serie, 'string', 'faturacao', 'Série atual de faturação');
        
        return redirect()->route('impostos.index')->with('success', 'Configurações gerais atualizadas com sucesso.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:IVA,IS,IRT,II',
            'sigla' => 'required|string|max:50',
            'nome' => 'required|string|max:255',
            'taxa' => 'required|numeric|min:0|max:100',
            'motivo_isencao_codigo' => 'nullable|string|max:50',
            'motivo_isencao_descricao' => 'nullable|string|max:255',
            'ativo' => 'required|boolean',
        ]);

        Imposto::create($request->all());

        return redirect()->route('impostos.index')->with('success', 'Imposto criado com sucesso.');
    }

    public function update(Request $request, Imposto $imposto)
    {
        $request->validate([
            'tipo' => 'required|in:IVA,IS,IRT,II',
            'sigla' => 'required|string|max:50',
            'nome' => 'required|string|max:255',
            'taxa' => 'required|numeric|min:0|max:100',
            'motivo_isencao_codigo' => 'nullable|string|max:50',
            'motivo_isencao_descricao' => 'nullable|string|max:255',
            'ativo' => 'required|boolean',
        ]);

        $imposto->update($request->all());

        return redirect()->route('impostos.index')->with('success', 'Imposto atualizado com sucesso.');
    }

    public function destroy(Imposto $imposto)
    {
        try {
            $imposto->delete();
            return redirect()->route('impostos.index')->with('success', 'Imposto apagado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('impostos.index')->with('error', 'Não é possível apagar este imposto pois já está a ser utilizado em faturas.');
        }
    }
}
