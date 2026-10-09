<!DOCTYPE html>
<html lang="km" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rule Vector Search Engine | ប្រព័ន្ធស្វែងរកវិធានគតិយុត្តិឆ្លាតវៃ (Gemini)</title>
    
    <!-- Google Fonts: Kantumruy Pro & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Kantumruy Pro"', 'Inter', 'sans-serif'],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            500: '#4f46e5',
                            600: '#4338ca',
                            700: '#3730a3',
                            800: '#312e81',
                            900: '#1e1b4b',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Kantumruy Pro', 'Inter', sans-serif;
        }
    </style>
</head>
<body class="min-h-full flex flex-col text-slate-800 antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Top Navigation Header -->
    <header class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('rules.index') }}" class="flex items-center space-x-3 group">
                <!-- <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-sky-500 flex items-center justify-center text-white shadow-md shadow-indigo-200 group-hover:scale-105 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div> -->  
                    
                    <div>
                        <h1 class="text-base sm:text-lg font-bold tracking-tight text-slate-900 flex items-center gap-2">
                            <span>ប្រព័ន្ធស្វែងរកវិធានគតិយុត្តិ</span>
                            {{-- <span class="text-[11px] px-2 py-0.5 rounded-full font-mono bg-sky-50 text-sky-700 border border-sky-200 hidden sm:inline-block">Gemini 768d + RRF</span> --}}
                        </h1>
                        {{-- <p class="text-xs text-slate-500">គណៈវិស្វករកម្ពុជា (Board of Engineers of Cambodia)</p> --}}
                    </div>
                </a>
            </div>

            <div class="flex items-center space-x-2 sm:space-x-3">
                <!-- Add New Rule Button -->
                <button 
                    type="button" 
                    onclick="document.getElementById('entry-modal').showModal()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-200 hover:shadow transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>បញ្ចូលគតិយុត្តិ</span>
                </button>

                <!-- <button 
                    type="button" 
                    onclick="document.getElementById('tech-modal').showModal()" 
                    class="text-xs font-medium text-slate-600 hover:text-indigo-600 bg-slate-100 hover:bg-indigo-50 px-3 py-2 rounded-xl border border-slate-200 transition hidden sm:inline-flex"
                >
                    ព័ត៌មានបច្ចេកទេស
                </button> -->
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">

        <!-- Flash Success Notification -->
        @if(session('success'))
            <div class="max-w-4xl mx-auto mb-6 bg-emerald-50 border border-emerald-200 rounded-2xl p-4 sm:p-5 flex items-start justify-between shadow-sm animate-fade-in">
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-emerald-900">ជោគជ័យ!</h3>
                        <p class="text-xs sm:text-sm text-emerald-700 mt-0.5 leading-relaxed">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 ml-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        @endif

        <!-- Validation Errors Notification -->
        @if($errors->any())
            <div class="max-w-4xl mx-auto mb-6 bg-rose-50 border border-rose-200 rounded-2xl p-4 flex items-start space-x-3 shadow-sm">
                <div class="w-8 h-8 rounded-full bg-rose-500 text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-rose-900">សូមពិនិត្យព័ត៌មានដែលបានបញ្ចូល៖</h3>
                    <ul class="text-xs text-rose-700 list-disc list-inside mt-1 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Hero Title -->
        <div class="text-center max-w-3xl mx-auto mb-8">
            <!-- <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-800 mb-3">
                <svg class="w-3.5 h-3.5 text-sky-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                </svg>
                Powered by Google Gemini (text-embedding-004) + MySQL Full-Text Search
            </div> -->
            {{-- <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-snug">
                ស្វែងរកវិធានបទបញ្ញត្តិ និងច្បាប់តាមន័យវិទ្យា
            </h2> --}}
            <!-- <p class="mt-2 text-sm sm:text-base text-slate-600 leading-relaxed">
                បច្ចេកវិទ្យាស្វែងរកកម្រិតខ្ពស់សម្រាប់អត្ថបទច្បាប់ខ្មែរ ដោយបញ្ចូល <span class="font-semibold text-indigo-600">Keyword FTS</span> និង <span class="font-semibold text-sky-600">Gemini 768-Dim Vectors</span> តាមរយៈក្បួន Reciprocal Rank Fusion (k=60)។
            </p> -->
        </div>

        <!-- Search Form Card -->
        <div class="max-w-4xl mx-auto mb-8">
            <form action="{{ route('rules.index') }}" method="GET" id="search-form" class="relative">
                <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-200 p-2 sm:p-3 transition-all focus-within:border-indigo-500 focus-within:ring-4 focus-within:ring-indigo-100">
                    <div class="flex items-center relative">
                        <div class="pl-3 sm:pl-4 text-slate-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 1114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="query" 
                            id="search-input"
                            value="{{ $query }}" 
                            placeholder="ឧទាហរណ៍៖ លក្ខខណ្ឌចុះបញ្ជីវិស្វករអាជីព ឬ តើត្រូវបង់ប្រាក់ប៉ុន្មានរាល់ឆ្នាំ?..." 
                            autocomplete="off"
                            class="w-full px-3 sm:px-4 py-3 sm:py-3.5 text-base sm:text-lg text-slate-900 placeholder:text-slate-400 bg-transparent border-0 focus:outline-none focus:ring-0"
                        />
                        <!-- Live Search Loading Spinner -->
                        <div id="live-search-spinner" class="hidden pr-2 text-indigo-600 animate-spin shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </div>
                        <!-- Clear button -->
                        <button 
                            type="button" 
                            id="clear-search-btn"
                            onclick="clearLiveSearch()"
                            class="{{ empty($query) ? 'hidden' : '' }} p-2 text-slate-400 hover:text-slate-600 transition shrink-0" 
                            title="សម្អាតសំណួរ"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        <button 
                            type="submit" 
                            class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-medium px-5 sm:px-7 py-3 sm:py-3.5 rounded-xl shadow-md hover:shadow-lg transition ml-2 whitespace-nowrap"
                        >
                            <span>ស្វែងរក</span>
                            <svg class="w-4 h-4 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>

        </div>

        <!-- Search Results Section -->
        <div id="search-results-section" class="{{ empty($query) ? 'hidden' : '' }}">
            <div class="max-w-4xl mx-auto mb-6 flex flex-wrap items-center justify-between gap-4 bg-white/60 backdrop-blur-sm p-4 rounded-xl border border-slate-200">
                <div class="flex items-center space-x-3 text-sm text-slate-700">
                    <div>
                        លទ្ធផលស្វែងរកសម្រាប់៖ <span id="results-query-label" class="font-bold text-slate-900">«{{ $query }}»</span>
                    </div>
                    <span class="text-slate-300">|</span>
                    <div class="text-slate-500 text-xs">
                        រកឃើញ <span id="results-count-badge" class="font-semibold text-slate-900">{{ $results->count() }}</span> មាត្រា
                    </div>
                </div>

                <div class="flex items-center space-x-4 text-xs font-mono text-slate-500">
                    <div class="flex items-center gap-1 bg-slate-100 px-2.5 py-1 rounded-md">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>ល្បឿន: <strong id="results-time-badge">{{ $executionTimeMs }} ms</strong></span>
                    </div>
                    <div class="flex items-center gap-1 bg-slate-100 px-2.5 py-1 rounded-md">
                        <span>ទិន្នន័យក្នុងប្រព័ន្ធ: <strong>{{ $totalRules }} មាត្រា</strong></span>
                    </div>
                </div>
            </div>

            <!-- Results List -->
            <div id="results-cards-list" class="max-w-4xl mx-auto space-y-5">
                @forelse($results as $index => $item)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition p-5 sm:p-6 relative overflow-hidden group">
                        
                        <!-- Top Header with Badges -->
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 mb-3 border-b border-slate-100">
                            <div class="flex items-center flex-wrap gap-2">
                                <!-- Rank Counter -->
                                <span class="w-7 h-7 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center font-mono">
                                    #{{ $index + 1 }}
                                </span>

                                <!-- Document Type Badge -->
                                <span class="text-xs px-2.5 py-1 rounded-md font-medium 
                                    @if($item->document_type === 'ព្រះរាជក្រឹត្យ') bg-purple-50 text-purple-700 border border-purple-200
                                    @elseif($item->document_type === 'អនុក្រឹត្យ') bg-blue-50 text-blue-700 border border-blue-200
                                    @elseif($item->document_type === 'ប្រកាស') bg-teal-50 text-teal-700 border border-teal-200
                                    @else bg-slate-100 text-slate-700 border border-slate-200 @endif
                                ">
                                    {{ $item->document_type }}
                                </span>

                                <!-- Article Number Badge -->
                                @if(!empty($item->article_no))
                                    <span class="text-xs px-2.5 py-1 rounded-md font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        {{ $item->article_no }}
                                    </span>
                                @endif


                            </div>

                            <!-- Scores breakdown container -->
                            <div class="flex items-center space-x-2 text-xs font-mono">
                                <div class="bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-lg text-slate-700" title="Reciprocal Rank Fusion Score (k=60)">
                                    <span class="text-slate-400">RRF:</span> <strong class="text-indigo-600 font-bold">{{ number_format($item->rrf_score, 5) }}</strong>
                                </div>
                                @if($item->cosine_similarity !== null)
                                    <div class="bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-lg text-slate-700" title="Cosine Similarity between query and rule embedding">
                                        <span class="text-slate-400">Cosine:</span> <strong class="text-sky-600 font-bold">{{ number_format($item->cosine_similarity * 100, 1) }}%</strong>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Document Title -->
                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition mb-2">
                            {{ $item->document_title }}
                        </h3>

                        <!-- Rule Body / Content -->
                        <div class="text-slate-700 text-sm sm:text-base leading-relaxed bg-slate-50/70 p-4 rounded-xl border border-slate-100">
                            <p id="rule-content-{{ $item->id }}">
                                {{ $item->content_chunk }}
                            </p>
                        </div>

                        <!-- Retrieval Ranks Footer -->
                        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-500 gap-2">
                            <div class="flex items-center space-x-3">
                                <span class="inline-flex items-center gap-1 font-mono">
                                    <span class="w-2 h-2 rounded-full {{ $item->keyword_rank ? 'bg-amber-500' : 'bg-slate-300' }}"></span>
                                    <span>Keyword Rank: <strong>{{ $item->keyword_rank ? '#' . $item->keyword_rank : 'N/A' }}</strong></span>
                                </span>
                                <span class="text-slate-300">•</span>
                                <span class="inline-flex items-center gap-1 font-mono">
                                    <span class="w-2 h-2 rounded-full {{ $item->vector_rank ? 'bg-sky-500' : 'bg-slate-300' }}"></span>
                                    <span>Vector Rank: <strong>{{ $item->vector_rank ? '#' . $item->vector_rank : 'N/A' }}</strong></span>
                                </span>
                            </div>

                            <button 
                                type="button" 
                                onclick="copyText('{{ addslashes($item->content_chunk) }}', this)" 
                                class="text-indigo-600 hover:text-indigo-800 font-medium inline-flex items-center gap-1 transition"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                </svg>
                                <span>ចម្លងអត្ថបទ</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-slate-300 p-8">
                        <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 mb-1">ពុំរកឃើញវិធានគតិយុត្តិដែលត្រូវគ្នានោះទេ</h4>
                        <p class="text-sm text-slate-500 max-w-md mx-auto mb-6">
                            សូមសាកល្បងស្វែងរកដោយប្រើពាក្យគន្លឹះផ្សេងទៀត ឬចុចលើប៊ូតុង «បញ្ចូលវិធានថ្មី» ដើម្បីបន្ថែមវិធានផ្ទាល់ខ្លួនរបស់អ្នក។
                        </p>
                        <button type="button" onclick="document.getElementById('entry-modal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-medium hover:bg-indigo-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span>បញ្ចូលវិធានថ្មីឥឡូវនេះ</span>
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Default Welcome Section: Features & All Indexed Rules -->
        <div id="welcome-section" class="max-w-4xl mx-auto space-y-8 {{ !empty($query) ? 'hidden' : '' }}">

                <!-- Current Rules in Database Table List -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">វិធានគតិយុត្តិដែលមានក្នុងប្រព័ន្ធបច្ចុប្បន្ន</h3>
                           {{-- <p class="text-xs text-slate-500">មានសរុប {{ $totalRules }} មាត្រា/ប្រការ ដែលបាន Indexed ជាមួយ Gemini Vector</p> --}}
                        </div>
                        {{-- <span class="text-xs font-mono bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md">
                            MySQL Table: `rules`
                        </span> --}}
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($recentRules as $rule)
                            <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 group hover:bg-slate-50/50 px-2 rounded-lg transition">
                                <div class="flex items-start sm:items-center space-x-2">
                                    <span class="text-xs px-2 py-0.5 rounded font-medium shrink-0
                                        @if($rule->document_type === 'ព្រះរាជក្រឹត្យ') bg-purple-50 text-purple-700 border border-purple-200
                                        @elseif($rule->document_type === 'អនុក្រឹត្យ') bg-blue-50 text-blue-700 border border-blue-200
                                        @elseif($rule->document_type === 'ប្រកាស') bg-teal-50 text-teal-700 border border-teal-200
                                        @else bg-slate-100 text-slate-700 border border-slate-200 @endif
                                    ">
                                        {{ $rule->document_type }}
                                    </span>
                                    @if($rule->article_no)
                                        <span class="text-xs font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 shrink-0">
                                            {{ $rule->article_no }}
                                        </span>
                                    @endif
                                    <span class="text-xs sm:text-sm font-medium text-slate-800 truncate max-w-md">
                                        {{ $rule->document_title }}
                                    </span>
                                </div>
                                <button 
                                    type="button" 
                                    onclick="runQuickSearch('{{ addslashes($rule->article_no ?: $rule->document_title) }}')"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium inline-flex items-center gap-1 self-end sm:self-auto shrink-0"
                                >
                                    <span>ស្វែងរក</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- NEW RULE ENTRY MODAL -->
    <dialog id="entry-modal" class="backdrop:bg-slate-900/60 p-0 rounded-2xl shadow-2xl max-w-2xl w-full border border-slate-200 text-slate-800">
        <div class="p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <!-- <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div> -->
                        <span>បញ្ចូលវិធាន ឬមាត្រាច្បាប់ថ្មី</span>
                    </h3>
                    <!-- <p class="text-xs text-slate-500 mt-0.5">ប្រព័ន្ធនឹងបង្កើត Google Gemini Vector (768 វិមាត្រ) សម្រាប់មាត្រានេះដោយស្វ័យប្រវត្តិ</p> -->
                </div>
                <button type="button" onclick="document.getElementById('entry-modal').close()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form action="{{ route('rules.store') }}" method="POST" id="rule-entry-form" class="py-4 space-y-4">
                @csrf

                <!-- Document Type & Article No -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="document_type" class="block text-xs font-semibold text-slate-700 mb-1">
                            ប្រភេទលិខិតបទដ្ឋាន <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            name="document_type" 
                            id="document_type" 
                            required
                            class="w-full text-sm rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white px-3 py-2 border"
                        >
                            <option value="ព្រះរាជក្រឹត្យ">ព្រះរាជក្រឹត្យ</option>
                            <option value="អនុក្រឹត្យ" selected>អនុក្រឹត្យ</option>
                            <option value="ប្រកាស">ប្រកាស</option>
                            <option value="សេចក្តីសម្រេច">សេចក្តីសម្រេច</option>
                            <option value="សារាចរ">សារាចរ</option>
                            <option value="បទបញ្ជាផ្ទៃក្នុង">បទបញ្ជាផ្ទៃក្នុង</option>
                        </select>
                    </div>

                    <div>
                        <label for="article_no" class="block text-xs font-semibold text-slate-700 mb-1">
                            លេខមាត្រា ឬប្រការ (បើមាន)
                        </label>
                        <input 
                            type="text" 
                            name="article_no" 
                            id="article_no" 
                            placeholder="ឧ. មាត្រា ៣០ ឬ ប្រការ ១២"
                            class="w-full text-sm rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                        />
                    </div>
                </div>

                <!-- Document Title -->
                <div>
                    <label for="document_title" class="block text-xs font-semibold text-slate-700 mb-1">
                        ចំណងជើងឯកសារច្បាប់ <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="document_title" 
                        id="document_title" 
                        required
                        placeholder="ឧ. អនុក្រឹត្យ ស្ដីពីការគ្រប់គ្រង និងការប្រកបវិជ្ជាជីវៈវិស្វកម្ម"
                        class="w-full text-sm rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                    />
                </div>

                <!-- Rule Body (Content) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="content_chunk" class="block text-xs font-semibold text-slate-700">
                            ខ្លឹមសារនៃវិធានច្បាប់ ឬមាត្រា <span class="text-rose-500">*</span>
                        </label>
                        <button 
                            type="button" 
                            onclick="fillSampleRule()" 
                            class="text-[11px] text-indigo-600 hover:text-indigo-800 font-medium inline-flex items-center gap-1"
                        >
                            <span>+ ប្រើគំរូទិន្នន័យតេស្ត</span>
                        </button>
                    </div>
                    <textarea 
                        name="content_chunk" 
                        id="content_chunk" 
                        rows="5" 
                        required
                        placeholder="សូមបញ្ចូលខ្លឹមសារច្បាប់ជាភាសាខ្មែរនៅទីនេះ..."
                        class="w-full text-sm rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3 border leading-relaxed"
                    ></textarea>
                </div>

                <!-- Submit Button -->
                <div class="pt-3 border-t border-slate-200 flex items-center justify-end space-x-3">
                    <button 
                        type="button" 
                        onclick="document.getElementById('entry-modal').close()" 
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition"
                    >
                        បោះបង់
                    </button>
                    <button 
                        type="submit" 
                        id="submit-rule-btn"
                        class="px-5 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-200 hover:shadow-lg transition inline-flex items-center gap-2"
                    >
                        <svg id="submit-spinner" class="w-4 h-4 hidden animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>រក្សាទុក & បង្កើត Gemini Vector</span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Technical Architecture Modal Dialog -->
    <dialog id="tech-modal" class="backdrop:bg-slate-900/60 p-0 rounded-2xl shadow-2xl max-w-2xl w-full border border-slate-200 text-slate-800">
        <div class="p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    ស្ថាបត្យកម្ម Google Gemini Hybrid Search & RRF
                </h3>
                <button type="button" onclick="document.getElementById('tech-modal').close()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="py-4 space-y-4 text-sm leading-relaxed text-slate-600">
                <div>
                    <h4 class="font-bold text-slate-900 mb-1">១. ម៉ូដែល Google Gemini (gemini-embedding-001)</h4>
                    <p class="text-xs text-slate-600">
                        ម៉ូដែលនេះបង្កើត Vector ទំហំ <strong>768 វិមាត្រ (768 dimensions)</strong> ដែលមានសមត្ថភាពស្វែងយល់ន័យវិទ្យាយ៉ាងស៊ីជម្រៅលើពាក្យខ្មែរ ដោយមិនអាស្រ័យតែលើពាក្យគន្លឹះឡើយ។
                    </p>
                </div>

                <div>
                    <h4 class="font-bold text-slate-900 mb-1">២. រូបមន្ត Reciprocal Rank Fusion (RRF k=60)</h4>
                    <div class="bg-slate-100 p-3 rounded-xl font-mono text-xs text-indigo-700">
                        RRF_Score(d) = Σ [ 1 / (60 + Rank_m(d)) ]
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        ដែល Rank_m(d) ជាចំណាត់ថ្នាក់របស់ឯកសារ d ក្នុងបញ្ជី Keyword FTS និងបញ្ជី Semantic Vector ដោយមាន k = 60 ជា Smoothing Constant។
                    </p>
                </div>

                <div>
                    <h4 class="font-bold text-slate-900 mb-1">៣. ការគណនា Cosine Similarity ក្នុង PHP សុទ្ធ</h4>
                    <div class="bg-slate-100 p-3 rounded-xl font-mono text-xs text-sky-700">
                        cos(θ) = (A · B) / (||A|| × ||B||)
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        ដំណើរការលើ PHP 8.4 ដោយផ្ទាល់ មិនទាមទារ Python server ឬ C extensions ឡើយ។
                    </p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end">
                <button type="button" onclick="document.getElementById('tech-modal').close()" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition">
                    យល់ព្រម
                </button>
            </div>
        </div>
    </dialog>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
            <div>
                © {{ date('Y') }} គណៈវិស្វករកម្ពុជា (Board of Engineers of Cambodia) — Gemini Vector Discovery Engine
            </div>
        </div>
    </footer>

    <!-- Client-side Scripts -->
    <script>
        let searchDebounceTimer = null;
        let currentAbortController = null;

        const searchInput = document.getElementById('search-input');
        const searchForm = document.getElementById('search-form');
        const spinner = document.getElementById('live-search-spinner');
        const clearBtn = document.getElementById('clear-search-btn');
        const resultsSection = document.getElementById('search-results-section');
        const welcomeSection = document.getElementById('welcome-section');
        const resultsCardsList = document.getElementById('results-cards-list');
        const resultsQueryLabel = document.getElementById('results-query-label');
        const resultsCountBadge = document.getElementById('results-count-badge');
        const resultsTimeBadge = document.getElementById('results-time-badge');

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function escapeJs(str) {
            if (!str) return '';
            return String(str).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\r/g, '');
        }

        function getDocumentTypeBadgeClass(docType) {
            switch (docType) {
                case 'ព្រះរាជក្រឹត្យ':
                    return 'bg-purple-50 text-purple-700 border border-purple-200';
                case 'អនុក្រឹត្យ':
                    return 'bg-blue-50 text-blue-700 border border-blue-200';
                case 'ប្រកាស':
                    return 'bg-teal-50 text-teal-700 border border-teal-200';
                default:
                    return 'bg-slate-100 text-slate-700 border border-slate-200';
            }
        }

        function renderLiveResults(results, query, executionTimeMs) {
            if (resultsQueryLabel) resultsQueryLabel.textContent = `«${query}»`;
            if (resultsCountBadge) resultsCountBadge.textContent = results ? results.length : 0;
            if (resultsTimeBadge) resultsTimeBadge.textContent = `${executionTimeMs} ms`;

            if (!results || results.length === 0) {
                resultsCardsList.innerHTML = `
                    <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-slate-300 p-8 animate-fade-in">
                        <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 mb-1">ពុំរកឃើញវិធានគតិយុត្តិដែលត្រូវគ្នានោះទេ</h4>
                        <p class="text-sm text-slate-500 max-w-md mx-auto mb-6">
                            សូមសាកល្បងស្វែងរកដោយប្រើពាក្យគន្លឹះផ្សេងទៀត ឬចុចលើប៊ូតុង «បញ្ចូលវិធានថ្មី» ដើម្បីបន្ថែមវិធានផ្ទាល់ខ្លួនរបស់អ្នក។
                        </p>
                        <button type="button" onclick="document.getElementById('entry-modal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-medium hover:bg-indigo-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span>បញ្ចូលវិធានថ្មីឥឡូវនេះ</span>
                        </button>
                    </div>
                `;
                return;
            }

            let html = '';
            results.forEach((item, index) => {
                const typeBadge = getDocumentTypeBadgeClass(item.document_type);
                const articleBadge = item.article_no 
                    ? `<span class="text-xs px-2.5 py-1 rounded-md font-semibold bg-amber-50 text-amber-800 border border-amber-200">${escapeHtml(item.article_no)}</span>` 
                    : '';
                const cosineBadge = (item.cosine_similarity !== null && item.cosine_similarity !== undefined)
                    ? `<div class="bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-lg text-slate-700" title="Cosine Similarity between query and rule embedding">
                           <span class="text-slate-400">Cosine:</span> <strong class="text-sky-600 font-bold">${(item.cosine_similarity * 100).toFixed(1)}%</strong>
                       </div>`
                    : '';
                const rrfFormatted = item.rrf_score !== undefined ? Number(item.rrf_score).toFixed(5) : '0.00000';
                const kwRankText = item.keyword_rank ? `#${item.keyword_rank}` : 'N/A';
                const kwDotColor = item.keyword_rank ? 'bg-amber-500' : 'bg-slate-300';
                const vecRankText = item.vector_rank ? `#${item.vector_rank}` : 'N/A';
                const vecDotColor = item.vector_rank ? 'bg-sky-500' : 'bg-slate-300';
                const escapedChunk = escapeJs(item.content_chunk);

                html += `
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition p-5 sm:p-6 relative overflow-hidden group">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 mb-3 border-b border-slate-100">
                            <div class="flex items-center flex-wrap gap-2">
                                <span class="w-7 h-7 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center font-mono">
                                    #${index + 1}
                                </span>
                                <span class="text-xs px-2.5 py-1 rounded-md font-medium ${typeBadge}">
                                    ${escapeHtml(item.document_type)}
                                </span>
                                ${articleBadge}
                            </div>
                            <div class="flex items-center space-x-2 text-xs font-mono">
                                <div class="bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-lg text-slate-700" title="Reciprocal Rank Fusion Score (k=60)">
                                    <span class="text-slate-400">RRF:</span> <strong class="text-indigo-600 font-bold">${rrfFormatted}</strong>
                                </div>
                                ${cosineBadge}
                            </div>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition mb-2">
                            ${escapeHtml(item.document_title)}
                        </h3>

                        <div class="text-slate-700 text-sm sm:text-base leading-relaxed bg-slate-50/70 p-4 rounded-xl border border-slate-100">
                            <p id="rule-content-${item.id}">
                                ${escapeHtml(item.content_chunk)}
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-500 gap-2">
                            <div class="flex items-center space-x-3">
                                <span class="inline-flex items-center gap-1 font-mono">
                                    <span class="w-2 h-2 rounded-full ${kwDotColor}"></span>
                                    <span>Keyword Rank: <strong>${kwRankText}</strong></span>
                                </span>
                                <span class="text-slate-300">•</span>
                                <span class="inline-flex items-center gap-1 font-mono">
                                    <span class="w-2 h-2 rounded-full ${vecDotColor}"></span>
                                    <span>Vector Rank: <strong>${vecRankText}</strong></span>
                                </span>
                            </div>

                            <button 
                                type="button" 
                                onclick="copyText('${escapedChunk}', this)" 
                                class="text-indigo-600 hover:text-indigo-800 font-medium inline-flex items-center gap-1 transition"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                </svg>
                                <span>ចម្លងអត្ថបទ</span>
                            </button>
                        </div>
                    </div>
                `;
            });

            resultsCardsList.innerHTML = html;
        }

        async function performLiveSearch(q, immediate = false) {
            if (searchDebounceTimer) {
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = null;
            }

            const trimmed = (q || '').trim();

            if (!trimmed) {
                if (currentAbortController) {
                    currentAbortController.abort();
                    currentAbortController = null;
                }
                if (spinner) spinner.classList.add('hidden');
                if (clearBtn) clearBtn.classList.add('hidden');
                if (resultsSection) resultsSection.classList.add('hidden');
                if (welcomeSection) welcomeSection.classList.remove('hidden');
                window.history.replaceState({}, '', window.location.pathname);
                return;
            }

            if (clearBtn) clearBtn.classList.remove('hidden');

            const execute = async () => {
                if (currentAbortController) {
                    currentAbortController.abort();
                }
                currentAbortController = new AbortController();
                if (spinner) spinner.classList.remove('hidden');

                try {
                    const url = `/?query=${encodeURIComponent(trimmed)}`;
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal: currentAbortController.signal
                    });

                    if (!response.ok) {
                        throw new Error('Search failed: ' + response.status);
                    }

                    const data = await response.json();
                    renderLiveResults(data.results, data.query, data.execution_time_ms);
                    if (welcomeSection) welcomeSection.classList.add('hidden');
                    if (resultsSection) resultsSection.classList.remove('hidden');
                    window.history.replaceState({}, '', url);
                } catch (err) {
                    if (err.name === 'AbortError') {
                        return; // Superseded by a subsequent keystroke
                    }
                    console.error('Live search error:', err);
                } finally {
                    if (spinner) spinner.classList.add('hidden');
                }
            };

            if (immediate) {
                execute();
            } else {
                searchDebounceTimer = setTimeout(execute, 300);
            }
        }

        function clearLiveSearch() {
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
            }
            performLiveSearch('', true);
        }

        function runQuickSearch(queryText) {
            if (searchInput) {
                searchInput.value = queryText;
            }
            performLiveSearch(queryText, true);
        }

        function copyText(text, btnElement) {
            navigator.clipboard.writeText(text).then(() => {
                const originalHtml = btnElement.innerHTML;
                btnElement.innerHTML = `
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-emerald-600 font-bold">បានចម្លង!</span>
                `;
                setTimeout(() => {
                    btnElement.innerHTML = originalHtml;
                }, 2000);
            });
        }

        function fillSampleRule() {
            document.getElementById('document_type').value = 'អនុក្រឹត្យ';
            document.getElementById('article_no').value = 'មាត្រា ៣២';
            document.getElementById('document_title').value = 'អនុក្រឹត្យ ស្ដីពីការបន្តការអភិវឌ្ឍវិជ្ជាជីវៈវិស្វកម្ម (CPD)';
            document.getElementById('content_chunk').value = 'វិស្វករអាជីពគ្រប់រូបដែលបានចុះបញ្ជីក្នុងគណៈវិស្វករកម្ពុជា ត្រូវបំពេញកាតព្វកិច្ចចូលរួមវគ្គបណ្តុះបណ្តាលបន្តវិជ្ជាជីវៈ (CPD) ឱ្យបានយ៉ាងតិច ៣០ ក្រេឌីត ក្នុងរយៈពេល ៣ឆ្នាំ ដើម្បីមានសិទ្ធិបន្តអាជ្ញាបណ្ណប្រកបវិជ្ជាជីវៈវិស្វកម្មស្របច្បាប់។ ការខកខានមិនបានបំពេញក្រេឌីត CPD នឹងនាំឱ្យមានការព្យួរអាជ្ញាបណ្ណជាបណ្តោះអាសន្ន។';
        }

        // Live input listeners
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                performLiveSearch(e.target.value);
            });
        }

        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                performLiveSearch(searchInput.value, true);
            });
        }

        // Add loading state on rule form submit
        const ruleForm = document.getElementById('rule-entry-form');
        if (ruleForm) {
            ruleForm.addEventListener('submit', function() {
                const btn = document.getElementById('submit-rule-btn');
                const spinnerEl = document.getElementById('submit-spinner');
                if (btn) {
                    btn.disabled = true;
                    btn.classList.add('opacity-75');
                }
                if (spinnerEl) {
                    spinnerEl.classList.remove('hidden');
                }
            });
        }
    </script>
</body>
</html>
