<?php
namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\AuditLog;
use App\Models\FacturaItem;
use App\Models\Concessionaria;
use App\Models\Imposto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\ConfigHelper;

class FacturaController extends Controller
{
    public function index(Request $request)
    {
        $query = Factura::with('contrato')
            ->when($request->status, fn($q,$s) => $q->where('status',$s))
            ->when($request->tipo_documento, fn($q,$td) => $q->where('tipo_documento','like', $td.'%'))
            ->when($request->concessionaria_id, fn($q,$c) => $q->where('concessionaria_id',$c))
            ->when($request->contrato_id, fn($q,$cid) => $q->where('contrato_id',$cid))
            ->when($request->search, fn($q,$search) => $q->where('numero', 'like', "%{$search}%"))
            ->when($request->data_inicio, fn($q,$data) => $q->whereDate('created_at', '>=', $data))
            ->when($request->data_fim, fn($q,$data) => $q->whereDate('created_at', '<=', $data))
            ->orderByDesc('created_at');

        $facturas = $query->paginate(15);
        return view('facturas.index', compact('facturas'));
    }

    public function relatorioFiltrado(Request $request)
    {
        $query = Factura::with('contrato', 'concessionaria')
            ->when($request->status, fn($q,$s) => $q->where('status',$s))
            ->when($request->tipo_documento, fn($q,$td) => $q->where('tipo_documento','like', $td.'%'))
            ->when($request->search, fn($q,$search) => $q->where('numero', 'like', "%{$search}%"))
            ->when($request->data_inicio, fn($q,$data) => $q->whereDate('created_at', '>=', $data))
            ->when($request->data_fim, fn($q,$data) => $q->whereDate('created_at', '<=', $data))
            ->orderBy('created_at', 'asc');

        $facturas = $query->get();
        $formato = $request->input('formato', 'pdf');

        if ($formato === 'excel') {
            $csvData = "Numero;Status;Data;Cliente;NIF;Valor S/ IVA;IVA;Imposto Selo;Retencao;Total a Pagar\n";
            foreach ($facturas as $f) {
                $cliente = $f->concessionaria->nome ?? '-';
                $nif = $f->concessionaria->nif ?? '-';
                $dataEmissao = $f->created_at->format('d/m/Y H:i');
                
                $totalPagar = $f->total_a_pagar > 0 ? $f->total_a_pagar : $f->valor_total;
                $subTotal = $f->total_s_iva > 0 ? $f->total_s_iva : $totalPagar;
                
                $csvData .= "{$f->numero};{$f->status};{$dataEmissao};{$cliente};{$nif};{$subTotal};{$f->total_iva};{$f->total_imposto_selo};{$f->total_retencao};{$totalPagar}\n";
            }
            
            return response($csvData)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="relatorio_faturas.csv"');
        }

        // PDF Formato
        // Obter configurações do sistema para o cabeçalho do PDF
        $logoRelatorios = ConfigHelper::get('logo_relatorios');
        $logoBase64 = null;
        if (extension_loaded('gd') && $logoRelatorios) {
            $caminhosPossiveis = [
                public_path('storage/' . $logoRelatorios),
                storage_path('app/public/' . $logoRelatorios),
                public_path($logoRelatorios)
            ];
            foreach ($caminhosPossiveis as $path) {
                if (file_exists($path)) {
                    try {
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = file_get_contents($path);
                        $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        break;
                    } catch (\Exception $e) {}
                }
            }
        }
        
        $configuracoes = [
            'logo_relatorios' => $logoBase64,
            'cabecalho' => ConfigHelper::get('cabecalho_documentos'),
            'rodape' => ConfigHelper::get('rodape_relatorios', 'Relatório gerado automaticamente pelo SIGP'),
            'nome_porto' => ConfigHelper::get('nome_empresa', 'Porto de Soyo')
        ];

        $pdf = Pdf::loadView('facturas.relatorio_pdf', compact('facturas', 'configuracoes', 'request'));
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('relatorio_faturas_' . date('Ymd_His') . '.pdf');
    }

