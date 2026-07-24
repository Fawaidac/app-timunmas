@extends('layouts.app')

@section('title', 'Titip Pembayaran')
@section('page_title', 'Titip Pembayaran')
@section('page_description', 'Form pembayaran untuk invoice pelanggan')

@section('content')
<div style="display:flex;gap:8px;margin-bottom:16px;">
    <a href="{{ route('sales.kunjungan.index') }}" class="button button-soft">← Kembali</a>
</div>

@if(session('error'))
    <div style="background:#fee;border:1px solid #fcc;color:#c33;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
        ✖ {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div style="background:#fee;border:1px solid #fcc;color:#c33;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
        <strong>Terdapat kesalahan:</strong>
        <ul style="margin:8px 0 0 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<article class="card">
    <header style="border-bottom:1px solid #eee;padding-bottom:12px;margin-bottom:20px;">
        <h3 style="margin:0;color:#333;">📝 Form Titip Pembayaran</h3>
        <p style="margin:4px 0 0;font-size:13px;color:#666;">Pembayaran akan masuk ke status <strong>Pending Approval</strong> dan menunggu persetujuan admin</p>
    </header>

    <form action="{{ route('sales.pembayaran.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="invoice_id" value="{{ $order->invoice->id }}">

        <!-- Info Order & Invoice (Read-only) -->
        <div style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:10px;padding:16px;margin-bottom:24px;">
            <h4 style="margin:0 0 12px;color:#495057;font-size:14px;">📋 Informasi Order & Invoice</h4>
            
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                <div>
                    <label style="display:block;font-size:12px;color:#666;margin-bottom:4px;">Nomor Order</label>
                    <input type="text" value="{{ $order->order_number }}" readonly 
                           style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;background:#fff;color:#333;">
                </div>
                
                <div>
                    <label style="display:block;font-size:12px;color:#666;margin-bottom:4px;">Nomor Invoice</label>
                    <input type="text" value="{{ $order->invoice->invoice_number }}" readonly 
                           style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;background:#fff;color:#333;font-weight:600;">
                </div>
            </div>

            <div style="margin-top:12px;">
                <label style="display:block;font-size:12px;color:#666;margin-bottom:4px;">Pelanggan</label>
                <input type="text" value="{{ $order->customer->name }}" readonly 
                       style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;background:#fff;color:#333;">
            </div>

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:12px;">
                <div>
                    <label style="display:block;font-size:12px;color:#666;margin-bottom:4px;">Total Invoice</label>
                    <input type="text" value="Rp {{ number_format($order->invoice->total_amount, 0, ',', '.') }}" readonly 
                           style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;background:#fff;color:#333;font-weight:600;">
                </div>
                
                <div>
                    <label style="display:block;font-size:12px;color:#666;margin-bottom:4px;">Sisa Tagihan</label>
                    <input type="text" value="Rp {{ number_format($order->invoice->remaining_balance, 0, ',', '.') }}" readonly 
                           style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;background:#fff;color:#C76C28;font-weight:600;">
                </div>
                
                <div>
                    <label style="display:block;font-size:12px;color:#666;margin-bottom:4px;">Jatuh Tempo</label>
                    <input type="text" value="{{ \Carbon\Carbon::parse($order->invoice->due_date)->format('d M Y') }}" readonly 
                           style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;background:#fff;color:#333;">
                </div>
            </div>
        </div>

        <!-- Form Input Pembayaran -->
        <div style="display:grid;gap:16px;">
            <!-- Metode Pembayaran -->
            <div>
                <label for="payment_method" style="display:block;font-weight:500;margin-bottom:6px;color:#333;">
                    Metode Pembayaran <span style="color:#C76C28;">*</span>
                </label>
                <select name="payment_method" id="payment_method" required
                        style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
                    <option value="">-- Pilih Metode --</option>
                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>💵 Cash (Tunai)</option>
                    <option value="transfer" {{ old('payment_method') === 'transfer' ? 'selected' : '' }}>🏦 Transfer Bank</option>
                    <option value="giro" {{ old('payment_method') === 'giro' ? 'selected' : '' }}>📄 Giro</option>
                    <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>🔖 Lainnya</option>
                </select>
                @error('payment_method')
                    <small style="color:#c33;font-size:12px;">{{ $message }}</small>
                @enderror
            </div>

            <!-- Nominal Pembayaran -->
            <div>
                <label for="amount_paid" style="display:block;font-weight:500;margin-bottom:6px;color:#333;">
                    Nominal Pembayaran <span style="color:#C76C28;">*</span>
                </label>
                <input type="number" name="amount_paid" id="amount_paid" 
                       value="{{ old('amount_paid', $order->invoice->remaining_balance) }}" 
                       min="0" step="0.01" required
                       style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;"
                       placeholder="Masukkan nominal pembayaran">
                <small style="font-size:12px;color:#666;">Sisa tagihan: Rp {{ number_format($order->invoice->remaining_balance, 0, ',', '.') }}</small>
                @error('amount_paid')
                    <small style="color:#c33;font-size:12px;display:block;">{{ $message }}</small>
                @enderror
            </div>

            <!-- Nomor Referensi (Optional) -->
            <div>
                <label for="reference_number" style="display:block;font-weight:500;margin-bottom:6px;color:#333;">
                    Nomor Referensi / No. Bukti
                </label>
                <input type="text" name="reference_number" id="reference_number" 
                       value="{{ old('reference_number') }}" 
                       maxlength="100"
                       style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;"
                       placeholder="Contoh: No. rekening, No. giro, atau bukti transfer">
                <small style="font-size:12px;color:#666;">Opsional - Untuk referensi internal</small>
                @error('reference_number')
                    <small style="color:#c33;font-size:12px;display:block;">{{ $message }}</small>
                @enderror
            </div>

            <!-- Upload Bukti Pembayaran (Optional) -->
            <div>
    <label style="display:block; font-weight:600; font-size:13px; color:#1e293b; margin-bottom:8px;">
        Upload Bukti Pembayaran
    </label>
    
    <!-- Custom Upload Box -->
    <div class="upload-zone" onclick="document.getElementById('proof_image').click()">
            <input type="file" name="proof_image" id="proof_image" 
                accept="image/jpeg,image/jpg,image/png" 
                style="display:none;" onchange="previewImage(this)">
            
            <!-- Placeholder View -->
            <div id="uploadPlaceholder" class="upload-placeholder">
                <div class="upload-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                </div>
                <p class="upload-title">Klik untuk upload foto</p>
                <p class="upload-sub">Format JPG, PNG (Maksimal 2MB) • Opsional</p>
            </div>

            <!-- Preview View (Tampil setelah file dipilih) -->
            <div id="uploadPreview" class="upload-preview" style="display:none;">
                <img id="imagePreview" src="" alt="Preview">
                <div class="preview-info">
                    <span id="fileName" class="file-name">filename.jpg</span>
                    <span class="file-action">Klik untuk mengganti foto</span>
                </div>
            </div>
        </div>

        @error('proof_image')
            <small style="color:#ef4444; font-size:12px; margin-top:6px; display:block; font-weight:500;">
                {{ $message }}
            </small>
        @enderror
    </div>

            <!-- Catatan (Optional) -->
            <div>
                <label for="notes" style="display:block;font-weight:500;margin-bottom:6px;color:#333;">
                    Catatan Tambahan
                </label>
                <textarea name="notes" id="notes" rows="3" 
                          style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;resize:vertical;"
                          placeholder="Catatan atau informasi tambahan mengenai pembayaran ini">{{ old('notes') }}</textarea>
                <small style="font-size:12px;color:#666;">Opsional</small>
            </div>
        </div>

        <!-- Info Box -->
        <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:12px 16px;margin:24px 0;">
            <p style="margin:0;font-size:13px;color:#856404;">
                <strong>ℹ️ Catatan:</strong> Setelah pembayaran dititipkan, status akan menjadi <strong>Pending Approval</strong>. 
                Admin akan melakukan verifikasi dan approval sebelum pembayaran tercatat secara resmi.
            </p>
        </div>

        <!-- Action Buttons -->
        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:24px;padding-top:16px;border-top:1px solid #eee;">
            <a href="{{ route('sales.order.show', $order->id) }}" class="button button-soft">
                Batal
            </a>
            <button type="submit" class="button button-primary">
                💾 Titip Pembayaran
            </button>
        </div>
    </form>
</article>

@push('scripts')
<script>
function previewImage(input) {
    const placeholder = document.getElementById('uploadPlaceholder');
    const preview = document.getElementById('uploadPreview');
    const image = document.getElementById('imagePreview');
    const fileName = document.getElementById('fileName');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            image.src = e.target.result;
            fileName.textContent = input.files[0].name;
            
            placeholder.style.display = 'none';
            preview.style.display = 'flex';
        }

        reader.readAsDataURL(input.files[0]);
    }
}
</script>
<script>
function previewImage(input) {
    const placeholder = document.getElementById('uploadPlaceholder');
    const preview = document.getElementById('uploadPreview');
    const image = document.getElementById('imagePreview');
    const fileName = document.getElementById('fileName');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            image.src = e.target.result;
            fileName.textContent = input.files[0].name;
            
            placeholder.style.display = 'none';
            preview.style.display = 'flex';
        }

        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
