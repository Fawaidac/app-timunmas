<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Helpers\SalesHelper;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    private function salesPegawai()
    {
        return SalesHelper::pegawai();
    }

    private function kdPeg(): string
    {
        return SalesHelper::kdPeg() ?? '';
    }

    public function index(Request $request)
    {
        $sales  = $this->salesPegawai();
        $kdPeg  = $this->kdPeg();

        $query = Customer::where('KD_PEG', $kdPeg)->orderBy('NM_CUST');

        if ($request->filled('search')) {
            $term = '%' . strtoupper(trim($request->search)) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw("UPPER(CAST(KD_CUST AS VARCHAR(100))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(NM_CUST AS VARCHAR(100))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(COALESCE(ALM_CUST, '') AS VARCHAR(255))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(COALESCE(HP, '') AS VARCHAR(100))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(COALESCE(TELP1, '') AS VARCHAR(100))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(COALESCE(C_PERSON, '') AS VARCHAR(100))) LIKE ?", [$term]);
            });
        }

        $customers = $query->paginate(15)->withQueryString();

        return view('sales.customer.index', compact('customers', 'sales'));
    }

    public function create()
    {
        $sales       = $this->salesPegawai();
        $nextKdCust  = Customer::nextKdCust('WEB');
        $wilayahList = DB::connection('firebird')->table('WILAYAH')->get();
        $kategoriList= DB::connection('firebird')->table('KAT_CUSTOMER')->get();

        return view('sales.customer.create', compact('sales', 'nextKdCust', 'wilayahList', 'kategoriList'));
    }

    public function store(Request $request)
    {
        $sales = $this->salesPegawai();
        $kdPeg = $this->kdPeg();

        $validated = $request->validate([
            'kd_cust'   => 'nullable|string|max:9|unique:CUSTOMER,KD_CUST',
            'nm_cust'   => 'required|string|max:50',
            'c_person'  => 'nullable|string|max:20',
            'alm_cust'  => 'nullable|string|max:65',
            'telp1'     => 'nullable|string|max:14',
            'telp2'     => 'nullable|string|max:14',
            'hp'        => 'nullable|string|max:14',
            'e_mail'    => 'nullable|email|max:50',
            'kd_kat'    => 'nullable|string|max:9',
            'kd_wil'    => 'nullable|string|max:20',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ], [
            'nm_cust.required'  => 'Nama customer wajib diisi.',
            'kd_cust.unique'    => 'Kode customer sudah terdaftar, gunakan kode lain.',
        ]);

        $kdCust = !empty($validated['kd_cust'])
            ? strtoupper(trim($validated['kd_cust']))
            : Customer::nextKdCust('WEB');

        $nmWil = null;
        if (!empty($validated['kd_wil'])) {
            $w = DB::connection('firebird')->table('WILAYAH')->where('KD_WIL', $validated['kd_wil'])->first();
            $nmWil = $w ? trim($w->WILAYAH) : null;
        }

        $nmKat = null;
        if (!empty($validated['kd_kat'])) {
            $k = DB::connection('firebird')->table('KAT_CUSTOMER')->where('KD_KAT', $validated['kd_kat'])->first();
            $nmKat = $k ? trim($k->KATEGORI) : null;
        }

        $customer = Customer::create([
            'KD_CUST'         => $kdCust,
            'NM_CUST'         => strtoupper(trim($validated['nm_cust'])),
            'C_PERSON'        => $validated['c_person'] ?? null,
            'ALM_CUST'        => $validated['alm_cust'] ?? null,
            'TELP1'           => $validated['telp1'] ?? null,
            'TELP2'           => $validated['telp2'] ?? null,
            'HP'              => $validated['hp'] ?? null,
            'E_MAIL'          => $validated['e_mail'] ?? null,
            'KD_KAT'          => $validated['kd_kat'] ?? null,
            'KATEGORI'        => $nmKat,
            'KD_WIL'          => $validated['kd_wil'] ?? null,
            'WILAYAH'         => $nmWil,
            'KD_PEG'          => $kdPeg,
            'NM_PEG'          => $sales?->NM_PEG ?? SalesHelper::nama(),
            'KRD_LIMIT'       => 0,
            'TOP_LIMIT'       => 0,
            'SO_AWAL_PIUTANG' => 0,
            'JML_PIUTANG'     => 0,
            'JML_BAYAR'       => 0,
            'LATITUDE'        => isset($validated['latitude']) && $validated['latitude'] !== '' ? (float) $validated['latitude'] : null,
            'LONGITUDE'       => isset($validated['longitude']) && $validated['longitude'] !== '' ? (float) $validated['longitude'] : null,
        ]);

        return redirect()->route('sales.customer.show', $customer->KD_CUST)
            ->with('success', "Customer [{$customer->NM_CUST}] berhasil ditambahkan.");
    }


    public function show($kdCust)
    {
        $sales    = $this->salesPegawai();
        $kdPeg    = $this->kdPeg();
        $customer = Customer::with('sales')
            ->where('KD_CUST', $kdCust)
            ->where('KD_PEG', $kdPeg)
            ->firstOrFail();

        return view('sales.customer.show', compact('customer', 'sales'));
    }


    public function edit($kdCust)
    {
        $sales       = $this->salesPegawai();
        $kdPeg       = $this->kdPeg();
        $customer    = Customer::where('KD_CUST', $kdCust)
            ->where('KD_PEG', $kdPeg)
            ->firstOrFail();
        $wilayahList = DB::connection('firebird')->table('WILAYAH')->get();
        $kategoriList= DB::connection('firebird')->table('KAT_CUSTOMER')->get();

        return view('sales.customer.edit', compact('customer', 'sales', 'wilayahList', 'kategoriList'));
    }

    public function update(Request $request, $kdCust)
    {
        $kdPeg    = $this->kdPeg();
        $customer = Customer::where('KD_CUST', $kdCust)
            ->where('KD_PEG', $kdPeg)
            ->firstOrFail();

        $validated = $request->validate([
            'nm_cust'   => 'required|string|max:50',
            'c_person'  => 'nullable|string|max:20',
            'alm_cust'  => 'nullable|string|max:65',
            'telp1'     => 'nullable|string|max:14',
            'telp2'     => 'nullable|string|max:14',
            'hp'        => 'nullable|string|max:14',
            'e_mail'    => 'nullable|email|max:50',
            'kd_kat'    => 'nullable|string|max:9',
            'kd_wil'    => 'nullable|string|max:20',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ], [
            'nm_cust.required' => 'Nama customer wajib diisi.',
        ]);

        $nmWil = $customer->WILAYAH;
        if (isset($validated['kd_wil']) && $validated['kd_wil'] !== $customer->KD_WIL) {
            $w = DB::connection('firebird')->table('WILAYAH')->where('KD_WIL', $validated['kd_wil'])->first();
            $nmWil = $w ? trim($w->WILAYAH) : null;
        }

        $nmKat = $customer->KATEGORI;
        if (isset($validated['kd_kat']) && $validated['kd_kat'] !== $customer->KD_KAT) {
            $k = DB::connection('firebird')->table('KAT_CUSTOMER')->where('KD_KAT', $validated['kd_kat'])->first();
            $nmKat = $k ? trim($k->KATEGORI) : null;
        }

        $customer->update([
            'NM_CUST'   => strtoupper(trim($validated['nm_cust'])),
            'C_PERSON'  => $validated['c_person'] ?? null,
            'ALM_CUST'  => $validated['alm_cust'] ?? null,
            'TELP1'     => $validated['telp1'] ?? null,
            'TELP2'     => $validated['telp2'] ?? null,
            'HP'        => $validated['hp'] ?? null,
            'E_MAIL'    => $validated['e_mail'] ?? null,
            'KD_KAT'    => $validated['kd_kat'] ?? null,
            'KATEGORI'  => $nmKat,
            'KD_WIL'    => $validated['kd_wil'] ?? null,
            'WILAYAH'   => $nmWil,
            'LATITUDE'  => isset($validated['latitude']) && $validated['latitude'] !== '' ? (float) $validated['latitude'] : null,
            'LONGITUDE' => isset($validated['longitude']) && $validated['longitude'] !== '' ? (float) $validated['longitude'] : null,
        ]);

        return redirect()->route('sales.customer.show', $customer->KD_CUST)
            ->with('success', "Data Customer [{$customer->NM_CUST}] berhasil diperbarui.");
    }
}