    public function create()
    {
        $concessionarias = Concessionaria::orderBy('nome')->get();
        $impostos = Imposto::where('ativo', true)->orderBy('tipo')->get();
        $impostosIva = $impostos->whereIn('tipo', ['IVA', 'IS']);
        $impostosRetencao = $impostos->whereIn('tipo', ['IRT', 'II']);
        return view('facturas.create', compact('concessionarias', 'impostosIva', 'impostosRetencao'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'concessionaria_id' => 'required|exists:concessionarias,id',
            'tipo_documento' => 'required|in:FT,FR,FP,FS,VD,ND,NC,OR',
            'items' => 'required|array|min:1',
            'items.*.descricao' => 'required|string|max:255',
            'items.*.quantidade' => 'required|numeric|min:0.01',
            'items.*.preco_unitario' => 'required|numeric|min:0',
            'items.*.imposto_id' => 'required|exists:impostos,id',
            'retencao_imposto_id' => 'nullable|exists:impostos,id',
        ]);

        try {
            DB::beginTransaction();

            $total_s_iva = 0;
            $total_iva = 0;
            $total_imposto_selo = 0;

            // Pré-calcular totais para a fatura (evitando manipulação no frontend)
            $itemsData = [];
            foreach ($request->items as $itemData) {
                $imposto = Imposto::findOrFail($itemData['imposto_id']);
                
                $quantidade = (float) $itemData['quantidade'];
                $preco_unitario = (float) $itemData['preco_unitario'];
                $subtotal = $quantidade * $preco_unitario;
                
                $valor_imposto = 0;
                if ($imposto->taxa > 0) {
                    $valor_imposto = $subtotal * ($imposto->taxa / 100);
                }

                if ($imposto->tipo === 'IVA') {
                    $total_iva += $valor_imposto;
                } elseif ($imposto->tipo === 'IS') {
                    $total_imposto_selo += $valor_imposto;
                }

                $total_s_iva += $subtotal;

                $itemsData[] = [
                    'descricao' => $itemData['descricao'],
                    'quantidade' => $quantidade,
                    'preco_unitario' => $preco_unitario,
                    'subtotal' => $subtotal,
                    'taxa_iva_id' => $imposto->id,
                    'valor_iva' => $valor_imposto,
                    'motivo_isencao_codigo' => $imposto->motivo_isencao_codigo,
                    'sujeito_retencao' => $request->filled('retencao_imposto_id') // Marcamos verdadeiro se houver retenção global
                ];
            }

            $total_retencao = 0;
            if ($request->filled('retencao_imposto_id')) {
                $impostoRetencao = Imposto::findOrFail($request->retencao_imposto_id);
                if ($impostoRetencao->taxa > 0) {
                    $total_retencao = $total_s_iva * ($impostoRetencao->taxa / 100);
                }
            }

            $total_a_pagar = ($total_s_iva + $total_iva + $total_imposto_selo) - $total_retencao;

            $factura = Factura::create([
                'concessionaria_id' => $request->concessionaria_id,
                'tipo_documento' => $request->tipo_documento,
                'status' => 'emitida',
                'valor_total' => $total_a_pagar,
                'total_s_iva' => $total_s_iva,
                'total_iva' => $total_iva,
                'total_imposto_selo' => $total_imposto_selo,
                'total_retencao' => $total_retencao,
                'total_a_pagar' => $total_a_pagar,
                'metadados' => ['origem' => 'manual', 'criado_por' => auth()->id()]
            ]);

            foreach ($itemsData as $it) {
                $factura->items()->create($it);
            }

            DB::commit();

            return redirect()->route('facturas.show', $factura)->with('success', 'Fatura criada e assinada com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao criar fatura: ' . $e->getMessage());
        }
    }

    public function show(Factura $factura)
    {
        $factura->load('items','contrato');
        return view('facturas.show', compact('factura'));
    }

