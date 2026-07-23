<div class="toolbar">
    <label class="search-box search-grow">
        <span>⌕</span>
        <input type="search" id="stock-search" placeholder="Cari nama barang, SKU, atau kategori...">
    </label>
    <select class="button button-soft">
        <option>Semua Gudang</option>
        <option>Gudang Jakarta</option>
        <option>Gudang Bekasi</option>
    </select>
    @if(isset($showAddButton) && $showAddButton)
        <button class="button button-primary" type="button" onclick="document.getElementById('add-product-modal').style.display='grid'">＋ Tambah Barang</button>
    @endif
</div>

<div class="product-grid" id="product-grid">
    @foreach([
        ['🫙','Minyak Goreng Premium 2L','MG-PREM-2L','248','Gudang Jakarta','Tersedia','success'],
        ['🧂','Gula Pasir Kristal 1Kg','GP-KRS-1K','86','Gudang Jakarta','Tersedia','success'],
        ['☕','Kopi Bubuk Robusta 250g','KB-ROB-250','14','Gudang Bekasi','Menipis','warning'],
        ['🥛','Susu UHT Full Cream 1L','SU-FC-1L','0','Gudang Jakarta','Habis','danger'],
        ['🍵','Teh Celup Melati 25s','TC-MLT-25','127','Gudang Jakarta','Tersedia','success'],
        ['🍪','Biskuit Kelapa 300g','BK-KLP-300','31','Gudang Bekasi','Tersedia','success'],
    ] as $product)
        <article class="product-card" data-product="{{ strtolower($product[1] . ' ' . $product[2]) }}">
            <div class="product-image">{{ $product[0] }}</div>
            <h4>{{ $product[1] }}</h4>
            <div class="sku">SKU: {{ $product[2] }}</div>
            <div class="stock-row">
                <div><div class="stock-value">{{ $product[3] }}</div><div class="warehouse">{{ $product[4] }}</div></div>
                <span class="badge badge-{{ $product[6] }}">{{ $product[5] }}</span>
            </div>
        </article>
    @endforeach
</div>
