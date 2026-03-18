<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap NTE</title>

    <style>
        /* HALAMAN SELAIN PERTAMA */
        @page {
            margin-top: 4.5cm;
            margin-bottom: 2cm;
            margin-left: 1.5cm;
            margin-right: 1.5cm;
        }

        /* HALAMAN PERTAMA SAJA */
        /* @page :first {
            margin-top: 4.5cm;
        } */

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            margin: 0;
            padding: 0;
        }

        /* ================= HEADER ================= */
        #header {
            position: fixed;
            top: -4.5cm;
            left: 0;
            right: 0;
            height: 3cm;
        }

        .kop-container {
            padding: 0 1.5cm;
        }

        /* ====== KOP TABLE ====== */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .kop-logo-cell {
            width: 15%; 
            text-align: center;
            vertical-align: middle;
        }

        .kop-logo-cell img {
            width: 110px;
            height: auto;
        }

        .kop-dummy-cell {
            width: 15%; /* Harus sama persis dengan kop-logo-cell */
        }

        .kop-text-cell {
            width: 70%; 
            text-align: center;
            vertical-align: middle;
        }

        .kop-text-cell h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .kop-text-cell h3 {
            margin: 2px 0;
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .kop-text-cell p {
            margin: 0;
            font-size: 9pt;
            line-height: 1.3;
            white-space: nowrap; /* Mencegah auto-wrap turun ke baris baru */
        }

        .kop-line {
            margin-top: 6px;
            height: 3px;
            background-color: #000;
            width: 100%;
        }

        /* ================= FOOTER ================= */
        #footer {
            position: fixed;
            bottom: -1.5cm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
        
        .pagenum:before {
            content: counter(page);
        }

        /* ================= CONTENT ================= */
        .judul-dokumen {
            text-align: center;
            margin: 20px 0;
        }

        .judul-dokumen h3 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 10pt;
            vertical-align: top;
        }

        table.data-table th {
            background-color: #e8e8e8;
            text-align: center;
        }

        table.data-table tfoot th {
            background-color: #d5d5d5;
            font-size: 11pt;
            text-align: right;
        }
        
        .text-right {
            text-align: right;
        }

        tr {
            page-break-inside: avoid;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }
        
        .list-produksi {
            margin: 0;
            padding-left: 15px;
        }

        .catatan-container {
            margin-top: 20px;
            font-size: 10pt;
        }

        .catatan-container ul {
            margin: 5px 0 0 0;
            padding-left: 20px;
        }
    </style>
</head>

<body>

<div id="header">
    <div class="kop-container">
        <table class="kop-table">
            <tr>
                <td class="kop-logo-cell">
                    @php
                        $logoPath = public_path('images/Logo_Lampung.png');
                    @endphp

                    @if(file_exists($logoPath))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}">
                    @endif
                </td>

                <td class="kop-text-cell">
                    <h2>PEMERINTAH PROVINSI LAMPUNG</h2>
                    <h3>DINAS KEHUTANAN</h3>
                    <p>
                        Jl. Zainal Abidin Pagar Alam Rajabasa, Bandar Lampung, Lampung 35144<br>
                        Telp. (0721) 703177 Fax. 705058<br>
                        Laman: dishut.lampungprov.go.id &nbsp; Pos-el: dishut@lampungprov.go.id
                    </p>
                </td>
                
                <td class="kop-dummy-cell"></td>
            </tr>
        </table>
        <div class="kop-line"></div>
    </div>
</div>

<div id="footer">
    Halaman <span class="pagenum"></span>
</div>

<div class="content">

    <div class="judul-dokumen">
        <h3>REKAP NILAI TRANSAKSI EKONOMI</h3>
        <p style="margin-top: 5px;">Periode: {{ $periode }}</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                {{-- Header --}}
                @foreach($headers as $index => $header)
                    <th {!! $index === 0 ? 'width="5%"' : '' !!}>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            {{-- Row --}}
            @foreach($rows as $row)
            <tr>
                <td align="center">{{ $row['no'] }}</td>
                
                {{-- Loop untuk Nama KPH, Nama KTH, Nama Petani --}}
                @foreach($row['columns'] as $col)
                    <td>{{ $col }}</td>
                @endforeach
                
                {{-- Kolom Produksi & Total NTE --}}
                <td>{!! $row['produksi'] !!}</td>
                <td class="text-right">{{ $row['total_nte'] }}</td>
            </tr>
            @endforeach
        </tbody>
        
        <tfoot>
            <tr>
                <th colspan="{{ $colspan }}">GRAND TOTAL KESELURUHAN</th>
                <th class="text-right">{{ $grand_total }}</th>
            </tr>
        </tfoot>
    </table>

    {{-- CATATAN --}}
    <div class="catatan-container">
        <strong>Catatan:</strong>
        <ul>
            <li>Tipe: {{ ucfirst($type) }}</li>
            <li>Bandar Lampung, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</li>
        </ul>
    </div>

</div>

</body>
</html>