@extends('layouts.app')

@section('title', 'Pencarian Stok - SFA Orange')
@section('page_title', 'Pencarian Stok Barang')
@section('page_description', 'Cek stok aktual per gudang sebelum membuat order pelanggan')

@section('content')
<div class="section-head">
    <h2>Pencarian Stok Barang</h2>
    <p>Cek stok aktual per gudang sebelum membuat order pelanggan.</p>
</div>

@include('partials.product-grid', ['showAddButton' => false])
@endsection
