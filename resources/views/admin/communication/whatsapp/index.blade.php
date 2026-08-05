@extends('layouts.admin')

@section('breadcrumb', 'Communication › WhatsApp')
@section('page_title', 'WhatsApp Communication')

@section('content')
<div class="space-y-6">

  <!-- Status Messages -->
  @if(session('success'))
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
      <div class="flex items-start gap-3">
        <i class="fa-solid fa-circle-check text-green-600 dark:text-green-400 mt-0.5"></i>
        <p class="text-sm font-semibold text-green-800 dark:text-green-200">{{ session('success') }}</p>
      </div>
    </div>
  @endif

  @if(session('error'))
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
      <div class="flex items-start gap-3">
        <i class="fa-solid fa-circle-xmark text-red-600 dark:text-red-400 mt-0.5"></i>
        <p class="text-sm font-semibold text-red-800 dark:text-red-200">{{ session('error') }}</p>
      </div>
    </div>
  @endif

  <!-- Send Message Section -->
  @if($settings && $settings->session_api_key && $settings->is_active)
  <div class="bg-white dark:bg-dark-card rounded-xl shadow-sm border border-primary-100 dark:border-primary-800 p-6">
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-lg bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 flex items-center justify-center">
        <i class="fa-solid fa-paper-plane"></i>
      </div>
      <div>
        <h3 class="text-lg font-semibold text-primary-900 dark:text-white">Send Messages</h3>
        <p class="text-xs text-primary-500 dark:text-primary-400">Send single or bulk messages via WhatsApp or SMS</p>
      </div>
    </div>

    <!-- Channel Type Tabs -->
    <div x-data="{
            channelType: 'whatsapp',
            messageScope: 'single',
            msgType: 'text',
            uploadedUrl: '{{ session('uploaded_url', '') }}',
            groups: [],
            groupsLoading: false,
            selectedGroup: null,
            loadGroups() {
              this.groupsLoading = true;
              fetch('{{ route('admin.communication.whatsapp.groups') }}')
                .then(r => r.json())
                .then(data => {
                  this.groups = data.groups || [];
                  this.groupsLoading = false;
                })
                .catch(() => {
                  this.groupsLoading = false;
                });
            }
         }"
         class="space-y-6">
      <div class="flex gap-2 border-b border-primary-200 dark:border-primary-800">
        <button @click="channelType = 'whatsapp'"
                :class="channelType === 'whatsapp' ? 'border-b-2 border-green-500 text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'"
                class="px-4 py-2 text-sm font-medium transition-colors">
          <i class="fa-brands fa-whatsapp mr-2"></i>WhatsApp
        </button>
        <button @click="channelType = 'sms'"
                :class="channelType === 'sms' ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400'"
                class="px-4 py-2 text-sm font-medium transition-colors">
          <i class="fa-solid fa-comment-sms mr-2"></i>SMS
        </button>
      </div>

      <!-- WhatsApp Messages -->
      <div x-show="channelType === 'whatsapp'" x-transition>

        <!-- Single / Bulk / Groups Scope Tabs -->
        <div class="flex gap-2 border-b border-primary-200 dark:border-primary-800 mb-4">
          <button @click="messageScope = 'single'"
                  :class="messageScope === 'single' ? 'border-b-2 border-primary-500 text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400'"
                  class="px-4 py-2 text-sm font-medium transition-colors">
            <i class="fa-solid fa-user mr-1"></i>Single Message
          </button>
          <button @click="messageScope = 'bulk'"
                  :class="messageScope === 'bulk' ? 'border-b-2 border-primary-500 text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400'"
                  class="px-4 py-2 text-sm font-medium transition-colors">
            <i class="fa-solid fa-users mr-1"></i>Bulk Message
          </button>
          <button @click="messageScope = 'groups'; loadGroups()"
                  :class="messageScope === 'groups' ? 'border-b-2 border-primary-500 text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400'"
                  class="px-4 py-2 text-sm font-medium transition-colors">
            <i class="fa-solid fa-user-group mr-1"></i>Groups
          </button>
        </div>

        <!-- ========================================================== -->
        <!-- SINGLE SCOPE -->
        <!-- ========================================================== -->
        <div x-show="messageScope === 'single'" x-transition>

          <!-- Message Type Selector (8 types) -->
          <div class="mb-5">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              <i class="fa-solid fa-layer-group mr-1 text-primary-500"></i>Message Type
            </label>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2">
              @php
                $msgTypes = [
                    'text'     => ['icon' => 'fa-message',       'label' => 'Text',     'color' => 'emerald'],
                    'image'    => ['icon' => 'fa-image',         'label' => 'Image',    'color' => 'indigo'],
                    'video'    => ['icon' => 'fa-video',         'label' => 'Video',    'color' => 'rose'],
                    'document' => ['icon' => 'fa-file-pdf',      'label' => 'Document', 'color' => 'amber'],
                    'audio'    => ['icon' => 'fa-music',         'label' => 'Audio',    'color' => 'violet'],
                    'sticker'  => ['icon' => 'fa-face-smile',    'label' => 'Sticker',  'color' => 'pink'],
                    'contact'  => ['icon' => 'fa-address-book',  'label' => 'Contact',  'color' => 'cyan'],
                    'location' => ['icon' => 'fa-location-dot',  'label' => 'Location', 'color' => 'orange'],
                ];
              @endphp
              @foreach($msgTypes as $key => $mt)
                <button type="button" @click="msgType = '{{ $key }}'"
                        :class="msgType === '{{ $key }}'
                                 ? 'bg-{{ $mt['color'] }}-50 border-{{ $mt['color'] }}-400 text-{{ $mt['color'] }}-700 dark:bg-{{ $mt['color'] }}-900/30 dark:border-{{ $mt['color'] }}-600 dark:text-{{ $mt['color'] }}-300 shadow-sm'
                                 : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100 dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-300'"
                        class="group border rounded-xl p-3 text-center transition-all">
                  <i class="fa-solid {{ $mt['icon'] }} text-lg mb-1"></i>
                  <p class="text-xs font-semibold">{{ $mt['label'] }}</p>
                </button>
              @endforeach
            </div>
          </div>

          <!-- Single - Text Form -->
          <form x-show="msgType === 'text'" x-transition action="{{ route('admin.communication.whatsapp.send-single') }}" method="POST">
            @csrf
            @include('admin.communication.whatsapp.partials._single_phone_field')
            <div class="mb-4">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Message</label>
              <textarea name="message" rows="5" required placeholder="Enter message text..."
                        class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none"></textarea>
            </div>
            <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-green-500 to-green-600 text-white text-sm font-semibold transition-all shadow-lg shadow-green-500/20">
              <i class="fa-solid fa-message mr-2"></i>Send Text
            </button>
          </form>

          <!-- Single - Image Form -->
          <form x-show="msgType === 'image'" x-transition action="{{ route('admin.communication.whatsapp.send-image') }}" method="POST">
            @csrf
            @include('admin.communication.whatsapp.partials._single_phone_field')
            @include('admin.communication.whatsapp.partials._url_field', ['name'=>'image_url', 'label'=>'Image URL', 'placeholder'=>'https://.../photo.jpg', 'required'=>true, 'help'=>'Public image URL (JPG/PNG/WebP)'])
            <div class="mb-4">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Caption <span class="text-gray-400 font-normal">(optional)</span></label>
              <textarea name="caption" rows="3" placeholder="Add a caption to the image..."
                        class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none"></textarea>
            </div>
            <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-indigo-500 to-indigo-600 text-white text-sm font-semibold transition-all shadow-lg shadow-indigo-500/20">
              <i class="fa-solid fa-image mr-2"></i>Send Image
            </button>
          </form>

          <!-- Single - Video Form -->
          <form x-show="msgType === 'video'" x-transition action="{{ route('admin.communication.whatsapp.send-video') }}" method="POST">
            @csrf
            @include('admin.communication.whatsapp.partials._single_phone_field')
            @include('admin.communication.whatsapp.partials._url_field', ['name'=>'video_url', 'label'=>'Video URL', 'placeholder'=>'https://.../clip.mp4', 'required'=>true, 'help'=>'Public video URL (MP4 recommended)'])
            <div class="mb-4">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Caption <span class="text-gray-400 font-normal">(optional)</span></label>
              <textarea name="caption" rows="3" placeholder="Add a caption..."
                        class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none"></textarea>
            </div>
            <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-rose-500 to-rose-600 text-white text-sm font-semibold transition-all shadow-lg shadow-rose-500/20">
              <i class="fa-solid fa-video mr-2"></i>Send Video
            </button>
          </form>

          <!-- Single - Document Form -->
          <form x-show="msgType === 'document'" x-transition action="{{ route('admin.communication.whatsapp.send-document') }}" method="POST">
            @csrf
            @include('admin.communication.whatsapp.partials._single_phone_field')
            @include('admin.communication.whatsapp.partials._url_field', ['name'=>'document_url', 'label'=>'Document URL', 'placeholder'=>'https://.../report.pdf', 'required'=>true, 'help'=>'Public URL (PDF, DOCX, XLS, etc.)'])
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">File Name</label>
                <input type="text" name="file_name" required placeholder="report.pdf"
                       class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Caption <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="text" name="caption" placeholder="Document description..."
                       class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
              </div>
            </div>
            <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-amber-500 to-amber-600 text-white text-sm font-semibold transition-all shadow-lg shadow-amber-500/20">
              <i class="fa-solid fa-file-pdf mr-2"></i>Send Document
            </button>
          </form>

          <!-- Single - Audio Form -->
          <form x-show="msgType === 'audio'" x-transition action="{{ route('admin.communication.whatsapp.send-audio') }}" method="POST">
            @csrf
            @include('admin.communication.whatsapp.partials._single_phone_field')
            @include('admin.communication.whatsapp.partials._url_field', ['name'=>'audio_url', 'label'=>'Audio URL', 'placeholder'=>'https://.../voice.mp3', 'required'=>true, 'help'=>'Public audio URL (MP3, AAC, OGG, AMR)'])
            <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-violet-500 to-violet-600 text-white text-sm font-semibold transition-all shadow-lg shadow-violet-500/20">
              <i class="fa-solid fa-music mr-2"></i>Send Audio
            </button>
          </form>

          <!-- Single - Sticker Form -->
          <form x-show="msgType === 'sticker'" x-transition action="{{ route('admin.communication.whatsapp.send-sticker') }}" method="POST">
            @csrf
            @include('admin.communication.whatsapp.partials._single_phone_field')
            @include('admin.communication.whatsapp.partials._url_field', ['name'=>'sticker_url', 'label'=>'Sticker URL (.webp)', 'placeholder'=>'https://.../sticker.webp', 'required'=>true, 'help'=>'Must be .webp, max 100KB, 512×512px'])
            <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-pink-500 to-pink-600 text-white text-sm font-semibold transition-all shadow-lg shadow-pink-500/20">
              <i class="fa-solid fa-face-smile mr-2"></i>Send Sticker
            </button>
          </form>

          <!-- Single - Contact Form -->
          <form x-show="msgType === 'contact'" x-transition action="{{ route('admin.communication.whatsapp.send-contact') }}" method="POST">
            @csrf
            @include('admin.communication.whatsapp.partials._single_phone_field')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Contact Full Name</label>
                <input type="text" name="contact_name" required placeholder="John Doe"
                       class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Contact Phone Number</label>
                <input type="text" name="contact_phone" required placeholder="+255711000000"
                       class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
              </div>
            </div>
            <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-cyan-500 to-cyan-600 text-white text-sm font-semibold transition-all shadow-lg shadow-cyan-500/20">
              <i class="fa-solid fa-address-book mr-2"></i>Send Contact Card
            </button>
          </form>

          <!-- Single - Location Form -->
          <form x-show="msgType === 'location'" x-transition action="{{ route('admin.communication.whatsapp.send-location') }}" method="POST">
            @csrf
            @include('admin.communication.whatsapp.partials._single_phone_field')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Latitude</label>
                <input type="number" step="any" name="latitude" required placeholder="-6.7924"
                       class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Longitude</label>
                <input type="number" step="any" name="longitude" required placeholder="39.2083"
                       class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Location Name <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="text" name="name" placeholder="Head Office"
                       class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Address <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="text" name="address" placeholder="Samora Avenue, Dar es Salaam"
                       class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
              </div>
            </div>
            <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 text-white text-sm font-semibold transition-all shadow-lg shadow-orange-500/20">
              <i class="fa-solid fa-location-dot mr-2"></i>Send Location Pin
            </button>
          </form>

          <!-- Upload helper -->
          <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl">
            <div class="flex items-start gap-3">
              <div class="w-9 h-9 rounded-lg bg-sky-100 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-cloud-arrow-up"></i>
              </div>
              <div class="flex-1">
                <h5 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">No public URL? Upload a file first</h5>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Upload a file below to get a public URL to paste into any of the URL fields above.</p>
                <form action="{{ route('admin.communication.whatsapp.upload-media') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                  @csrf
                  <div class="flex items-center gap-3">
                    <input type="file" name="file" required class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-sky-100 file:text-sky-700 dark:file:bg-sky-900/40 dark:file:text-sky-300 hover:file:bg-sky-200 dark:hover:file:bg-sky-900/60 transition" />
                    <button type="submit" class="flex-shrink-0 px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold transition-all">
                      <i class="fa-solid fa-upload mr-1"></i>Upload
                    </button>
                  </div>
                  @if(session('uploaded_url'))
                    <div class="rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-3">
                      <p class="text-xs font-semibold text-green-800 dark:text-green-300 mb-1"><i class="fa-solid fa-check mr-1"></i>Upload successful – copy URL below:</p>
                      <div class="flex items-center gap-2">
                        <input x-model="uploadedUrl" type="text" readonly value="{{ session('uploaded_url') }}"
                               class="flex-1 px-3 py-1.5 text-xs font-mono rounded-lg border border-green-200 dark:border-green-800 bg-white dark:bg-green-900/30 text-green-900 dark:text-green-100">
                        <button type="button"
                                @click="navigator.clipboard.writeText(uploadedUrl).then(() => alert('Copied!'))"
                                class="px-3 py-1.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-xs font-semibold">
                          <i class="fa-solid fa-copy mr-1"></i>Copy
                        </button>
                      </div>
                    </div>
                  @endif
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- ========================================================== -->
        <!-- BULK SCOPE -->
        <!-- ========================================================== -->
        <div x-show="messageScope === 'bulk'" x-transition>

          <!-- Bulk Message Type Selector (3 types) -->
          <div class="mb-5">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              <i class="fa-solid fa-layer-group mr-1 text-primary-500"></i>Bulk Message Type
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
              <button type="button" @click="msgType = 'text'"
                      :class="msgType === 'text'
                               ? 'bg-emerald-50 border-emerald-400 text-emerald-700 dark:bg-emerald-900/30 dark:border-emerald-600 dark:text-emerald-300 shadow-sm'
                               : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100 dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-300'"
                      class="border rounded-xl p-4 text-left transition-all">
                <i class="fa-solid fa-message text-xl mb-1 block"></i>
                <p class="text-sm font-bold">Bulk Text</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Same text to many numbers</p>
              </button>
              <button type="button" @click="msgType = 'image'"
                      :class="msgType === 'image'
                               ? 'bg-indigo-50 border-indigo-400 text-indigo-700 dark:bg-indigo-900/30 dark:border-indigo-600 dark:text-indigo-300 shadow-sm'
                               : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100 dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-300'"
                      class="border rounded-xl p-4 text-left transition-all">
                <i class="fa-solid fa-image text-xl mb-1 block"></i>
                <p class="text-sm font-bold">Bulk Image</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Same image + caption to many</p>
              </button>
              <button type="button" @click="msgType = 'document'"
                      :class="msgType === 'document'
                               ? 'bg-amber-50 border-amber-400 text-amber-700 dark:bg-amber-900/30 dark:border-amber-600 dark:text-amber-300 shadow-sm'
                               : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100 dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-300'"
                      class="border rounded-xl p-4 text-left transition-all">
                <i class="fa-solid fa-file-pdf text-xl mb-1 block"></i>
                <p class="text-sm font-bold">Bulk Document</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Same PDF/doc to many numbers</p>
              </button>
            </div>
          </div>

          <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3 mb-4">
            <p class="text-xs text-amber-800 dark:text-amber-300"><i class="fa-solid fa-circle-info mr-1"></i> <b>Rate limit notice:</b> WasenderAPI enforces ~1 message per 5 seconds. Bulk sends respect this sequentially; large lists will take time.</p>
          </div>

          <!-- Bulk - Text Form -->
          <form x-show="msgType === 'text'" x-transition action="{{ route('admin.communication.whatsapp.send-bulk') }}" method="POST">
            @csrf
            @include('admin.communication.whatsapp.partials._bulk_phones_field')
            <div class="mb-4">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Message</label>
              <textarea name="message" rows="5" required placeholder="Enter message text to send to all recipients..."
                        class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none"></textarea>
            </div>
            <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-green-500 to-green-600 text-white text-sm font-semibold transition-all shadow-lg shadow-green-500/20">
              <i class="fa-solid fa-paper-plane mr-2"></i>Send Bulk Text
            </button>
          </form>

          <!-- Bulk - Image Form -->
          <form x-show="msgType === 'image'" x-transition action="{{ route('admin.communication.whatsapp.send-bulk-image') }}" method="POST">
            @csrf
            @include('admin.communication.whatsapp.partials._bulk_phones_field')
            @include('admin.communication.whatsapp.partials._url_field', ['name'=>'image_url', 'label'=>'Image URL', 'placeholder'=>'https://.../photo.jpg', 'required'=>true, 'help'=>'Public image URL'])
            <div class="mb-4">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Caption <span class="text-gray-400 font-normal">(optional)</span></label>
              <textarea name="caption" rows="3" placeholder="Shared caption for all recipients..."
                        class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none"></textarea>
            </div>
            <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-indigo-500 to-indigo-600 text-white text-sm font-semibold transition-all shadow-lg shadow-indigo-500/20">
              <i class="fa-solid fa-image mr-2"></i>Send Bulk Image
            </button>
          </form>

          <!-- Bulk - Document Form -->
          <form x-show="msgType === 'document'" x-transition action="{{ route('admin.communication.whatsapp.send-bulk-document') }}" method="POST">
            @csrf
            @include('admin.communication.whatsapp.partials._bulk_phones_field')
            @include('admin.communication.whatsapp.partials._url_field', ['name'=>'document_url', 'label'=>'Document URL', 'placeholder'=>'https://.../report.pdf', 'required'=>true, 'help'=>'Public document URL'])
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">File Name</label>
                <input type="text" name="file_name" required placeholder="report.pdf"
                       class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Caption <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="text" name="caption" placeholder="Description for this document..."
                       class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
              </div>
            </div>
            <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-amber-500 to-amber-600 text-white text-sm font-semibold transition-all shadow-lg shadow-amber-500/20">
              <i class="fa-solid fa-file-pdf mr-2"></i>Send Bulk Document
            </button>
          </form>
        </div>

        <!-- ========================================================== -->
        <!-- GROUPS SCOPE -->
        <!-- ========================================================== -->
        <div x-show="messageScope === 'groups'" x-transition>
          <div class="mb-5">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              <i class="fa-solid fa-user-group mr-1 text-primary-500"></i>Select a Group
            </label>
            
            <div x-show="groupsLoading" class="flex items-center justify-center py-8">
              <i class="fa-solid fa-spinner fa-spin text-2xl text-primary-500"></i>
              <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Loading groups...</span>
            </div>

            <div x-show="!groupsLoading && groups.length === 0" class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-lg p-6 text-center">
              <i class="fa-solid fa-user-group text-4xl text-gray-400 dark:text-gray-500 mb-3"></i>
              <p class="text-sm text-gray-600 dark:text-gray-400">No groups found. Make sure your WhatsApp session is connected and has groups.</p>
              <button type="button" @click="loadGroups()" class="mt-3 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold transition-all">
                <i class="fa-solid fa-refresh mr-2"></i>Reload Groups
              </button>
            </div>

            <div x-show="!groupsLoading && groups.length > 0" class="space-y-3">
              <template x-for="group in groups" :key="group.id || group.jid">
                <div @click="selectedGroup = group"
                     :class="selectedGroup && (selectedGroup.id === group.id || selectedGroup.jid === group.jid) ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600'"
                     class="border rounded-lg p-4 cursor-pointer transition-all">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                      <i class="fa-solid fa-user-group text-green-600 dark:text-green-400"></i>
                    </div>
                    <div class="flex-1">
                      <p class="text-sm font-semibold text-gray-900 dark:text-white" x-text="group.name || group.subject || 'Unknown Group'"></p>
                      <p class="text-xs text-gray-500 dark:text-gray-400" x-text="group.jid || group.id || ''"></p>
                    </div>
                    <i :class="selectedGroup && (selectedGroup.id === group.id || selectedGroup.jid === group.jid) ? 'fa-solid fa-circle-check text-green-600 dark:text-green-400' : 'fa-regular fa-circle text-gray-400'" class="text-xl"></i>
                  </div>
                </div>
              </template>
            </div>
          </div>

          <!-- Group Message Form -->
          <div x-show="selectedGroup" x-transition>
            <form action="{{ route('admin.communication.whatsapp.send-single') }}" method="POST">
              @csrf
              <input type="hidden" name="phone_number" :value="selectedGroup.jid || selectedGroup.id">
              
              <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
                <p class="text-sm font-semibold text-green-800 dark:text-green-200">
                  <i class="fa-solid fa-check-circle mr-2"></i>Selected Group:
                </p>
                <p class="text-sm text-green-700 dark:text-green-300 mt-1" x-text="selectedGroup.name || selectedGroup.subject || 'Unknown Group'"></p>
              </div>

              <!-- Message Type Selector -->
              <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                  <i class="fa-solid fa-layer-group mr-1 text-primary-500"></i>Message Type
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2">
                  @php
                    $msgTypes = [
                        'text'     => ['icon' => 'fa-message',       'label' => 'Text',     'color' => 'emerald'],
                        'image'    => ['icon' => 'fa-image',         'label' => 'Image',    'color' => 'indigo'],
                        'video'    => ['icon' => 'fa-video',         'label' => 'Video',    'color' => 'rose'],
                        'document' => ['icon' => 'fa-file-pdf',      'label' => 'Document', 'color' => 'amber'],
                        'audio'    => ['icon' => 'fa-music',         'label' => 'Audio',    'color' => 'violet'],
                        'sticker'  => ['icon' => 'fa-face-smile',    'label' => 'Sticker',  'color' => 'pink'],
                        'contact'  => ['icon' => 'fa-address-book',  'label' => 'Contact',  'color' => 'cyan'],
                        'location' => ['icon' => 'fa-location-dot',  'label' => 'Location', 'color' => 'orange'],
                    ];
                  @endphp
                  @foreach($msgTypes as $key => $mt)
                    <button type="button" @click="msgType = '{{ $key }}'"
                            :class="msgType === '{{ $key }}'
                                     ? 'bg-{{ $mt['color'] }}-50 border-{{ $mt['color'] }}-400 text-{{ $mt['color'] }}-700 dark:bg-{{ $mt['color'] }}-900/30 dark:border-{{ $mt['color'] }}-600 dark:text-{{ $mt['color'] }}-300 shadow-sm'
                                     : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100 dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-300'"
                            class="group border rounded-xl p-3 text-center transition-all">
                      <i class="fa-solid {{ $mt['icon'] }} text-lg mb-1"></i>
                      <p class="text-xs font-semibold">{{ $mt['label'] }}</p>
                    </button>
                  @endforeach
                </div>
              </div>

              <!-- Group - Text Form -->
              <div x-show="msgType === 'text'" x-transition>
                <div class="mb-4">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Message</label>
                  <textarea name="message" rows="5" required placeholder="Enter message text to send to the group..."
                            class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none"></textarea>
                </div>
                <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-green-500 to-green-600 text-white text-sm font-semibold transition-all shadow-lg shadow-green-500/20">
                  <i class="fa-solid fa-message mr-2"></i>Send to Group
                </button>
              </div>

              <!-- Group - Image Form -->
              <div x-show="msgType === 'image'" x-transition>
                @include('admin.communication.whatsapp.partials._url_field', ['name'=>'image_url', 'label'=>'Image URL', 'placeholder'=>'https://.../photo.jpg', 'required'=>true, 'help'=>'Public image URL'])
                <div class="mb-4">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Caption <span class="text-gray-400 font-normal">(optional)</span></label>
                  <textarea name="caption" rows="3" placeholder="Image caption..."
                            class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none"></textarea>
                </div>
                <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-indigo-500 to-indigo-600 text-white text-sm font-semibold transition-all shadow-lg shadow-indigo-500/20">
                  <i class="fa-solid fa-image mr-2"></i>Send Image to Group
                </button>
              </div>

              <!-- Group - Video Form -->
              <div x-show="msgType === 'video'" x-transition>
                @include('admin.communication.whatsapp.partials._url_field', ['name'=>'video_url', 'label'=>'Video URL', 'placeholder'=>'https://.../video.mp4', 'required'=>true, 'help'=>'Public video URL (MP4, 3GP)'])
                <div class="mb-4">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Caption <span class="text-gray-400 font-normal">(optional)</span></label>
                  <textarea name="caption" rows="3" placeholder="Video caption..."
                            class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none"></textarea>
                </div>
                <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-rose-500 to-rose-600 text-white text-sm font-semibold transition-all shadow-lg shadow-rose-500/20">
                  <i class="fa-solid fa-video mr-2"></i>Send Video to Group
                </button>
              </div>

              <!-- Group - Document Form -->
              <div x-show="msgType === 'document'" x-transition>
                @include('admin.communication.whatsapp.partials._url_field', ['name'=>'document_url', 'label'=>'Document URL', 'placeholder'=>'https://.../report.pdf', 'required'=>true, 'help'=>'Public document URL'])
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">File Name</label>
                    <input type="text" name="file_name" required placeholder="report.pdf"
                           class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                  </div>
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Caption <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" name="caption" placeholder="Document description..."
                           class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                  </div>
                </div>
                <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-amber-500 to-amber-600 text-white text-sm font-semibold transition-all shadow-lg shadow-amber-500/20">
                  <i class="fa-solid fa-file-pdf mr-2"></i>Send Document to Group
                </button>
              </div>

              <!-- Group - Audio Form -->
              <div x-show="msgType === 'audio'" x-transition>
                @include('admin.communication.whatsapp.partials._url_field', ['name'=>'audio_url', 'label'=>'Audio URL', 'placeholder'=>'https://.../audio.mp3', 'required'=>true, 'help'=>'Public audio URL (MP3, OGG, AMR, max 16MB)'])
                <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-violet-500 to-violet-600 text-white text-sm font-semibold transition-all shadow-lg shadow-violet-500/20">
                  <i class="fa-solid fa-music mr-2"></i>Send Audio to Group
                </button>
              </div>

              <!-- Group - Sticker Form -->
              <div x-show="msgType === 'sticker'" x-transition>
                @include('admin.communication.whatsapp.partials._url_field', ['name'=>'sticker_url', 'label'=>'Sticker URL (.webp)', 'placeholder'=>'https://.../sticker.webp', 'required'=>true, 'help'=>'Must be .webp, max 100KB, 512×512px'])
                <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-pink-500 to-pink-600 text-white text-sm font-semibold transition-all shadow-lg shadow-pink-500/20">
                  <i class="fa-solid fa-face-smile mr-2"></i>Send Sticker to Group
                </button>
              </div>

              <!-- Group - Contact Form -->
              <div x-show="msgType === 'contact'" x-transition>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Contact Full Name</label>
                    <input type="text" name="contact_name" required placeholder="John Doe"
                           class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                  </div>
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Contact Phone Number</label>
                    <input type="text" name="contact_phone" required placeholder="+255711000000"
                           class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                  </div>
                </div>
                <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-cyan-500 to-cyan-600 text-white text-sm font-semibold transition-all shadow-lg shadow-cyan-500/20">
                  <i class="fa-solid fa-address-book mr-2"></i>Send Contact to Group
                </button>
              </div>

              <!-- Group - Location Form -->
              <div x-show="msgType === 'location'" x-transition>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Latitude</label>
                    <input type="number" step="any" name="latitude" required placeholder="-6.7924"
                           class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                  </div>
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Longitude</label>
                    <input type="number" step="any" name="longitude" required placeholder="39.2083"
                           class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                  </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Location Name <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" name="name" placeholder="Head Office"
                           class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                  </div>
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Address <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" name="address" placeholder="Samora Avenue, Dar es Salaam"
                           class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                  </div>
                </div>
                <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 text-white text-sm font-semibold transition-all shadow-lg shadow-orange-500/20">
                  <i class="fa-solid fa-location-dot mr-2"></i>Send Location to Group
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- ========================================================== -->
      <!-- SMS Messages -->
      <!-- ========================================================== -->
      <div x-show="channelType === 'sms'" x-transition>
        <!-- Message Type Tabs -->
        <div class="flex gap-2 border-b border-primary-200 dark:border-primary-800 mb-4">
          <button @click="messageScope = 'single'"
                  :class="messageScope === 'single' ? 'border-b-2 border-primary-500 text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400'"
                  class="px-4 py-2 text-sm font-medium transition-colors">
            <i class="fa-solid fa-user mr-1"></i>Single SMS
          </button>
          <button @click="messageScope = 'bulk'"
                  :class="messageScope === 'bulk' ? 'border-b-2 border-primary-500 text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400'"
                  class="px-4 py-2 text-sm font-medium transition-colors">
            <i class="fa-solid fa-users mr-1"></i>Bulk SMS
          </button>
        </div>

        <!-- Single SMS Form -->
        <div x-show="messageScope === 'single'" x-transition>
          <form action="{{ route('admin.communication.whatsapp.send-single-sms') }}" method="POST">
            @csrf
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Phone Number</label>
                <input type="text" name="phone_number" required placeholder="255123456789"
                       class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Include country code (e.g., 255 for Tanzania, without +)</p>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Message</label>
                <textarea name="message" rows="4" required placeholder="Enter your SMS message here"
                          class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none"></textarea>
              </div>
              <button type="submit" class="px-4 py-2 rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 text-white text-sm font-semibold transition-all shadow-lg shadow-blue-500/20">
                <i class="fa-solid fa-comment-sms mr-2"></i>Send SMS
              </button>
            </div>
          </form>
        </div>

        <!-- Bulk SMS Form -->
        <div x-show="messageScope === 'bulk'" x-transition>
          <form action="{{ route('admin.communication.whatsapp.send-bulk-sms') }}" method="POST">
            @csrf
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Phone Numbers</label>
                <textarea name="phone_numbers" rows="6" required placeholder="2551234567890&#10;2551234567891&#10;2551234567892"
                          class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none font-mono text-sm"></textarea>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Enter one phone number per line (include country code, without +)</p>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Message</label>
                <textarea name="message" rows="4" required placeholder="Enter your SMS message here"
                          class="w-full px-4 py-2.5 rounded-lg border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none"></textarea>
              </div>
              <button type="submit" class="px-4 py-2 rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 text-white text-sm font-semibold transition-all shadow-lg shadow-blue-500/20">
                <i class="fa-solid fa-comment-sms mr-2"></i>Send Bulk SMS
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  @else
  <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl p-6 text-center">
    <i class="fa-brands fa-whatsapp text-4xl text-gray-400 dark:text-gray-500 mb-3"></i>
    <p class="text-sm text-gray-600 dark:text-gray-400">Configure your Session API Key and activate it to send messages</p>
  </div>
  @endif

  <!-- Message History Section -->
  <div class="bg-white dark:bg-dark-card rounded-xl shadow-sm border border-primary-100 dark:border-primary-800 p-6">
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center">
        <i class="fa-solid fa-history"></i>
      </div>
      <div>
        <h3 class="text-lg font-semibold text-primary-900 dark:text-white">Message History</h3>
        <p class="text-xs text-primary-500 dark:text-primary-400">View sent and failed messages</p>
      </div>
    </div>

    @if($messageHistory->count() > 0)
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-primary-50 dark:bg-primary-900/20">
              <th class="text-left py-3 px-4 text-xs font-semibold text-primary-600 dark:text-primary-400">Phone Number</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-primary-600 dark:text-primary-400">Message</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-primary-600 dark:text-primary-400">Type</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-primary-600 dark:text-primary-400">Status</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-primary-600 dark:text-primary-400">Sent At</th>
            </tr>
          </thead>
          <tbody>
            @foreach($messageHistory as $history)
              <tr class="border-b border-primary-100 dark:border-primary-800 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-3 px-4 text-sm text-primary-700 dark:text-primary-300">{{ $history->phone_number }}</td>
                <td class="py-3 px-4 text-sm text-primary-700 dark:text-primary-300 max-w-xs truncate">{{ Str::limit($history->message, 50) }}</td>
                <td class="py-3 px-4">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                    {{ ucfirst($history->message_type) }}
                  </span>
                </td>
                <td class="py-3 px-4">
                  @if($history->status === 'sent')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Sent</span>
                  @elseif($history->status === 'failed')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Failed</span>
                  @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Pending</span>
                  @endif
                </td>
                <td class="py-3 px-4 text-sm text-primary-700 dark:text-primary-300">{{ $history->sent_at ? $history->sent_at->format('M d, Y H:i') : 'N/A' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($messageHistory->hasPages())
        <div class="flex items-center justify-between mt-5 pt-5 border-t border-primary-100 dark:border-primary-800">
          <span class="text-xs text-primary-600 dark:text-primary-400">Showing {{ $messageHistory->firstItem() }} to {{ $messageHistory->lastItem() }} of {{ $messageHistory->total() }} results</span>
          {{ $messageHistory->links() }}
        </div>
      @endif
    @else
      <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No message history found.</p>
    @endif
  </div>

</div>
@endsection
