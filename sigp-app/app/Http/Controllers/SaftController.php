<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SaftService;

class SaftController extends Controller
{
    public function index()
    {
        return view('saft.index');
    }

    public function export(Request $request)
    {
        $request->validate([
            'mes' => 'required|integer|min:1|max:12',
            'ano' => 'required|integer|min:2020|max:2099',
        ]);

        $mes = $request->input('mes');
        $ano = $request->input('ano');

        try {
            $service = new SaftService();
            $xmlContent = $service->generate($mes, $ano);

            $filename = "SAFT_AO_{$ano}_{$mes}.xml";

            return response($xmlContent, 200, [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao gerar SAF-T: ' . $e->getMessage());
        }
    }
}
