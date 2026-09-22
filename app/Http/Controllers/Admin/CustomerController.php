<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with('sales')->orderBy('NM_CUST');

        if ($request->filled('search')) {
            $search = strtoupper(trim($request->search));
            $term = "%{$search}%";
            $query->where(function ($q) use ($term) {
                $q->whereRaw("UPPER(CAST(KD_CUST AS VARCHAR(100))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(NM_CUST AS VARCHAR(100))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(COALESCE(ALM_CUST, '') AS VARCHAR(255))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(COALESCE(HP, '') AS VARCHAR(100))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(COALESCE(TELP1, '') AS VARCHAR(100))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(COALESCE(E_MAIL, '') AS VARCHAR(100))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(COALESCE(C_PERSON, '') AS VARCHAR(100))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(COALESCE(WILAYAH, '') AS VARCHAR(100))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(COALESCE(NM_PEG, '') AS VARCHAR(100))) LIKE ?", [$term]);
            });
        }

        if ($request->filled('wilayah')) {
            $query->where('KD_WIL', $request->wilayah);
        }

        if ($request->filled('sales')) {
            $query->where('KD_PEG', $request->sales);
        }

        if ($request->filled('kategori')) {
            $query->where('KD_KAT', $request->kategori);
        }

        $customers = $query->paginate(15)->withQueryString();

        $wilayahList = DB::connection('firebird')->table('WILAYAH')->get();
        $salesList = Pegawai::salesAktif()->orderBy('NM_PEG')->get();
        $kategoriList = DB::connection('firebird')->table('KAT_CUSTOMER')->get();

        return view('admin.pelanggan.index', compact('customers', 'wilayahList', 'salesList', 'kategoriList'));
    }

    public function create()
    {
        $nextKdCust = Customer::nextKdCust('CUST');
        $wilayahList = DB::connection('firebird')->table('WILAYAH')->get();
        $kategoriList = DB::connection('firebird')->table('KAT_CUSTOMER')->get();
        $salesList = Pegawai::salesAktif()->orderBy('NM_PEG')->get();

        return view('admin.pelanggan.create', compact('nextKdCust', 'wilayahList', 'kategoriList', 'salesList'));
    }

    public function store(StoreCustomerRequest $request)
    {
        $data = $request->validated();
        $kdCust = !empty($data['kd_cust']) ? strtoupper(trim($data['kd_cust'])) : Customer::nextKdCust('CUST');

        $nmWil = null;
        if (!empty($data['kd_wil'])) {
            $w = DB::connection('firebird')->table('WILAYAH')->where('KD_WIL', $data['kd_wil'])->first();
            $nmWil = $w ? trim($w->WILAYAH) : null;
        }

        $nmKat = null;
        if (!empty($data['kd_kat'])) {
            $k = DB::connection('firebird')->table('KAT_CUSTOMER')->where('KD_KAT', $data['kd_kat'])->first();
            $nmKat = $k ? trim($k->KATEGORI) : null;
        }

        $nmPeg = null;
        if (!empty($data['kd_peg'])) {
            $p = Pegawai::where('KD_PEG', $data['kd_peg'])->first();
            $nmPeg = $p ? trim($p->NM_PEG) : null;
        }

        $customer = Customer::create([
            'KD_CUST'         => $kdCust,
            'NM_CUST'         => strtoupper(trim($data['nm_cust'])),
            'C_PERSON'        => $data['c_person'] ?? null,
            'ALM_CUST'        => $data['alm_cust'] ?? null,
            'TELP1'           => $data['telp1'] ?? null,
            'TELP2'           => $data['telp2'] ?? null,
            'HP'              => $data['hp'] ?? null,
            'FAX'             => $data['fax'] ?? null,
            'E_MAIL'          => $data['e_mail'] ?? null,
            'WEB_SITE'        => $data['web_site'] ?? null,
            'KD_KAT'          => $data['kd_kat'] ?? null,
            'KATEGORI'        => $nmKat,
            'KD_WIL'          => $data['kd_wil'] ?? null,
            'WILAYAH'         => $nmWil,
            'KD_PEG'          => $data['kd_peg'] ?? null,
            'NM_PEG'          => $nmPeg,
            'KRD_LIMIT'       => isset($data['krd_limit']) ? (float) $data['krd_limit'] : 0,
            'TOP_LIMIT'       => isset($data['top_limit']) ? (int) $data['top_limit'] : 0,
            'SO_AWAL_PIUTANG' => 0,
            'JML_PIUTANG'     => 0,
            'JML_BAYAR'       => 0,
            'NPWP'            => $data['npwp'] ?? null,
            'NM_PKP'          => $data['nm_pkp'] ?? null,
            'ALM_PKP'         => $data['alm_pkp'] ?? null,
            'BANK1'           => $data['bank1'] ?? null,
            'NO_REK1'         => $data['no_rek1'] ?? null,
            'BANK2'           => $data['bank2'] ?? null,
            'NO_REK2'         => $data['no_rek2'] ?? null,
            'LATITUDE'        => isset($data['latitude']) && $data['latitude'] !== '' ? (float) $data['latitude'] : null,
            'LONGITUDE'       => isset($data['longitude']) && $data['longitude'] !== '' ? (float) $data['longitude'] : null,
        ]);

        return redirect()->route('admin.customers.show', $customer->KD_CUST)
            ->with('success', "Customer [{$customer->NM_CUST}] berhasil ditambahkan.");
    }

    public function show($kdCust)
    {
        $customer = Customer::with('sales')
            ->where('KD_CUST', $kdCust)
            ->firstOrFail();

        return view('admin.pelanggan.show', compact('customer'));
    }

    public function edit($kdCust)
    {
        $customer = Customer::where('KD_CUST', $kdCust)->firstOrFail();
        $wilayahList = DB::connection('firebird')->table('WILAYAH')->get();
        $kategoriList = DB::connection('firebird')->table('KAT_CUSTOMER')->get();
        $salesList = Pegawai::salesAktif()->orderBy('NM_PEG')->get();

        return view('admin.pelanggan.edit', compact('customer', 'wilayahList', 'kategoriList', 'salesList'));
    }

    public function update(UpdateCustomerRequest $request, $kdCust)
    {
        $customer = Customer::where('KD_CUST', $kdCust)->firstOrFail();
        $data = $request->validated();

        $nmWil = $customer->WILAYAH;
        if (isset($data['kd_wil']) && $data['kd_wil'] !== $customer->KD_WIL) {
            $w = DB::connection('firebird')->table('WILAYAH')->where('KD_WIL', $data['kd_wil'])->first();
            $nmWil = $w ? trim($w->WILAYAH) : null;
        }

        $nmKat = $customer->KATEGORI;
        if (isset($data['kd_kat']) && $data['kd_kat'] !== $customer->KD_KAT) {
            $k = DB::connection('firebird')->table('KAT_CUSTOMER')->where('KD_KAT', $data['kd_kat'])->first();
            $nmKat = $k ? trim($k->KATEGORI) : null;
        }

        $nmPeg = $customer->NM_PEG;
        if (isset($data['kd_peg']) && $data['kd_peg'] !== $customer->KD_PEG) {
            $p = Pegawai::where('KD_PEG', $data['kd_peg'])->first();
            $nmPeg = $p ? trim($p->NM_PEG) : null;
        }

        $customer->update([
            'NM_CUST'   => strtoupper(trim($data['nm_cust'])),
            'C_PERSON'  => $data['c_person'] ?? null,
            'ALM_CUST'  => $data['alm_cust'] ?? null,
            'TELP1'     => $data['telp1'] ?? null,
            'TELP2'     => $data['telp2'] ?? null,
            'HP'        => $data['hp'] ?? null,
            'FAX'       => $data['fax'] ?? null,
            'E_MAIL'    => $data['e_mail'] ?? null,
            'WEB_SITE'  => $data['web_site'] ?? null,
            'KD_KAT'    => $data['kd_kat'] ?? null,
            'KATEGORI'  => $nmKat,
            'KD_WIL'    => $data['kd_wil'] ?? null,
            'WILAYAH'   => $nmWil,
            'KD_PEG'    => $data['kd_peg'] ?? null,
            'NM_PEG'    => $nmPeg,
            'KRD_LIMIT' => isset($data['krd_limit']) ? (float) $data['krd_limit'] : 0,
            'TOP_LIMIT' => isset($data['top_limit']) ? (int) $data['top_limit'] : 0,
            'NPWP'      => $data['npwp'] ?? null,
            'NM_PKP'    => $data['nm_pkp'] ?? null,
            'ALM_PKP'   => $data['alm_pkp'] ?? null,
            'BANK1'     => $data['bank1'] ?? null,
            'NO_REK1'   => $data['no_rek1'] ?? null,
            'BANK2'     => $data['bank2'] ?? null,
            'NO_REK2'   => $data['no_rek2'] ?? null,
            'LATITUDE'  => isset($data['latitude']) && $data['latitude'] !== '' ? (float) $data['latitude'] : null,
            'LONGITUDE' => isset($data['longitude']) && $data['longitude'] !== '' ? (float) $data['longitude'] : null,
        ]);

        return redirect()->route('admin.customers.show', $customer->KD_CUST)
            ->with('success', "Data Customer [{$customer->NM_CUST}] berhasil diperbarui.");
    }

    public function destroy($kdCust)
    {
        $customer = Customer::where('KD_CUST', $kdCust)->firstOrFail();

        $punyaOrder = \App\Models\SalesOrder::where('KD_CUST', $kdCust)->exists()
            || DB::connection('firebird')->table('MST_ORD_JUAL')->where('KD_CUST', $kdCust)->exists();

        if ($punyaOrder) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Customer memiliki riwayat transaksi sales order dan tidak dapat dihapus.');
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer berhasil dihapus.');
    }
}
