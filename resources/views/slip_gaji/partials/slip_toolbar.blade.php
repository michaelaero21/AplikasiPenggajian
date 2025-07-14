<div id="bulk-toolbar" class="alert alert-secondary d-flex justify-content-between align-items-center p-2 mb-2"
     style="display:none;">
    <span id="bulk-count"><strong>0</strong> dipilih</span>
    <span id="bulk-breakdown" class="ms-2 small text-muted">
        (0 siap generate · 0 ada slip)
    </span>
    <div class="dropdown">
        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="bulkDropdown"
                data-bs-toggle="dropdown" aria-expanded="false">
            Aksi Massal
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bulkDropdown">
            {{-- Aksi bila belum ada slip --}}
            <li>
              <form id="bulk-generate" method="POST" action="{{ route('slip-gaji.generate_massal') }}">
                @csrf
                <input type="hidden" name="selected" value="">
                <input type="hidden" name="periode"  value="{{ $periode }}">
                <input type="hidden" name="kategori" value="{{ $kategori ?? '' }}">
                <button class="dropdown-item" type="submit">Generate Slip</button>
              </form>
            </li>
            <li>
              <form id="bulk-thr" method="POST" action="{{ route('slip-gaji.setThrFlagMassal') }}">
                @csrf
                <input type="hidden" name="selected" value="">
                <input type="hidden" name="periode"  value="{{ $periode }}">
                <input type="hidden" name="kategori" value="{{ $kategori ?? '' }}">
                <button class="dropdown-item" type="submit">Input THR</button>
              </form>
            </li>
            <li><hr class="dropdown-divider"></li>
            {{-- Aksi bila sudah ada slip --}}
            <li>
              <form id="bulk-download" method="POST" action="{{ route('slip-gaji.download_massal') }}">
                @csrf
                <input type="hidden" name="slip_ids" value="">
                <button class="dropdown-item" type="submit">Unduh Slip</button>
              </form>
            </li>
            <li>
              <form id="bulk-wa" method="POST" action="{{ route('slip-gaji.kirim_wa_massal') }}">
                @csrf
                <input type="hidden" name="slip_ids" value="">
                <button class="dropdown-item" type="submit">Kirim WA</button>
              </form>
            </li>
        </ul>
    </div>
</div>