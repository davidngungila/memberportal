@extends('layouts.admin')

@section('breadcrumb', 'Members \u203A List')
@section('page_title', 'Members Directory')

@php
  $fmt = fn($n) => number_format((float)$n, 2) . ' TSh';
@endphp

@section('content')

<div x-data="membersList()" class="space-y-6">

  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1 max-w-2xl">
      <div class="relative flex-1">
        <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-primary-400"></i>
        <input type="text" 
               placeholder="Search by member code, name, phone, email..."
               class="form-input pl-9 py-2.5 text-sm"
               x-model="searchQuery"/>
        <button x-show="searchQuery" @click="clearSearch()" class="absolute right-3 top-1/2 -translate-y-1/2 text-primary-400 hover:text-primary-600">
          <i class="fa-solid fa-xmark text-xs"></i>
        </button>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <button type="button" @click="$dispatch('open-import-modal')"
             class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold transition-all shadow-sm hover:shadow-md active:scale-95 whitespace-nowrap">
        <i class="fa-solid fa-file-import text-[13px]"></i> Import Members
      </button>
      <a href="{{ route('admin.users.create') }}"
         class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all shadow-sm hover:shadow-md active:scale-95 whitespace-nowrap">
        <i class="fa-solid fa-user-plus text-[13px]"></i> New Member
      </a>
    </div>
  </div>

  <div class="glass p-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
      <div class="flex items-center gap-3">
        <span class="text-xs font-semibold text-primary-600 dark:text-primary-400">
          <i class="fa-solid fa-list-check mr-1.5"></i> <span x-text="filteredMembers.length"></span> Members Found
        </span>
        <span x-show="searchQuery" class="badge badge-blue text-[10px]">Search: <span x-text="searchQuery"></span></span>
      </div>
      <div class="flex items-center gap-3">
        <label class="flex items-center gap-2 text-xs text-primary-600 dark:text-primary-400">
          Per page:
          <select name="per_page" class="form-input py-1.5 px-2 w-20 text-xs" @change="changePerPage($el.value)">
            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
            <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
          </select>
        </label>
      </div>
    </div>

    <div class="overflow-x-auto -webkit-scrollbar [&::-webkit-scrollbar]:hidden rounded-2xl">
      <table class="data-table">
        <thead>
          <tr>
            <th class="w-12">#</th>
            <th class="cursor-pointer select-none" @click="sortBy('membercode')">
              Member Code
              <i class="fa-solid ml-1.5 text-[10px] {{ $memberService->getSortDirectionIcon($sortColumn, 'membercode', $sortDirection) }}"></i>
            </th>
            <th class="cursor-pointer select-none" @click="sortBy('name')">
              Full Name
              <i class="fa-solid ml-1.5 text-[10px] {{ $memberService->getSortDirectionIcon($sortColumn, 'name', $sortDirection) }}"></i>
            </th>
            <th>Phone</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <template x-for="(member, index) in filteredMembers" :key="member.id">
            <tr class="group">
              <td class="text-xs text-primary-400 dark:text-primary-500 font-mono" x-text="index + 1"></td>
              <td>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-primary-50 dark:bg-primary-900/40 font-mono text-xs font-bold text-primary-700 dark:text-primary-300">
                  <i class="fa-solid fa-id-card text-[10px] opacity-60"></i>
                  <span x-text="member.membercode"></span>
                </span>
              </td>
              <td>
                <div class="flex items-center gap-3">
                  <div x-show="member.photo" class="w-9 h-9 rounded-full object-cover flex-shrink-0 shadow-sm border-2 border-white dark:border-gray-700 overflow-hidden">
                    <img :src="'/storage/' + member.photo" :alt="member.name" class="w-full h-full object-cover"/>
                  </div>
                  <div x-show="!member.photo" class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 text-white flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-sm">
                    <span x-text="member.name.charAt(0).toUpperCase()"></span>
                  </div>
                  <div class="min-w-0">
                    <p class="text-sm font-semibold text-primary-900 dark:text-white truncate max-w-[200px]" x-text="member.name"></p>
                  </div>
                </div>
              </td>
              <td>
                <span class="text-xs font-mono text-primary-700 dark:text-primary-300" x-text="member.phone || '-'"></span>
              </td>
              <td>
                <span class="badge" :class="member.status_badge_class" x-text="member.status_badge_label"></span>
              </td>
              <td class="text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <a :href="'/admin/members/' + member.encrypted_id"
                     class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 text-sm transition-colors"
                     title="View Profile">
                    <i class="fa-solid fa-eye text-xs"></i>
                  </a>
                  <a :href="'/admin/members/' + member.encrypted_id + '#tab-loans'"
                     class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-orange-50 hover:bg-orange-100 dark:bg-orange-900/30 dark:hover:bg-orange-900/50 text-orange-700 dark:text-orange-300 text-sm transition-colors border border-orange-200 dark:border-orange-800/40"
                     title="Loans">
                    <i class="fa-solid fa-hand-holding-dollar text-xs"></i>
                  </a>
                  <a :href="'/admin/members/' + member.encrypted_id + '#tab-savings'"
                     class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-50 hover:bg-green-100 dark:bg-green-900/30 dark:hover:bg-green-900/50 text-green-700 dark:text-green-300 text-sm transition-colors border border-green-200 dark:border-green-800/40"
                     title="Savings">
                    <i class="fa-solid fa-piggy-bank text-xs"></i>
                  </a>
                  <a :href="'/admin/members/' + member.encrypted_id + '#tab-investments'"
                     class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/30 dark:hover:bg-purple-900/50 text-purple-700 dark:text-purple-300 text-sm transition-colors border border-purple-200 dark:border-purple-800/40"
                     title="Investments">
                    <i class="fa-solid fa-chart-line text-xs"></i>
                  </a>
                  <a :href="'/admin/members/' + member.encrypted_id + '#tab-shares'"
                     class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-sm transition-colors border border-blue-200 dark:border-blue-800/40"
                     title="Shares">
                    <i class="fa-solid fa-certificate text-xs"></i>
                  </a>
                  <a :href="'/admin/members/' + member.encrypted_id + '#tab-swf'"
                     class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-pink-50 hover:bg-pink-100 dark:bg-pink-900/30 dark:hover:bg-pink-900/50 text-pink-700 dark:text-pink-300 text-sm transition-colors border border-pink-200 dark:border-pink-800/40"
                     title="SWF">
                    <i class="fa-solid fa-hand-holding-heart text-xs"></i>
                  </a>
                </div>
              </td>
            </tr>
          </template>
          <tr x-show="filteredMembers.length === 0">
            <td colspan="6" class="text-center py-16 text-primary-500 dark:text-primary-400">
              <i class="fa-solid fa-user-slash text-4xl mb-4 block opacity-30"></i>
              <p class="text-sm font-semibold mb-1">No members found</p>
              <p class="text-xs">Try adjusting your search terms</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    @if(false && $members->hasPages())
      <div class="mt-6 pt-5 border-t border-primary-100 dark:border-primary-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <p class="text-xs text-primary-600 dark:text-primary-400">
          Showing <span class="font-bold text-primary-900 dark:text-white">{{ $members->firstItem() ?? 0 }}</span> to
          <span class="font-bold text-primary-900 dark:text-white">{{ $members->lastItem() ?? 0 }}</span> of
          <span class="font-bold text-primary-900 dark:text-white">{{ $members->total() }}</span> members
        </p>

        <nav class="flex items-center justify-center gap-1" role="navigation" aria-label="Pagination Navigation">
          @if($members->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg text-xs font-bold text-primary-300 dark:text-primary-700 bg-primary-50 dark:bg-primary-900/20 cursor-not-allowed">
              <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </span>
          @else
            <a href="{{ $members->appends(request()->query())->previousPageUrl() }}"
               class="px-3 py-1.5 rounded-lg text-xs font-bold text-primary-600 dark:text-primary-400 bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 transition-colors">
              <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </a>
          @endif

          @php
            $start = max($members->currentPage() - 2, 1);
            $end = min($start + 4, $members->lastPage());
            if ($end - $start < 4) {
                $start = max($end - 4, 1);
            }
          @endphp

          @for($i = $start; $i <= $end; $i++)
            @if($i == $members->currentPage())
              <span class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-white bg-primary-600 shadow-sm">
                {{ $i }}
              </span>
            @else
              <a href="{{ $members->appends(request()->query())->url($i) }}"
                 class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-primary-700 dark:text-primary-300 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 transition-colors">
                {{ $i }}
              </a>
            @endif
          @endfor

          @if($members->hasMorePages())
            <a href="{{ $members->appends(request()->query())->nextPageUrl() }}"
               class="px-3 py-1.5 rounded-lg text-xs font-bold text-primary-600 dark:text-primary-400 bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 transition-colors">
              <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </a>
          @else
            <span class="px-3 py-1.5 rounded-lg text-xs font-bold text-primary-300 dark:text-primary-700 bg-primary-50 dark:bg-primary-900/20 cursor-not-allowed">
              <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </span>
          @endif
        </nav>
      </div>
    @endif
  </div>
</div>

<!-- Import Modal -->
<div id="importModal" x-show="showModal" x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     @open-import-modal.window="open()"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     x-data="importModal()">
  <div class="bg-white dark:bg-dark-bg rounded-2xl shadow-2xl w-full max-w-lg">
    <div class="p-6 border-b border-gray-200 dark:border-dark-border">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Import Members from Excel</h3>
        <button type="button" @click="showModal = false"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
          <i class="fa-solid fa-xmark text-xl"></i>
        </button>
      </div>
    </div>
    
    <!-- Upload Form -->
    <form x-show="!importing" method="POST" action="{{ route('admin.members.import') }}" enctype="multipart/form-data" class="p-6 space-y-4" @submit.prevent="handleImport">
      @csrf
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Excel File</label>
        <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 text-center hover:border-primary-500 dark:hover:border-primary-400 transition-colors">
          <input type="file" name="file" accept=".xlsx,.xls,.csv" required x-ref="fileInput"
                 class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                 @change="$el.parentElement.querySelector('.file-name').textContent = $el.files[0].name; $el.parentElement.querySelector('.upload-text').classList.add('hidden'); $el.parentElement.querySelector('.file-name').classList.remove('hidden');">
          <div class="upload-text">
            <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-400 mb-2"></i>
            <p class="text-sm text-gray-600 dark:text-gray-400">Drag and drop your Excel file here, or click to browse</p>
            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">.xlsx, .xls, .csv (Max 10MB)</p>
          </div>
          <p class="file-name hidden text-sm font-medium text-primary-600 dark:text-primary-400"></p>
        </div>
      </div>
      <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/40 rounded-lg p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-blue-800 dark:text-blue-300 font-semibold">
            <i class="fa-solid fa-info-circle mr-1"></i> Required Excel Columns:
          </p>
          <a href="{{ route('admin.members.template') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-semibold flex items-center gap-1">
            <i class="fa-solid fa-download"></i> Download Template
          </a>
        </div>
        <p class="text-xs text-blue-700 dark:text-blue-400">member_number, full_name, gender, phone, email, status, registration_date, date_of_birth, national_id, occupation, employer, residential_address, member_type, marital_status, bank_name, bank_branch, account_name, account_number, bank_account_status, mobile_money_provider, mobile_money_number, emergency_contact_name, emergency_contact_phone, emergency_contact_relationship, registration_fee, notes</p>
      </div>
      <div class="flex items-center justify-end gap-3 pt-4">
        <button type="button" @click="showModal = false"
                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
          Cancel
        </button>
        <button type="submit"
                class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors flex items-center gap-2">
          <i class="fa-solid fa-file-import"></i> Import Members
        </button>
      </div>
    </form>
    
    <!-- Progress Display -->
    <div x-show="importing" class="p-6 space-y-4">
      <div class="text-center">
        <div class="w-16 h-16 border-4 border-primary-200 dark:border-primary-800 border-t-primary-600 rounded-full animate-spin mx-auto mb-4"></div>
        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2" x-text="status"></h4>
        <p class="text-sm text-gray-600 dark:text-gray-400" x-text="message"></p>
      </div>
      
      <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
        <div class="bg-primary-600 h-full transition-all duration-300" :style="`width: ${progress}%`"></div>
      </div>
      
      <div class="flex items-center justify-between text-sm">
        <span class="text-gray-600 dark:text-gray-400">
          <span x-text="imported"></span> / <span x-text="total"></span> imported
        </span>
        <span class="font-semibold text-primary-600 dark:text-primary-400" x-text="progress + '%'"></span>
      </div>
      
      <div x-show="errors.length > 0" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/40 rounded-lg p-4">
        <p class="text-xs text-red-800 dark:text-red-300 font-semibold mb-2">
          <i class="fa-solid fa-exclamation-triangle mr-1"></i> Errors (<span x-text="errors.length"></span>):
        </p>
        <div class="max-h-32 overflow-y-auto">
          <template x-for="error in errors" :key="error">
            <p class="text-xs text-red-700 dark:text-red-400" x-text="error"></p>
          </template>
        </div>
      </div>
      
      <div x-show="status === 'completed' || status === 'failed'" class="flex items-center justify-end gap-3 pt-4">
        <button type="button" @click="importing = false; showModal = false; window.location.reload();"
                class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium transition-colors">
          Close & Refresh
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  function membersList() {
    return {
      searchQuery: @json($searchQuery ?? ''),
      allMembers: @json($allMembers ?? []),
      get filteredMembers() {
        if (!this.searchQuery || this.searchQuery.trim() === '') {
          return this.allMembers;
        }
        
        const query = this.searchQuery.toLowerCase().trim();
        return this.allMembers.filter(member => {
          const memberNo = (member.membercode || '').toLowerCase();
          const name = (member.name || '').toLowerCase();
          const phone = (member.phone || '').toLowerCase();
          const email = (member.email || '').toLowerCase();
          
          return memberNo.includes(query) || 
                 name.includes(query) || 
                 phone.includes(query) || 
                 email.includes(query);
        });
      },
      clearSearch() {
        this.searchQuery = '';
      },
      sortBy(column) {
        const params = new URLSearchParams(window.location.search);
        if (params.get('sort') === column) {
          params.set('sort_direction', params.get('sort_direction') === 'asc' ? 'desc' : 'asc');
        } else {
          params.set('sort', column);
          params.set('sort_direction', 'asc');
        }
        window.location.href = window.location.pathname + '?' + params.toString();
      },
      changePerPage(value) {
        const params = new URLSearchParams(window.location.search);
        params.set('per_page', value);
        params.delete('page');
        window.location.href = window.location.pathname + '?' + params.toString();
      }
    };
  }

  function importModal() {
    return {
      showModal: false,
      importing: false,
      progress: 0,
      status: '',
      message: '',
      jobId: null,
      imported: 0,
      total: 0,
      errors: [],
      open() {
        this.showModal = true;
      },
      close() {
        if (!this.importing) {
          this.showModal = false;
        }
      },
      handleImport() {
        const formData = new FormData();
        const fileInput = this.$refs.fileInput;
        
        if (!fileInput.files[0]) {
          alert('Please select a file');
          return;
        }
        
        formData.append('file', fileInput.files[0]);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        this.importing = true;
        this.status = 'Uploading...';
        this.message = 'Please wait while we upload your file...';
        this.progress = 10;
        
        fetch('{{ route("admin.members.import") }}', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            this.jobId = data.job_id;
            this.status = 'Processing...';
            this.message = 'Importing members from Excel file...';
            this.progress = 20;
            this.pollProgress();
          } else {
            this.status = 'Failed';
            this.message = data.message;
            this.progress = 0;
          }
        })
        .catch(error => {
          this.status = 'Failed';
          this.message = 'Upload failed: ' + error.message;
          this.progress = 0;
        });
      },
      pollProgress() {
        const interval = setInterval(() => {
          fetch(`{{ route('admin.members.import-progress', ':jobId') }}`.replace(':jobId', this.jobId))
            .then(response => response.json())
            .then(data => {
              this.status = data.status;
              this.message = data.message;
              this.progress = data.progress;
              this.imported = data.imported;
              this.total = data.total;
              this.errors = data.errors || [];
              
              if (data.status === 'completed' || data.status === 'failed') {
                clearInterval(interval);
              }
            })
            .catch(error => {
              console.error('Progress check failed:', error);
            });
        }, 1000);
      }
    };
  }
</script>
@endpush
