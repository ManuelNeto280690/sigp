<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware('role:admin|gestor')->except(['index', 'show', 'export']);
        $this->middleware('role:admin|gestor|operador')->only(['index', 'show', 'export']);
    }

    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request)
    {
        $query = AuditLog::with(['user'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('user_agent', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->paginate(20);

        // Estatísticas para dashboard
        $stats = [
            'total_logs' => AuditLog::count(),
            'logs_today' => AuditLog::whereDate('created_at', today())->count(),
            'logs_this_week' => AuditLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'logs_this_month' => AuditLog::whereMonth('created_at', now()->month)->count(),
            'unique_users' => AuditLog::distinct('user_id')->count('user_id'),
            'events_count' => AuditLog::select('event', DB::raw('count(*) as total'))
                                    ->groupBy('event')
                                    ->orderBy('total', 'desc')
                                    ->limit(10)
                                    ->get(),
            'models_count' => AuditLog::select('auditable_type', DB::raw('count(*) as total'))
                                    ->groupBy('auditable_type')
                                    ->orderBy('total', 'desc')
                                    ->limit(10)
                                    ->get(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'logs' => $logs,
                'stats' => $stats
            ]);
        }

        return view('audit-logs.index', compact('logs', 'stats'));
    }

    /**
     * Display the specified audit log.
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load(['user']);
        
        // Logs relacionados (mesmo modelo e ID)
        $relatedLogs = AuditLog::where('auditable_type', $auditLog->auditable_type)
            ->where('auditable_id', $auditLog->auditable_id)
            ->where('id', '!=', $auditLog->id)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('audit-logs.show', compact('auditLog', 'relatedLogs'));
    }

    /**
     * Dashboard with statistics and charts.
     */
    public function dashboard(Request $request)
    {
        $period = $request->get('period', '30'); // dias
        $startDate = now()->subDays($period);

        // Estatísticas gerais
        $stats = [
            'total_logs' => AuditLog::where('created_at', '>=', $startDate)->count(),
            'unique_users' => AuditLog::where('created_at', '>=', $startDate)
                                   ->distinct('user_id')
                                   ->count('user_id'),
            'unique_ips' => AuditLog::where('created_at', '>=', $startDate)
                                  ->distinct('ip_address')
                                  ->count('ip_address'),
            'failed_attempts' => AuditLog::where('created_at', '>=', $startDate)
                                       ->where('event', 'failed_login')
                                       ->count(),
        ];

        // Logs por dia
        $dailyLogs = AuditLog::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top usuários
        $topUsers = AuditLog::where('created_at', '>=', $startDate)
            ->select('user_id', DB::raw('count(*) as total'))
            ->with(['user:id,name,email'])
            ->groupBy('user_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Top ações
        $topActions = AuditLog::where('created_at', '>=', $startDate)
            ->select('event', DB::raw('count(*) as total'))
            ->groupBy('event')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Top modelos
        $topModels = AuditLog::where('created_at', '>=', $startDate)
            ->select('auditable_type', DB::raw('count(*) as total'))
            ->groupBy('auditable_type')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // IPs suspeitos (muitas tentativas de login falhadas)
        $suspiciousIps = AuditLog::where('created_at', '>=', $startDate)
            ->where('event', 'failed_login')
            ->select('ip_address', DB::raw('count(*) as attempts'))
            ->groupBy('ip_address')
            ->having('attempts', '>=', 5)
            ->orderBy('attempts', 'desc')
            ->get();

        return view('audit-logs.dashboard', compact(
            'stats', 'dailyLogs', 'topUsers', 'topActions', 
            'topModels', 'suspiciousIps', 'period'
        ));
    }

    /**
     * Export audit logs to various formats.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        $query = AuditLog::with(['user'])
            ->orderBy('created_at', 'desc');

        // Aplicar mesmos filtros do index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->get();

        $filename = 'audit_logs_' . now()->format('Y-m-d_H-i-s');

        switch ($format) {
            case 'csv':
                return $this->exportToCsv($logs, $filename);
            case 'excel':
                return $this->exportToExcel($logs, $filename);
            case 'pdf':
                return $this->exportToPdf($logs, $filename);
            default:
                return redirect()->back()->with('error', 'Formato de exportação inválido.');
        }
    }

    /**
     * Clean old audit logs.
     */
    public function cleanup(Request $request)
    {
        $this->authorize('cleanup', AuditLog::class);

        $request->validate([
            'days' => 'required|integer|min:30|max:365'
        ]);

        $days = $request->days;
        $cutoffDate = now()->subDays($days);
        
        $deletedCount = AuditLog::where('created_at', '<', $cutoffDate)->delete();

        // Log da limpeza
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'cleanup',
            'auditable_type' => 'AuditLog',
            'description' => "Limpeza de logs: {$deletedCount} registros removidos (anteriores a {$cutoffDate->format('d/m/Y')})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 
            "Limpeza concluída: {$deletedCount} logs removidos.");
    }

    /**
     * Get audit logs for API.
     */
    public function api(Request $request)
    {
        $query = AuditLog::with(['user:id,name,email'])
            ->orderBy('created_at', 'desc');

        // Filtros via API
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        if ($request->filled('limit')) {
            $query->limit($request->limit);
        }

        $logs = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $logs,
            'message' => 'Logs de auditoria recuperados com sucesso.'
        ]);
    }

    /**
     * Get system activity summary.
     */
    public function activity(Request $request)
    {
        $hours = $request->get('hours', 24);
        $startTime = now()->subHours($hours);

        $activity = [
            'period' => "{$hours} horas",
            'total_actions' => AuditLog::where('created_at', '>=', $startTime)->count(),
            'unique_users' => AuditLog::where('created_at', '>=', $startTime)
                                   ->distinct('user_id')
                                   ->count('user_id'),
            'login_attempts' => AuditLog::where('created_at', '>=', $startTime)
                                      ->whereIn('event', ['login', 'failed_login'])
                                      ->count(),
            'failed_logins' => AuditLog::where('created_at', '>=', $startTime)
                                     ->where('event', 'failed_login')
                                     ->count(),
            'recent_actions' => AuditLog::where('created_at', '>=', $startTime)
                                      ->with(['user:id,name'])
                                      ->orderBy('created_at', 'desc')
                                      ->limit(10)
                                      ->get(),
            'top_actions' => AuditLog::where('created_at', '>=', $startTime)
                                   ->select('event', DB::raw('count(*) as total'))
                                   ->groupBy('event')
                                   ->orderBy('total', 'desc')
                                   ->limit(5)
                                   ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $activity,
            'message' => 'Atividade do sistema recuperada com sucesso.'
        ]);
    }

    /**
     * Export to CSV format.
     */
    private function exportToCsv($logs, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalhos
            fputcsv($file, [
                'ID', 'Usuário', 'Email', 'Evento', 'Modelo', 'ID do Modelo',
                'Descrição', 'IP', 'User Agent', 'Data/Hora'
            ], ';');

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user ? $log->user->name : 'N/A',
                    $log->user ? $log->user->email : 'N/A',
                    $log->event,
                    $log->auditable_type,
                    $log->auditable_id,
                    $log->description,
                    $log->ip_address,
                    $log->user_agent,
                    $log->created_at->format('d/m/Y H:i:s')
                ], ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export to Excel format.
     */
    private function exportToExcel($logs, $filename)
    {
        // Implementar exportação para Excel usando uma biblioteca como PhpSpreadsheet
        // Por simplicidade, retornando CSV por enquanto
        return $this->exportToCsv($logs, $filename);
    }

    /**
     * Export to PDF format.
     */
    private function exportToPdf($logs, $filename)
    {
        // Implementar exportação para PDF usando uma biblioteca como DomPDF
        // Por simplicidade, retornando erro por enquanto
        return redirect()->back()->with('error', 'Exportação PDF não implementada ainda.');
    }
}