<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Expense Tracker Dashboard
        </h2>

        <div class="flex gap-2">
            <a href="{{ route('expenses.index') }}" class="px-4 py-2 text-gray-800 rounded-md hover:text-indigo-500">
                Expenses
            </a>
            <a href="{{ route('categories.index') }}" class="px-4 py-2  text-gray-800 rounded-md hover:text-indigo-500">
                Categories
            </a>
        </div>
        </div>
    </x-slot>

    <div class="py-6">
        
        <div class="max-full mx-auto sm:px-6 lg:px-8 mb-6 space-y-6">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Month</label>
                        <input type="month" name="month" value="{{ $month }}"
                               class="mt-1 border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Search</label>
                        <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="e.g. rent, fuel..."
                               class="mt-1 border-gray-300 rounded-md shadow-sm w-64" />
                    </div>

                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Apply</button>
                </form>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 rounded-lg border">
                        <div class="text-sm text-gray-500">Period</div>
                        <div class="text-lg font-semibold">
                            {{ $start->format('M d, Y') }} - {{ $end->format('M d, Y') }}
                        </div>
                    </div>

                    <div class="p-4 rounded-lg border">
                        <div class="text-sm text-gray-500">Total Spent ({{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }})</div>

                        <div class="text-2xl font-bold">Tsh
                            {{ number_format($currentTotal, 2) }}
                        </div>
                        <div class="mt-2 text-sm">
                            @if($changePercent !== null)
                               @if($changeDirection === 'up')
                                   <span class="inline-flex items-center gap-1 text-green-600 font-semibold">
                                    ↑ {{ number_format(abs($changePercent), 1) }}% 
                                   </span>
                                   <span class="text-gray-500">vs last month</span>
                                @elseif($changeDirection === 'down')
                                   <span class="inline-flex items-center gap-1 text-red-800 font-semibold">
                                    ↓ {{ number_format(abs($changePercent), 1) }}% 
                                   </span>
                                   <span class="text-gray-500">vs last month</span>
                                @else
                                   <span class="inline-flex items-center gap-1 text-gray-700 font-semibold">
                                    - 0.0% 
                                   </span>
                                   <span class="text-gray-500">vs last month</span>
                                @endif
                            @else
                                <span class="text-gray-500">
                                 Last month total: {{ number_format($previousTotal, 2) }}
                                </span>
                            @endif
                        </div>                       
                    </div>

                   

                    <div class="p-4 rounded-lg border">
                        <div class="text-sm text-gray-500">Top Spending Category</div>
                        @if($topCategoryName)
                           <div class="text-lg font-semibold">{{ $topCategoryName }}</div>
                           <div class="text-2xl font-bold mt-1">Tsh {{ number_format($topCategoryTotal, 2) }}</div>
                           <div class="text-sm text-green-500 mt-1">
                            {{ $topCategoryShare}}% of this month 
                           </div>
                        @else
                           <div class="text-gray-500 mt-2">No expenses this month yet.</div>
                        @endif
                    </div>

                    <div class="p-4 rounded-lg border">
                        <div class="text-sm text-gray-500">Recent Entries</div>
                        <div class="text-lg font-semibold">
                            {{ $recentExpenses->count() }}
                        </div>
                    </div>
                </div>

                 <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6 mt-6">
                            <div class="bg-white shadow sm:rounded-lg p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-lg font-semibold">Monthly Budget</h3>
                                    <a href="{{ route('budget.edit') }}" class="px-3 py-1 border rounded text-sm">Set Budget</a>
                                </div>
                                @if(!$budgetEnabled)
                                <p class="text-gray-500">Budget tracking is off. Set a monthly budget to see progress.</p>
                                @else
                                <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                                    <div class="text-sm text-gray-600">
                                        Budget: <span class="font-semibold">Tsh {{ number_format($monthlyBudget, 2) }}</span>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Spent: <span class="font-semibold">Tsh {{ number_format($currentTotal, 2) }}</span>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Remaining: 
                                        <span class="font-semibold">Tsh
                                            {{ number_format($budgetRemaining, 2) }}
                                        </span>
                                    </div>
                                    <div class="text-sm font-semibold">
                                        {{ number_format($budgetUsedPercent, 1) }}%</span>
                                    </div>
                                </div>

                                @php 
                                $barClass = 'bg-green-600';
                                if ($budgetStatus === 'warning') $barClass = 'bg-yellow-500';
                                if ($budgetStatus === 'danger') $barClass = 'bg-orange-600';
                                if ($budgetStatus === 'over') $barClass = 'bg-red-600';
                                @endphp

                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div class="h-3 {{ $barClass }}" style="width: {{ $budgetProgress }}%;"></div>
                                </div>

                                @if($budgetStatus === 'over')
                                <div class="mt-2 text-sm text-red-600 font-semibold">
                                    You're over budget by {{ number_format(abs($budgetRemaining), 2) }}.
                                </div>
                                @elseif($budgetStatus === 'danger')
                                <div class="mt-2 text-sm text-orange-600 font-semibold">
                                    You've used 80%+ of your budget. Monitor spending closely.
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6 mt-6">
                        <div class="bg-white shadow sm:rounded-lg p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold">Smart Insights</h3>
                                <a href="{{ route('expenses.index') }}" class="text-sm px-3 py-1 border rounded">View Expenses</a>
                            </div>

                            @php 
                             $badge = function($type) {
                                return match($type) {
                                    'good' => 'bg-green-300 text-green-900',
                                    'risk' => 'bg-red-300 text-red-900',
                                    default => 'bg-gray-300 text-gray-900',
                                };
                             };
                            @endphp

                            @if(empty($insights))
                               <p class="text-gray-500">No insights yet. Add more expenses to unlock analytics.</p>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($insights as $insight)
                                        <div class="border rounded-r-lg p-4">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="font-semibold">{{ $insight['title'] }}</div>
                                                <span class="text-xs px-2 py-1 rounded {{ $badge($insight['type']) }}">
                                                    {{ strtoupper($insight['type']) }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600 mt2">{{ $insight['text'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold">Forecast</h3>
                            <div class="text-sm text-gray-500">
                                Based on average per day ({{ $daysPassed }}/{{ $daysInMonth }} days)
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 rounded-lg border">
                                <div class="text-sm text-gray-500">Daily Average</div>
                                <div class="text-xl font-bold">Tsh {{ number_format($dailyAvg, 2) }}</div>
                            </div>

                            <div class="p-4 rounded-lg border">
                                <div class="text-sm text-gray-500">Projected Month-End</div>
                                <div class="text-2xl font-bold">Tsh {{ number_format($forecastTotal, 2) }}</div>

                                @if(!empty($monthlyBudget) && $monthlyBudget > 0)
                                    @if($forecastOverBudget)
                                    <div class="mt-2 text-sm font-semibold text-red-600">
                                        Over budget by Tsh {{ number_format(abs($forecastRemaining), 2) }}
                                    </div>
                                    @else
                                    <div class="mt-2 text-sm font-semibold text-green-600">
                                        Under budget by Tsh {{ number_format($forecastRemaining, 2) }}
                                    </div>
                                @endif
                            @endif
                        </div>

                        <div class="p-4 rounded-lg border">
                            <div class="text-sm text-gray-500">Signal</div>
                            @if(!empty($monthlyBudget) && $monthlyBudget > 0)
                              @if($forecastOverBudget)
                              <div class="text-lg font-semibold text-red-600">Risk of overspending</div>
                            @else
                               <div class="text-lg font-semibold text-green-600 text-center">On track</div>
                            @endif
                            @else
                               <div class="text-lg font-semibold text-gray-700">Add a budget for signals</div>
                            @endif
                        </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-full mx-auto sm:px-6 lg:px-8 mb-6 space-y-4">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap3">

                        <div class="bg-white shadow sm:rounded-lg p-6 lg:col-span-2">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold">Daily Spend: This Month vs Last Month</h3>
                                <div class="text-sm text-gray-500">Day-by-day comparison</div>
                            </div>
                            <div id="monthCompareChart" style="height: 350px;"></div>
                        </div>

                        <div class="bg-white shadow sm:rounded-lg p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold">Categoty Breakdown</h3>
                                <div class="text-sm text-gray-500">This Month Summary</div>
                            </div>
                            <div id="categoryDonut" style="height: 350px;"></div>

                            @if(empty($donutSeries))
                              <div class="text-sm text-gray-500 mt-3">No expenses this month yet.</div>
                            @endif
                        </div>
                    </div>
                </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">By Category</h3>

                    @if($categoryTotals->isEmpty())
                        <p class="text-gray-500">No expenses in this month yet.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($categoryTotals as $row)
                                <div class="flex justify-between border-b pb-2">
                                    <div class="font-medium">
                                        {{ optional($row->category)->name ?? 'Unknown' }}
                                    </div>
                                    <div class="font-semibold">
                                        {{ number_format($row->total, 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent Expenses</h3>

                    @if($recentExpenses->isEmpty())
                       <p class="text-gray-500">No expenses yet. Create your first one.</p>
                    @else
                       <div class="space-y-3">
                        @foreach($recentExpenses as $expense)
                            <div class="border rounded-md p-3 flex justify-between">
                                <div>
                                    <div class="font-semibold">{{ $expense->title }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $expense->spent_at}} - {{ $expense->category?->name ?? 'No category' }}
                                    </div>
                                </div>
                                <div class="font-bold">
                                    {{ number_format($expense->amount, 2) }}
                                </div>
                            </div>
                        @endforeach
                       </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        const dailyLabels = @json($dailyLabels ?? []);
        const thisMonthDaily = @json($thisMonthDaily ?? []);
        const lastMonthDaily = @json($lastMonthDaily ?? []);
        const thisMonthLabel = @json($thisMonthLabel ?? 'This Month');
        const lastMonthLabel = @json($lastMonthLabel ?? 'Last Month');

        const el = document.querySelector("#monthCompareChart");
        if (el) {

        const options = {
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false }
            },
            series: [
                { name: thisMonthLabel, data: thisMonthDaily },
                { name: lastMonthLabel, data: lastMonthDaily }
            ],
            xaxis: {
                categories: dailyLabels,
                title: { text: 'Day of Month' }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            dataLabels: {
                enabled: false
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.30,
                    opacityTo: 0.05,
                    stops: [0, 100]
                }
            },
            markers: {
                size: 3
            },
            tooltip: {
                y: {
                    formatter: (val) => Number(val).toFixed(2)
                    }
                },
                yaxis: {
                    labels: {
                        formatter: (val) => Number(val).toFixed(0)
                    }
                },
                legend: { position: 'top' }
        };

        new ApexCharts(el, options).render();
    }
    </script>

    <script>
        const donutLabels = @json($donutLabels ?? []);
        const donutSeries = @json($donutSeries ?? []);
        const currentTotal = @json($currentTotal ?? 0);

        const donutEl = document.querySelector("#categoryDonut");
        if (donutEl && donutSeries.length) {
            const donutOptions = {
                chart: {
                    type: 'donut',
                    height: 350
                },
                labels: donutLabels,
                series: donutSeries,
                legend: {
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: true
                },
                tooltip: {
                    y: {
                        formatter: (val) => Number(val).toFixed(2)
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '18px'
                                },
                                value: {
                                    show: true,
                                    fontSize: '16px',
                                    formatter: function (val) {
                                        return Number(val).toFixed(2);
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '24px',
                                    formatter: function () {
                                        return Number(currentTotal).toFixed(2);
                                    }
                                }
                            }
                        }
                    }
                }
            };
            new ApexCharts(donutEl, donutOptions).render();
        }
    </script>
</x-app-layout>