    public function marcarPaga(Request $request, Factura $factura)
    {
        return DB::transaction(function() use ($request, $factura) {
            $old = $factura->getAttributes();
            $factura->update(['status' => 'paga']);

            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'auditable_type' => 'App\\Models\\Factura',
                'auditable_id' => $factura->id,
                'description' => 'Marcou fatura como paga',
                'old_values' => $old,
                'new_values' => ['status' => 'paga'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('facturas.show',$factura)->with('success','Fatura marcada como paga.');
        });
    }

    public function emitirNotaCredito(Request $request, Factura $factura)
    {
        // Placeholder para a lógica de emissão de Nota de Crédito
        // A Nota de Crédito deve ser uma nova Factura com valores negativos, referenciando a fatura original
        
        return DB::transaction(function() use ($request, $factura) {
            // 1. Criar Nota de Crédito (Tipo NC) baseada na fatura original
            $nc = $factura->replicate();
            $nc->tipo_documento = 'NC';
            $nc->valor_total = -abs($factura->valor_total);
            $nc->total_s_iva = -abs($factura->total_s_iva);
            $nc->total_iva = -abs($factura->total_iva);
            $nc->total_imposto_selo = -abs($factura->total_imposto_selo);
            $nc->total_retencao = -abs($factura->total_retencao);
            $nc->total_a_pagar = -abs($factura->total_a_pagar);
            $nc->status = 'emitida';
            
            // Gravar nos metadados a referência da fatura anulada
            $meta = $nc->metadados ?? [];
            $meta['fatura_referencia'] = $factura->numero;
            $meta['motivo_anulacao'] = $request->input('motivo', 'Anulação por erro operacional');
            $nc->metadados = $meta;
            
            $nc->save();

            // 2. Replicar os items com valores negativos
            foreach($factura->items as $item) {
                $ncItem = $item->replicate();
                $ncItem->factura_id = $nc->id;
                $ncItem->quantidade = -abs($item->quantidade);
                $ncItem->subtotal = -abs($item->subtotal);
                $ncItem->valor_iva = -abs($item->valor_iva);
                $ncItem->save();
            }

            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'create',
                'auditable_type' => 'App\\Models\\Factura',
                'auditable_id' => $nc->id,
                'description' => 'Emitiu Nota de Crédito para anular fatura ' . $factura->numero,
                'new_values' => ['tipo_documento' => 'NC'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('facturas.show', $nc)->with('success', 'Nota de Crédito emitida com sucesso.');
        });
    }

    public function exportPdf(Factura $factura)
    {
        try {
            $factura->load(['items', 'contrato', 'entradaSaida.embarcacao', 'entradaSaida.terminal', 'concessionaria']);


                       
                            // Obter configurações do sistema
                            $configuracoes = [
                                'logo_relatorios' => ConfigHelper::get('logo_relatorios', ''),
                                'cabecalho' => ConfigHelper::get('cabecalho_documentos'),
                                'rodape' => ConfigHelper::get('rodape_relatorios', 'Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária'),
                                // Fallbacks para dados do porto se não existirem na config
                                'nome_porto' => ConfigHelper::get('nome_porto', 'Porto de Sines'),
                                'endereco_porto' => ConfigHelper::get('endereco_porto', 'Rua do Porto, 123, Sines'),
                                'email_porto' => ConfigHelper::get('email_porto', 'geral@portosines.pt'),
                                'telefone_porto' => ConfigHelper::get('telefone_porto', '+351 269 123 456'),
                                'nif_porto' => ConfigHelper::get('nif_porto', '500123456')
                            ];
                      
                            // Obter configurações do sistema
                            $logoRelatorios = ConfigHelper::get('logo_relatorios');
                            $logoPath = null;
                            
                            if ($logoRelatorios) {
                                // Tenta resolver o caminho absoluto do logo para o DomPDF
                                $caminhosPossiveis = [
                                    public_path('storage/' . $logoRelatorios),
                                    storage_path('app/public/' . $logoRelatorios),
                                    public_path($logoRelatorios)
                                ];
                        
                                foreach ($caminhosPossiveis as $path) {
                                    if (file_exists($path)) {
                                        $logoPath = $path;
                                        break;
                                    }
                                }
                            }
                        
                            $configuracoes = [
                                'logo_relatorios' => $logoPath,
                                'cabecalho' => ConfigHelper::get('cabecalho_documentos'),
                                'rodape' => ConfigHelper::get('rodape_relatorios', 'Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária'),
                                
                                // Dados da Empresa/Porto (Mapeamento correto conforme solicitado)
                                'nome_porto' => ConfigHelper::get('nome_empresa', 'Porto de Soyo'),
                                'endereco_porto' => ConfigHelper::get('endereco_completo', 'Porto de Soyo, Província do Zaire, Angola'),
                                'email_porto' => ConfigHelper::get('email_contato', 'contato@portodesoyo.ao'),
                                'telefone_porto' => ConfigHelper::get('telefone_contato', ''),
                                'nif_porto' => ConfigHelper::get('cnpj_empresa', '')
                            ];
 
                        // Obter configurações do sistema
                        $logoRelatorios = ConfigHelper::get('logo_relatorios');
                        $logoBase64 = null;
                        
                        if ($logoRelatorios) {
                            // Tenta resolver o caminho absoluto do logo
                            $caminhosPossiveis = [
                                public_path('storage/' . $logoRelatorios),
                                storage_path('app/public/' . $logoRelatorios),
                                public_path($logoRelatorios)
                            ];
                    
                            foreach ($caminhosPossiveis as $path) {
                                if (file_exists($path)) {
                                    try {
                                        $type = pathinfo($path, PATHINFO_EXTENSION);
                                        $data = file_get_contents($path);
                                        $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                        break;
                                    } catch (\Exception $e) {
                                        \Illuminate\Support\Facades\Log::error('Erro ao converter logo para base64: ' . $e->getMessage());
                                    }
                                }
                            }
                        }
                    
                            $configuracoes = [
                                'logo_relatorios' => $logoBase64,
                                'cabecalho' => ConfigHelper::get('cabecalho_documentos'),
                                'rodape' => ConfigHelper::get('rodape_relatorios', 'Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária'),
                                
                                // Dados da Empresa/Porto (Mapeamento correto conforme solicitado)
                                'nome_porto' => ConfigHelper::get('nome_empresa', 'Porto de Soyo'),
                                'endereco_porto' => ConfigHelper::get('endereco_completo', 'Porto de Soyo, Província do Zaire, Angola'),
                                'email_porto' => ConfigHelper::get('email_contato', 'contato@portodesoyo.ao'),
                                'telefone_porto' => ConfigHelper::get('telefone_contato', ''),
                                'nif_porto' => ConfigHelper::get('cnpj_empresa', '')
                            ];

            // Obter configurações do sistema
            $logoRelatorios = ConfigHelper::get('logo_relatorios');
            $logoBase64 = null;
            
            // Verificar se a extensão GD está ativa (necessária para imagens no PDF)
            if (extension_loaded('gd') && $logoRelatorios) {
                // Tenta resolver o caminho absoluto do logo
                $caminhosPossiveis = [
                    public_path('storage/' . $logoRelatorios),
                    storage_path('app/public/' . $logoRelatorios),
                    public_path($logoRelatorios)
                ];

                foreach ($caminhosPossiveis as $path) {
                    if (file_exists($path)) {
                        try {
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $data = file_get_contents($path);
                            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            break;
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Erro ao converter logo para base64: ' . $e->getMessage());
                        }
                    }
                }
            } elseif (!extension_loaded('gd')) {
                \Illuminate\Support\Facades\Log::warning('Extensão GD do PHP não está ativa. O logotipo não será exibido no PDF.');
            }
        
            $configuracoes = [
                'logo_relatorios' => $logoBase64,
                'cabecalho' => ConfigHelper::get('cabecalho_documentos'),
                'rodape' => ConfigHelper::get('rodape_relatorios', 'Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária'),
                
                // Dados da Empresa/Porto
                'nome_porto' => ConfigHelper::get('nome_empresa', 'Porto de Soyo'),
                'endereco_porto' => ConfigHelper::get('endereco_completo', 'Porto de Soyo, Província do Zaire, Angola'),
                'email_porto' => ConfigHelper::get('email_contato', 'contato@portodesoyo.ao'),
                'telefone_porto' => ConfigHelper::get('telefone_contato', ''),
                'nif_porto' => ConfigHelper::get('cnpj_empresa', '')
            ];




            // Se cabecalho_documentos for string JSON, decodificar
            if (is_string($configuracoes['cabecalho'])) {
                $configuracoes['cabecalho'] = json_decode($configuracoes['cabecalho'], true) ?: $configuracoes['cabecalho'];
            }

            $pdf = Pdf::loadView('facturas.pdf', compact('factura', 'configuracoes'));
            
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial'
            ]);
            
            $safeNumero = str_replace(['/', '\\'], '-', $factura->numero);
            $filename = 'factura_' . $safeNumero . '_' . date('Y-m-d_H-i-s') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao gerar PDF da fatura: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar PDF da fatura.');
        }
    }

