<x-app-layout>
    <div class="space-y-8" x-data="dashboardData()">
        <!-- Filtros de Data -->
        <div class="bg-white rounded-2xl p-4 lg:p-6 shadow-lg mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <h2 class="text-xl font-bold text-dark">Dashboard - Sistema Integrado de Gestão Portuária</h2>
                
                <!-- Container dos filtros -->
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 items-stretch sm:items-center">
                    <!-- Filtros de Data -->
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 items-stretch sm:items-center">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <label class="text-sm font-medium text-gray-600 whitespace-nowrap">Data Início:</label>
                            <input type="date" x-model="startDate" 
                                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent w-full sm:w-auto">
                        </div>
                        
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <label class="text-sm font-medium text-gray-600 whitespace-nowrap">Data Fim:</label>
                            <input type="date" x-model="endDate" 
                                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent w-full sm:w-auto">
                        </div>
                    </div>
                    
                    <!-- Botões de ação -->
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <button @click="clearDateFilter()" 
                                class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors flex items-center justify-center">
                            <i class="fas fa-times mr-1"></i>
                            Limpar
                        </button>
                        
                        <button @click="refreshData()" 
                                class="px-4 py-2 bg-green-100 text-green-600 rounded-lg text-sm font-medium hover:bg-green-200 transition-colors flex items-center justify-center">
                            <i class="fas fa-sync-alt mr-1" :class="{'fa-spin': loading}"></i>
                            Atualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats - Cards dinâmicos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">
            <!-- Embarcações Ativas -->
            <div class="metric-card rounded-2xl p-4 lg:p-6 shadow-lg hover-scale card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm lg:text-base font-medium">Embarcações Ativas</p>
                        <p class="text-2xl lg:text-3xl font-bold text-dark mt-1" x-text="stats.embarcacoes_ativas">{{ $embarcacoes_ativas ?? 0 }}</p>
                        <p class="text-xs lg:text-sm mt-1 text-green-600">
                            <i class="fas fa-arrow-up"></i>
                            <span x-text="Math.abs(stats.tendencias?.embarcacoes || 0)"></span>% vs período anterior
                        </p>
                    </div>
                    <div class="w-12 h-12 lg:w-16 lg:h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-ship text-white text-lg lg:text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Alertas Ativos -->
            <div class="metric-card rounded-2xl p-4 lg:p-6 shadow-lg hover-scale card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm lg:text-base font-medium">Alertas Ativos</p>
                        <p class="text-2xl lg:text-3xl font-bold text-dark mt-1" x-text="stats.alertas_ativos">{{ $alertas_ativos ?? 0 }}</p>
                        <p class="text-xs lg:text-sm mt-1 text-red-600">
                            <i class="fas fa-arrow-up"></i>
                            <span x-text="Math.abs(stats.tendencias?.alertas || 0)"></span>% vs período anterior
                        </p>
                    </div>
                    <div class="w-12 h-12 lg:w-16 lg:h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white text-lg lg:text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Incidentes do Mês -->
            <div class="metric-card rounded-2xl p-4 lg:p-6 shadow-lg hover-scale card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm lg:text-base font-medium">Incidentes do Mês</p>
                        <p class="text-2xl lg:text-3xl font-bold text-dark mt-1" x-text="stats.incidentes_mes">{{ $incidentes_mes ?? 0 }}</p>
                        <p class="text-xs lg:text-sm mt-1 text-orange-600">
                            <i class="fas fa-arrow-up"></i>
                            <span x-text="Math.abs(stats.tendencias?.incidentes || 0)"></span>% vs período anterior
                        </p>
                    </div>
                    <div class="w-12 h-12 lg:w-16 lg:h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-white text-lg lg:text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Inspeções Realizadas -->
            <div class="metric-card rounded-2xl p-4 lg:p-6 shadow-lg hover-scale card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm lg:text-base font-medium">Inspeções Realizadas</p>
                        <p class="text-2xl lg:text-3xl font-bold text-dark mt-1" x-text="stats.inspecoes_realizadas">{{ $inspecoes_realizadas ?? 0 }}</p>
                        <p class="text-xs lg:text-sm mt-1 text-green-600">
                            <i class="fas fa-arrow-up"></i>
                            <span x-text="Math.abs(stats.tendencias?.inspecoes || 0)"></span>% vs período anterior
                        </p>
                    </div>
                    <div class="w-12 h-12 lg:w-16 lg:h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-clipboard-check text-white text-lg lg:text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Gráfico de Movimentação -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-dark">Movimentação Diária</h3>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Entradas/Saídas</span>
                    </div>
                </div>
                <div class="relative h-64">
                    <canvas id="movimentacaoChart"></canvas>
                </div>
            </div>

            <!-- Gráfico de Embarcações por Tipo -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-dark">Embarcações por Tipo</h3>
                </div>
                <div class="relative h-64">
                    <canvas id="embarcacoesTipoChart"></canvas>
                </div>
            </div>

            <!-- Gráfico de Incidentes por Gravidade -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-dark">Incidentes por Gravidade</h3>
                </div>
                <div class="relative h-64">
                    <canvas id="incidentesGravidadeChart"></canvas>
                </div>
            </div>

            <!-- Gráfico de Inspeções por Resultado -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-dark">Inspeções por Resultado</h3>
                </div>
                <div class="relative h-64">
                    <canvas id="inspecoesResultadoChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Atividades Recentes -->
        <div class="metric-card rounded-2xl p-4 lg:p-6 shadow-lg card-hover">
            <div class="flex items-center justify-between mb-4 lg:mb-6">
                <h3 class="text-lg lg:text-xl font-bold text-dark">Atividades Recentes</h3>
                <button @click="loadRecentActivities()" class="px-3 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs lg:text-sm hover:bg-gray-200 transition-colors">
                    <i class="fas fa-sync-alt mr-1" :class="{'fa-spin': loadingActivities}"></i>
                    Atualizar
                </button>
            </div>
            <div class="space-y-3" x-show="recentActivities.length > 0">
                <template x-for="activity in recentActivities" :key="activity.id">
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 bg-blue-100 text-blue-600">
                            <i class="fas fa-ship"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-dark" x-text="activity.titulo || activity.nome || 'Atividade'"></p>
                            <p class="text-sm text-gray-600" x-text="activity.descricao || activity.tipo || 'Sem descrição'"></p>
                            <p class="text-xs text-gray-500" x-text="activity.data || activity.created_at || 'Data não disponível'"></p>
                        </div>
                    </div>
                </template>
            </div>
            <div x-show="recentActivities.length === 0" class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-3xl mb-2"></i>
                <p>Nenhuma atividade recente encontrada</p>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function dashboardData() {
            return {
                loading: false,
                loadingStats: false,
                loadingActivities: false,
                startDate: '',
                endDate: '',
                stats: {
                    embarcacoes_ativas: {{ $embarcacoes_ativas ?? 0 }},
                    alertas_ativos: {{ $alertas_ativos ?? 0 }},
                    incidentes_mes: {{ $incidentes_mes ?? 0 }},
                    inspecoes_realizadas: {{ $inspecoes_realizadas ?? 0 }},
                    tendencias: {
                        embarcacoes: {{ $embarcacoes_ativas_tendencia ?? 0 }},
                        alertas: {{ $alertas_ativos_tendencia ?? 0 }},
                        incidentes: {{ $incidentes_mes_tendencia ?? 0 }},
                        inspecoes: {{ $inspecoes_realizadas_tendencia ?? 0 }}
                    }
                },
                recentActivities: @json($movimentacoes_recentes ?? []),
                charts: @json($charts ?? []),

                init() {
                    console.log('Dashboard inicializado');
                    console.log('Stats iniciais:', this.stats);
                    console.log('Activities:', this.recentActivities);
                    console.log('Charts data:', this.charts);
                    
                    // Debug: Verificar se os valores estão sendo passados corretamente
                    console.log('Embarcações ativas (PHP):', {{ $embarcacoes_ativas ?? 0 }});
                    console.log('Alertas ativos (PHP):', {{ $alertas_ativos ?? 0 }});
                    console.log('Incidentes mês (PHP):', {{ $incidentes_mes ?? 0 }});
                    console.log('Inspeções realizadas (PHP):', {{ $inspecoes_realizadas ?? 0 }});
                    
                    // Definir datas padrão (último mês)
                    const today = new Date();
                    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
                    
                    this.endDate = today.toISOString().split('T')[0];
                    this.startDate = lastMonth.toISOString().split('T')[0];
                    
                    // Configurar observadores de data
                    this.watchDates();
                    
                    // Inicializar gráficos
                    this.$nextTick(() => {
                        this.initCharts();
                    });
                    
                    // Forçar atualização dos dados se os valores iniciais são zero
                    if (this.stats.embarcacoes_ativas === 0 && this.stats.alertas_ativos === 0) {
                        console.log('Valores iniciais são zero, carregando dados via AJAX...');
                        setTimeout(() => {
                            this.refreshData();
                        }, 1000);
                    }
                },

                initCharts() {
                    // Gráfico de Movimentação
                    const movimentacaoCtx = document.getElementById('movimentacaoChart');
                    if (movimentacaoCtx && this.charts.movimentacao) {
                        movimentacaoCtx.chart = new Chart(movimentacaoCtx, {
                            type: 'line',
                            data: {
                                labels: this.charts.movimentacao.labels || [],
                                datasets: [{
                                    label: 'Movimentações',
                                    data: this.charts.movimentacao.data || [],
                                    borderColor: '#0086e1',
                                    backgroundColor: 'rgba(0, 134, 225, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            color: 'rgba(0, 0, 0, 0.1)'
                                        }
                                    },
                                    x: {
                                        grid: {
                                            display: false
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // Gráfico de Embarcações por Tipo
                    const embarcacoesTipoCtx = document.getElementById('embarcacoesTipoChart');
                    if (embarcacoesTipoCtx && this.charts.embarcacoes_tipo) {
                        embarcacoesTipoCtx.chart = new Chart(embarcacoesTipoCtx, {
                            type: 'doughnut',
                            data: {
                                labels: this.charts.embarcacoes_tipo.labels || [],
                                datasets: [{
                                    data: this.charts.embarcacoes_tipo.data || [],
                                    backgroundColor: [
                                        '#0086e1',
                                        '#01043d',
                                        '#10b981',
                                        '#f59e0b',
                                        '#ef4444'
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }
                        });
                    }

                    // Gráfico de Incidentes por Gravidade
                    const incidentesGravidadeCtx = document.getElementById('incidentesGravidadeChart');
                    if (incidentesGravidadeCtx && this.charts.incidentes_gravidade) {
                        incidentesGravidadeCtx.chart = new Chart(incidentesGravidadeCtx, {
                            type: 'bar',
                            data: {
                                labels: this.charts.incidentes_gravidade.labels || [],
                                datasets: [{
                                    label: 'Incidentes',
                                    data: this.charts.incidentes_gravidade.data || [],
                                    backgroundColor: [
                                        '#10b981', // Verde - Baixa
                                        '#f59e0b', // Amarelo - Média
                                        '#ef4444'  // Vermelho - Alta
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    }

                    // Gráfico de Inspeções por Resultado
                    const inspecoesResultadoCtx = document.getElementById('inspecoesResultadoChart');
                    if (inspecoesResultadoCtx && this.charts.inspecoes_resultado) {
                        inspecoesResultadoCtx.chart = new Chart(inspecoesResultadoCtx, {
                            type: 'pie',
                            data: {
                                labels: this.charts.inspecoes_resultado.labels || [],
                                datasets: [{
                                    data: this.charts.inspecoes_resultado.data || [],
                                    backgroundColor: [
                                        '#10b981', // Verde - Aprovada
                                        '#ef4444', // Vermelho - Reprovada
                                        '#f59e0b'  // Amarelo - Pendente
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }
                        });
                    }
                },

                // Funções para carregar dados
                async loadStats() {
                    this.loadingStats = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.startDate) params.append('start_date', this.startDate);
                        if (this.endDate) params.append('end_date', this.endDate);
                        
                        const response = await fetch(`/dashboard/stats?${params.toString()}`);
                        const data = await response.json();
                        
                        if (response.ok) {
                            this.stats = data;
                            console.log('Estatísticas atualizadas:', data);
                        } else {
                            console.error('Erro ao carregar estatísticas:', data);
                        }
                    } catch (error) {
                        console.error('Erro na requisição de estatísticas:', error);
                    } finally {
                        this.loadingStats = false;
                    }
                },

                async loadRecentActivities() {
                    this.loadingActivities = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.startDate) params.append('start_date', this.startDate);
                        if (this.endDate) params.append('end_date', this.endDate);
                        
                        const response = await fetch(`/dashboard/recent-activities?${params.toString()}`);
                        const data = await response.json();
                        
                        if (response.ok) {
                            this.recentActivities = data;
                            console.log('Atividades atualizadas:', data);
                        } else {
                            console.error('Erro ao carregar atividades:', data);
                        }
                    } catch (error) {
                        console.error('Erro na requisição de atividades:', error);
                    } finally {
                        this.loadingActivities = false;
                    }
                },

                async loadChartData() {
                    try {
                        const params = new URLSearchParams();
                        if (this.startDate) params.append('start_date', this.startDate);
                        if (this.endDate) params.append('end_date', this.endDate);
                        
                        const response = await fetch(`/dashboard/chart-data?${params.toString()}`);
                        const data = await response.json();
                        
                        if (response.ok) {
                            this.charts = {
                                movimentacao: data.movimentacoes,
                                embarcacoes_tipo: data.embarcacoes_tipo,
                                incidentes_gravidade: data.incidentes_gravidade,
                                inspecoes_resultado: data.inspecoes_resultado
                            };
                            
                            // Reinicializar gráficos com novos dados
                            this.$nextTick(() => {
                                this.destroyCharts();
                                this.initCharts();
                            });
                            
                            console.log('Dados dos gráficos atualizados:', this.charts);
                        } else {
                            console.error('Erro ao carregar dados dos gráficos:', data);
                        }
                    } catch (error) {
                        console.error('Erro na requisição de dados dos gráficos:', error);
                    }
                },

                destroyCharts() {
                    // Destruir gráficos existentes antes de criar novos
                    const chartIds = ['movimentacaoChart', 'embarcacoesTipoChart', 'incidentesGravidadeChart', 'inspecoesResultadoChart'];
                    chartIds.forEach(id => {
                        const canvas = document.getElementById(id);
                        if (canvas && canvas.chart) {
                            canvas.chart.destroy();
                        }
                    });
                },

                clearDateFilter() {
                    this.startDate = '';
                    this.endDate = '';
                    this.refreshData();
                },

                async refreshData() {
                    this.loading = true;
                    try {
                        await Promise.all([
                            this.loadStats(),
                            this.loadRecentActivities(),
                            this.loadChartData()
                        ]);
                    } catch (error) {
                        console.error('Erro ao atualizar dados:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                // Observadores para mudanças nas datas
                watchDates() {
                    this.$watch('startDate', () => {
                        if (this.startDate && this.endDate) {
                            this.refreshData();
                        }
                    });
                    
                    this.$watch('endDate', () => {
                        if (this.startDate && this.endDate) {
                            this.refreshData();
                        }
                    });
                }

                // ... rest of existing methods ...
            }
        }
    </script>

</x-app-layout>