    public function sendEmail(Request $request, Factura $factura)
    {
        try {
            $destinatario = $request->input('email_destino', $factura->concessionaria->email ?? '');
            $assunto = $request->input('assunto', "Envio de Documento - {$factura->numero}");
            $mensagemTexto = $request->input('mensagem', "Segue em anexo o documento.");

            if (!$destinatario) {
                return back()->with('error', 'Por favor, indique um endereço de e-mail válido.');
            }

            $factura->load(['items', 'contrato', 'entradaSaida.embarcacao', 'entradaSaida.terminal', 'concessionaria']);

            $logoRelatorios = ConfigHelper::get('logo_relatorios');
            $logoBase64 = null;
            if (extension_loaded('gd') && $logoRelatorios) {
                $caminhosPossiveis = [
                    public_path('storage/' . $logoRelatorios),
                    storage_path('app/public/' . $logoRelatorios),
                    public_path($logoRelatorios)
                ];
                foreach ($caminhosPossiveis as $path) {
                    if (file_exists($path)) {
                        try {
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $data = file_get_contents($path);
                            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            break;
                        } catch (\Exception $e) {
                        }
                    }
                }
            }
        
            $configuracoes = [
                'logo_relatorios' => $logoBase64,
                'cabecalho' => ConfigHelper::get('cabecalho_documentos'),
                'rodape' => ConfigHelper::get('rodape_relatorios', 'Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária'),
                'nome_porto' => ConfigHelper::get('nome_empresa', 'Porto de Soyo'),
                'endereco_porto' => ConfigHelper::get('endereco_completo', 'Porto de Soyo, Província do Zaire, Angola'),
                'email_porto' => ConfigHelper::get('email_contato', 'contato@portodesoyo.ao'),
                'telefone_porto' => ConfigHelper::get('telefone_contato', ''),
                'nif_porto' => ConfigHelper::get('cnpj_empresa', '')
            ];

            if (is_string($configuracoes['cabecalho'])) {
                $configuracoes['cabecalho'] = json_decode($configuracoes['cabecalho'], true) ?: $configuracoes['cabecalho'];
            }

            $pdf = Pdf::loadView('facturas.pdf', compact('factura', 'configuracoes'));
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial'
            ]);
            
            $pdfContent = $pdf->output();
            $safeNumero = str_replace(['/', '\\'], '-', $factura->numero);
            $filename = 'factura_' . $safeNumero . '.pdf';

            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($destinatario, $assunto, $mensagemTexto, $pdfContent, $filename) {
                // Convert newlines to <br> for HTML email
                $htmlBody = nl2br(e($mensagemTexto));
                
                $message->to($destinatario)
                        ->subject($assunto)
                        ->html($htmlBody)
                        ->attachData($pdfContent, $filename, ['mime' => 'application/pdf']);
            });

            return back()->with('success', 'E-mail enviado com sucesso com o PDF em anexo!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao enviar e-mail da fatura: ' . $e->getMessage());
            return back()->with('error', 'Erro de Servidor (SMTP) ao enviar e-mail: ' . $e->getMessage());
        }
    }
}