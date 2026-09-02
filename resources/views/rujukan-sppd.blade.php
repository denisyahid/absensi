@push('style')
    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            .area-printer,
            .area-printer * {
                visibility: visible;
            }

            .area-printer {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>
    <style>
        /* ================= PRINT SETTING ================= */
        @media print {

            /* Ukuran kertas F4 */
            @page {
                size: 21cm 33cm;
                /* F4 */
                margin: 1cm;
            }

            /* Sembunyikan elemen lain */
            body * {
                visibility: hidden;
            }

            .area-printer,
            .area-printer * {
                visibility: visible;
            }

            .area-printer {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* Font seperti Word / Excel */
            body,
            .area-printer {
                font-family: "Times New Roman", Arial, sans-serif;
                font-size: 9pt;
                line-height: 1.2;
                color: #000;
            }

            .sppd table {
                border-collapse: collapse;
                width: 100%;
            }


            .sppd td {
                border: 1px solid #000;
                padding: 6px;
                vertical-align: top;
            }
        }

        /* ================================================= */
    </style>
    <script>
        window.print();
    </script>
@endpush
<x-slot name="header">
    <div class="flex justify-between p-3 shadow-xl">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Print SPPD

        </h2>

        <div>

            <button type="button" onclick="window.print()" class="p-2 text-white bg-green-500 rounded"> <i
                    class="bx bx-print"></i> PRINT</button>
            <a href="/rujukan" class="p-2 text-white rounded bg-slate-500">Kembali</a>
        </div>
    </div>
</x-slot>

<div>

    <div class="area-printer">
        {{-- surtug --}}
        @if ($data->pns->count() == 1)
            <section class=" bg-white h-[330mm] mb-10">
                <x-kop-surat />
                <div>

                    <div class="text-xl font-extrabold text-center underline">
                        SURAT TUGAS
                    </div>
                    <div class="grid grid-cols-2 text-lg">
                        <div class="text-right me-12">
                            {{-- NOMOR : {{ $nomor_sppd }} --}}
                            NOMOR
                        </div>
                        <div>
                            {{-- : {{ $data->nomor_surat }} 800.1.11.1/ 002/2026/RSUD_Malangbong --}}
                            : 800.1.11.1/......./RSUD_Malangbong/2026
                        </div>
                    </div>
                </div>
                <div class="ps-16">
                    <table class="w-full text-lg border-0">
                        <tr class="">
                            <td class="py-0">Menimbang</td>
                            <td class="py-0">: - </td>
                            <td class="py-0"></td>

                        </tr>
                        <tr class="">
                            <td class="py-0">Dasar</td>
                            <td class="py-0">: </td>
                            <td class="py-0"></td>

                        </tr>
                        <tr>
                            <td class="pt-5" colspan="3">
                                <div class="text-xl font-extrabold text-center -ms-16">

                                    MEMERINTAHKAN
                                </div>
                            </td>
                        </tr>

                        <tr class="">
                            <td class="py-0">Kepada</td>
                            <td class="py-0">: Nama</td>
                            <td class="py-0">: {{ $data->pns->first()->nama }}</td>

                        </tr>
                        <tr class="">
                            <td class="py-0"></td>
                            <td class="py-0 ps-3">NIP</td>
                            <td class="py-0">: {{ $data->pns->first()->nip }}</td>

                        </tr>
                        <tr class="">
                            <td class="py-0"></td>
                            <td class="py-0 ps-3 whitespace-nowrap">Pangkat, Gol/Ruang </td>
                            <td class="py-0">: {{ $data->pns->first()->pangkat_golongan }} </td>

                        </tr>
                        <tr class="">
                            <td class="py-0"></td>
                            <td class="py-0 align-top ps-3 ">Jabatan </td>
                            <td class="py-0 ">
                                <div class="flex">
                                    <span class="pr-2">:</span>
                                    <span class="block">
                                        {{ $data->pns->first()->jabatan }}
                                    </span>
                                </div>
                            </td>

                        </tr>
                        <tr class="">
                            <td>Untuk </td>
                            <td colspan="2">: {{ $data->perihal }}
                            </td>

                        </tr>

                        <tr class="">
                            <td></td>
                            <td class="ps-3">Hari </td>
                            <td>: {{ $hari_berangkat }} </td>

                        </tr>
                        <tr class="">
                            <td></td>
                            <td class="ps-3">Tanggal </td>
                            <td>: {{ $data->tanggal_berangkat->translatedFormat('d F Y') }} </td>

                        </tr>
                        <tr class="">
                            <td></td>
                            <td class="ps-3">Waktu </td>
                            <td>: {{ $data->waktu }} </td>

                        </tr>
                        <tr class="">
                            <td></td>
                            <td class="ps-3">Tempat </td>
                            <td>: {{ $data->tempat }} </td>

                        </tr>
                        <tr class="">
                            <td></td>
                            <td> </td>
                            <td class="ps-3 pe-4">{{ $data->alamat_rujukan }}
                            </td>

                        </tr>
                        <tr>
                            <td colspan="3" class="py-8">Demikian, surat tugas ini kami buat agar yang
                                berkepentingan
                                melaksanakan
                                tugas
                                sebagaimana
                                mestinya. </td>
                        </tr>
                        <tr class="">
                            <td></td>
                            <td></td>
                            <td>
                                <div class="flex justify-center">
                                    <div>

                                        Ditetapkan di : Garut <br>
                                        Pada Tanggal : {{ $berangkat }} <br>
                                        <div class="text-center">
                                            Direktur ,
                                        </div>
                                        <div class="flex justify-center w-full mt-10">
                                            <img class="w-[70%]" src="/assets/img/ttd_budir.png" alt="">
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </section>
        @else
            <section class=" bg-white h-[330mm] mb-10">
                <x-kop-surat />
                <div>

                    <div class="text-xl font-extrabold text-center underline">
                        SURAT TUGAS
                    </div>
                    <div class="grid grid-cols-2 text-lg">
                        <div class="text-right me-12">
                            {{-- NOMOR : {{ $nomor_sppd }} --}}
                            NOMOR
                        </div>
                        <div>
                            : 800.1.11.1/......./RSUD_Malangbong/2026

                        </div>
                    </div>
                </div>
                <div class="ps-16">
                    <table class="w-full text-lg border-0">
                        <tr class="">
                            <td class="py-0">Menimbang</td>
                            <td class="py-0">: - </td>
                            <td class="py-0"></td>

                        </tr>
                        <tr class="">
                            <td class="py-0">Dasar</td>
                            <td class="py-0">: </td>
                            <td class="py-0"></td>

                        </tr>
                        <tr>
                            <td class="pt-5" colspan="3">
                                <div class="text-xl font-extrabold text-center -ms-16">

                                    MEMERINTAHKAN
                                </div>
                            </td>
                        </tr>
                        <tr class="">
                            <td class="py-0">Kepada</td>
                            <td class="py-0 border-black pe-10" colspan="2">

                                <table class="w-full text-sm">
                                    <tr class="text-center">
                                        <td class="border border-black ps-2">No</td>
                                        <td class="border border-black ps-2">Nama / NiP </td>
                                        <td class="border border-black ps-2">Pangkat / Golongan </td>
                                        <td class="border border-black ps-2">Jabatan </td>
                                    </tr>
                                    @foreach ($data->pns as $index => $pns)
                                        <tr>
                                            <td class="border border-black ps-2">{{ $index + 1 }}</td>
                                            <td class="align-top border border-black ps-2">
                                                <p class="w-full border-b border-black">

                                                    {{ $pns->nama }}
                                                </p>
                                                {{ $pns->nip }}
                                            </td>
                                            <td class="align-top border border-black ps-2">{{ $pns->pangkat_golongan }}
                                            </td>
                                            <td class="align-top border border-black ps-2">{{ $pns->jabatan }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>


                        </tr>

                        <tr class="">
                            <td>Untuk </td>
                            <td colspan="2">: {{ $data->perihal }}
                            </td>

                        </tr>

                        <tr class="">
                            <td></td>
                            <td class="ps-3">Hari </td>
                            <td>: {{ $hari_berangkat }} </td>

                        </tr>
                        <tr class="">
                            <td></td>
                            <td class="ps-3">Tanggal </td>
                            <td>: {{ $data->tanggal_berangkat->translatedFormat('d F Y') }} </td>

                        </tr>
                        <tr class="">
                            <td></td>
                            <td class="ps-3">Waktu </td>
                            <td>: {{ $data->waktu }} </td>

                        </tr>
                        <tr class="">
                            <td></td>
                            <td class="ps-3">Tempat </td>
                            <td>: {{ $data->tempat }} </td>

                        </tr>
                        <tr class="">
                            <td></td>
                            <td> </td>
                            <td class="ps-3 pe-4">{{ $data->alamat_rujukan }}
                            </td>

                        </tr>
                        <tr>
                            <td colspan="3" class="py-8">Demikian, surat tugas ini kami buat agar yang
                                berkepentingan
                                melaksanakan
                                tugas
                                sebagaimana
                                mestinya. </td>
                        </tr>
                        <tr class="">
                            <td></td>
                            <td></td>
                            <td>
                                <div class="flex justify-center">
                                    <div>

                                        Ditetapkan di : Garut <br>
                                        Pada Tanggal : {{ $berangkat }} <br>
                                        <div class="text-center">
                                            Direktur ,
                                        </div>
                                        <div class="flex justify-center w-full mt-10">
                                            <img class="w-[70%]" src="/assets/img/ttd_budir.png" alt="">
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </section>
        @endif

        {{-- spd depan --}}
        @foreach ($data->pns as $pns)
            <section class=" bg-white h-[330mm] mb-10">
                <x-kop-surat />
                <div>
                    <div class="grid grid-cols-2 mb-2 text-xs">
                        <div class="text-right me-12">
                        </div>
                        <div class="grid grid-cols-2">
                            <p>

                                Lampiran Ke
                            </p>
                            <p>
                                :
                            </p>
                            <p>

                                Kode No.
                            </p>
                            <p>
                                : 5.1.02.04.01.0003.
                            </p>
                            <p>

                                Nomor
                            </p>
                            <p>
                                {{-- : {{ $data->nomor_surat }} --}}
                                : 800.1.11.1/......./RSUD_Malangbong/2026

                            </p>
                        </div>
                    </div>
                    <div class="text-xl font-extrabold text-center underline">
                        SURAT PERJALANAN DINAS <br> ( S P D )
                    </div>

                </div>
                <div class="px-12 pt-5">
                    <table class="w-full text-lg border-0">
                        <tr class="">
                            <td class="py-0 text-sm align-top border border-black ps-1">1</td>
                            <td class="py-0 text-sm align-top border border-black ps-1">Pejabat berwenang yang memberi
                                perintah</td>
                            <td class="py-0 text-sm align-top border border-black ps-1" colspan="2">Kuasa Pengguna
                                Anggaran</td>
                        </tr>
                        <tr class="">
                            <td class="py-0 text-sm align-top border border-black ps-1">2</td>
                            <td class="py-0 text-sm align-top border border-black ps-1">Nama/NIP Pegawai yang
                                diperintah
                            </td>
                            <td class="py-0 text-sm align-top border border-black ps-1" colspan="2">
                                {{ $pns->nama }} /
                                {{ $pns->nip }} </td>
                        </tr>
                        <tr class="">
                            <td class="py-0 text-sm align-top border border-black ps-1">3</td>
                            <td class="py-0 text-sm align-top border border-black ps-1">a. Pangkat dan Golongan <br>
                                b. Jabatan / Intansi <br>
                                c. Tingkat Biaya perjalanan
                            </td>
                            <td class="py-0 text-sm align-top border border-black ps-1" colspan="2">
                                {{ $pns->pangkat_golongan }} <br>
                                {{ $pns->jabatan }} / UOBK RSUD Malangbong <br>
                                a. Tingkat A. <br>
                                b. Tingkat B. <br>
                                c. Tingkat C. </td>
                        </tr>
                        <tr class="">
                            <td class="py-0 text-sm align-top border border-black ps-1">4</td>
                            <td class="py-0 text-sm align-top border border-black ps-1">Maksud Perjalanan Dinas </td>
                            <td class="py-0 text-sm font-bold border border-black ps-1" colspan="2">
                                {{ $data->perihal }} </td>
                        </tr>
                        <tr class="">
                            <td class="py-0 text-sm align-top border border-black ps-1">5</td>
                            <td class="py-0 text-sm align-top border border-black ps-1"> Alat Angkutan yang
                                dipergunakan
                            </td>
                            <td class="py-0 text-sm align-top border border-black ps-1" colspan="2">
                                {{ $data->alat_angkut }} </td>
                        </tr>
                        <tr class="">
                            <td class="py-0 text-sm align-top border border-black ps-1">6</td>
                            <td class="py-0 text-sm align-top border border-black ps-1"> a. Tempat Berangkat <br>
                                b. Tempat Tujuan </td>
                            <td class="py-0 text-sm align-top border border-black ps-1" colspan="2"> UOBK RSUD
                                Malangbong <br>
                                {{ $data->tempat }} </td>
                        </tr>
                        <tr class="">
                            <td class="py-0 text-sm align-top border border-black ps-1">7</td>
                            <td class="py-0 text-sm align-top border border-black ps-1"> a Lamanya Perjalanan Dinas
                                <br>
                                b Tanggal Berangkat <br>
                                c Tanggal harus kembali
                            </td>
                            <td class="py-0 text-sm align-top border border-black ps-1" colspan="2">
                                {{ $data->lama_perjalanan }} Hari
                                <br>
                                {{ $tgl_berangkat }}<br>
                                {{ $tgl_pulang }}
                            </td>
                        </tr>
                        <tr class="">
                            <td class="py-0 text-sm align-top border border-black ps-1">8</td>
                            <td class="py-0 text-sm align-top border border-black ps-1"> Pengikut : Nama </td>
                            <td class="py-0 text-sm align-top border border-black ps-1"> Tanggal Lahir</td>
                            <td class="py-0 text-sm align-top border border-black ps-1"> Keterangan </td>
                        </tr>
                        <tr class="">
                            <td class="py-0 text-sm align-top border border-black ps-1"></td>
                            <td class="py-0 text-sm align-top border border-black ps-1"> 1 <br> 2 <br> 3 <br> 4 <br> 5
                                <br>
                            </td>
                            <td class="py-0 text-sm align-top border border-black ps-1"> </td>
                            <td class="py-0 text-sm align-top border border-black ps-1"> </td>
                        </tr>
                        <tr class="">
                            <td class="py-0 text-sm align-top border border-black ps-1">9</td>
                            <td class="py-0 text-sm align-top border border-black ps-1"> Pembebanan Anggaran <br>
                                a Instansi <br>
                                b Mata Anggaran </td>
                            <td class="py-0 text-sm align-top border border-black ps-1" colspan="2"> <br>
                                a. Dinas Kesehatan Kabupaten Garut <br>
                                b. Peningkatan Pelayanan BLUD </td>
                        </tr>
                        <tr class="">
                            <td class="py-0 text-sm align-top border border-black ps-1">10</td>
                            <td class="py-0 text-sm align-top border border-black ps-1" colspan="3"> Keterangan
                                Lain-lain <br>
                            </td>
                        </tr>
                        <tr class=""">
                            <td class="py-0 pt-10 text-sm align-top ps-1"> </td>
                            <td class="py-0 text-sm align-top ps-1"> </td>
                            <td class="py-0 pt-10 text-sm align-top ps-40"> Dikeluarkan di </td>
                            <td class="py-0 pt-10 text-sm align-top ps-1"> : Garut </td>
                        </tr>
                        <tr class=""">
                            <td class="py-0 pt-10 text-sm align-top ps-1"> </td>
                            <td class="py-0 text-sm align-top ps-1"> </td>
                            <td class="py-0 text-sm align-top ps-40"> Pada tanggal </td>
                            <td class="py-0 text-sm align-top ps-1"> : {{ $tgl_berangkat }} </td>
                        </tr>
                        <tr class=""">
                            <td class="py-0 pt-10 text-sm align-top ps-1"> </td>
                            <td class="py-0 text-sm align-top ps-1"> </td>
                            <td class="py-0 text-sm align-top ps-40" colspan="2">
                                <p class="text-center">
                                    Kuasa Pengguna Anggaran <br>
                                    UOBK RSUD Malangbong </p>
                            </td>
                        </tr>
                        <tr class=""">
                            <td class="py-0 pt-10 text-sm align-top ps-1"> </td>
                            <td class="py-0 text-sm align-top ps-1"> </td>
                            <td class="py-0 pt-24 text-sm align-top ps-40" colspan="2">
                                <p class="text-center">
                                    <b class="underline"> dr. Hj. Novita Silvana Mua </b><br>
                                    NIP . 197711052014122001
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </section>
            <section class="px-5 bg-white  h-[330mm] sppd ">
                <table class="w-full border">
                    <tr>
                        <td class="border"></td>
                        <td class="border ">
                            <div class="flex">

                                <div class="me-8">
                                    I.
                                </div>
                                <div class="grid w-full grid-cols-[40%_60%] mb-1">
                                    <div class="">
                                        Berangkat dari
                                    </div>
                                    <div class="">
                                        : UOBK RSUD Malangbong
                                    </div>
                                    <div class="">
                                        (Tempat kedudukan)
                                    </div>
                                    <div></div>
                                    <div class="">
                                        Ke
                                    </div>
                                    <div class="">
                                        : {{ $data->tempat }}
                                    </div>
                                    <div>
                                        Pada Tanggal
                                    </div>
                                    <div>
                                        : {{ $berangkat }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center mb-1">
                                <div class="text-center ">
                                    <div class="mb-1">Direktur UOBK RSUD Malangbong</div>
                                    <div class="mt-16">
                                        <div class="font-bold underline">dr. Hj. Novita Silvana Mua</div>
                                        <div class="font-bold ">NIP: 197711052014122001</div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="border">
                            <div class="flex">

                                <div class="me-3">
                                    II.
                                </div>
                                <div class="grid w-full grid-cols-[40%_60%] ">
                                    <div class="">
                                        Tiba Di
                                    </div>
                                    <div class="">
                                        : {{ $data->tempat }}
                                    </div>

                                    <div>
                                        Pada Tanggal
                                    </div>
                                    <div>
                                        : {{ $berangkat }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center ">
                                <div class="text-center ">
                                    <div class="mt-24">
                                        <div class="font-bold border border-black w-44"></div>
                                        <div class="mt-1 font-bold text-left w-44">NIP: </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="border">
                            <div class="flex">

                                <div class="me-3">

                                </div>
                                <div class="grid w-full grid-cols-[40%_60%] ">
                                    <div class="">
                                        Berangkat dari
                                    </div>
                                    <div class="">
                                        : {{ $data->tempat }}
                                    </div>
                                    <div class="">
                                        Ke
                                    </div>
                                    <div class="">
                                        : UOBK RSUD Malangbong
                                    </div>

                                    <div>
                                        Pada Tanggal
                                    </div>
                                    <div>
                                        : {{ $berangkat }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center ">
                                <div class="text-center">
                                    <div class="mt-20">
                                        <div class="font-bold border border-black w-44"></div>
                                        <div class="mt-1 font-bold text-left w-44">NIP: </div>
                                    </div>
                                </div>
                            </div>
                        </td>

                    </tr>
                    <tr>
                        <td class="border">
                            <div class="flex">

                                <div class="me-3">
                                    III.
                                </div>
                                <div class="grid w-full grid-cols-[40%_60%] ">
                                    <div class="">
                                        Tiba Di
                                    </div>
                                    <div class="">
                                        : {{ $data->tempat }}
                                    </div>

                                    <div>
                                        Pada Tanggal
                                    </div>
                                    <div>
                                        : {{ $berangkat }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center ">
                                <div class="text-center ">
                                    <div class="mt-24">
                                        <div class="font-bold border border-black w-44"></div>
                                        <div class="mt-1 font-bold text-left w-44">NIP: </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="border">
                            <div class="flex">

                                <div class="me-3">

                                </div>
                                <div class="grid w-full grid-cols-[40%_60%] ">
                                    <div class="">
                                        Berangkat dari
                                    </div>
                                    <div class="">
                                        : {{ $data->tempat }}
                                    </div>
                                    <div class="">
                                        Ke
                                    </div>
                                    <div class="">
                                        : UOBK RSUD Malangbong
                                    </div>

                                    <div>
                                        Pada Tanggal
                                    </div>
                                    <div>
                                        : {{ $berangkat }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center ">
                                <div class="text-center">
                                    <div class="mt-20">
                                        <div class="font-bold border border-black w-44"></div>
                                        <div class="mt-1 font-bold text-left w-44">NIP: </div>
                                    </div>
                                </div>
                            </div>
                        </td>

                    </tr>
                    <tr>
                        <td class="border">
                            <div class="flex">

                                <div class="me-3">
                                    IV.
                                </div>
                                <div class="grid w-full grid-cols-[40%_60%] ">
                                    <div class="">
                                        Tiba Di
                                    </div>
                                    <div class="">
                                        : {{ $data->tempat }}
                                    </div>

                                    <div>
                                        Pada Tanggal
                                    </div>
                                    <div>
                                        : {{ $berangkat }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center ">
                                <div class="text-center ">
                                    <div class="mt-24">
                                        <div class="font-bold border border-black w-44"></div>
                                        <div class="mt-1 font-bold text-left w-44">NIP: </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="border">
                            <div class="flex">

                                <div class="me-3">

                                </div>
                                <div class="grid w-full grid-cols-[40%_60%] ">
                                    <div class="">
                                        Berangkat dari
                                    </div>
                                    <div class="">
                                        : {{ $data->tempat }}
                                    </div>
                                    <div class="">
                                        Ke
                                    </div>
                                    <div class="">
                                        : UOBK RSUD Malangbong
                                    </div>

                                    <div>
                                        Pada Tanggal
                                    </div>
                                    <div>
                                        : {{ $berangkat }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center ">
                                <div class="text-center">
                                    <div class="mt-20">
                                        <div class="font-bold border border-black w-44"></div>
                                        <div class="mt-1 font-bold text-left w-44">NIP: </div>
                                    </div>
                                </div>
                            </div>
                        </td>

                    </tr>
                    <tr>
                        <td class="border">
                            <div class="flex">

                                <div class="me-3">
                                    V.
                                </div>
                                <div class="grid w-full grid-cols-[40%_60%] ">
                                    <div class="">
                                        Tiba Di
                                    </div>
                                    <div class="">
                                        : {{ $data->tempat }}
                                    </div>

                                    <div>
                                        Pada Tanggal
                                    </div>
                                    <div>
                                        : {{ $berangkat }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center ">
                                <div class="text-center ">
                                    <div class="mt-24">
                                        <div class="font-bold border border-black w-44"></div>
                                        <div class="mt-1 font-bold text-left w-44">NIP: </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="border">
                            <div class="flex">

                                <div class="me-3">

                                </div>
                                <div class="grid w-full grid-cols-[40%_60%] ">
                                    <div class="">
                                        Berangkat dari
                                    </div>
                                    <div class="">
                                        : {{ $data->tempat }}
                                    </div>
                                    <div class="">
                                        Ke
                                    </div>
                                    <div class="">
                                        : UOBK RSUD Malangbong
                                    </div>

                                    <div>
                                        Pada Tanggal
                                    </div>
                                    <div>
                                        : {{ $berangkat }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center ">
                                <div class="text-center">
                                    <div class="mt-20">
                                        <div class="font-bold border border-black w-44"></div>
                                        <div class="mt-1 font-bold text-left w-44">NIP: </div>
                                    </div>
                                </div>
                            </div>
                        </td>

                    </tr>
                    <tr>
                        <td class="border"></td>
                        <td class="border ">
                            <div class="flex">

                                <div class="me-8">
                                    VI.
                                </div>
                                <div class="grid w-full grid-cols-[40%_60%] mb-1">
                                    <div class="">
                                        Tiba Kembali di
                                    </div>
                                    <div class="">
                                        : UOBK RSUD Malangbong
                                    </div>
                                    <div class="">
                                        (Tempat kedudukan)
                                    </div>
                                    <div></div>
                                    <div>
                                        Pada Tanggal
                                    </div>
                                    <div>
                                        : {{ $berangkat }}
                                    </div>
                                </div>
                            </div>
                            <div class="ms-12">
                                Telah diperiksa dengan keterangan bahwa perjalanan tersebut <br>
                                atas benar dilakukan atas perintahnya dan semata-mata untuk <br>
                                kepentingan jabatan dalam waktu yang sesingkat-singkatnya.</div>

                        </td>
                    </tr>
                    <tr>
                        <td class="border"></td>
                        <td class="border ">
                            <div class="flex">

                                <div class="me-8">

                                </div>

                            </div>
                            <div class="flex justify-center mb-1">
                                <div class="text-center ">
                                    <div class="mb-1 font-bold">Pengguna Anggaran/ Kuasa <br>
                                        Penggunaan Anggaran</div>
                                    <div class="mt-16">
                                        <div class="font-bold underline">dr. Hj. Novita Silvana Mua</div>
                                        <div class="font-bold ">NIP: 197711052014122001</div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="border " colspan="2">
                            VII. Catatan Lain-Lain
                        </td>
                    </tr>
                    <tr>
                        <td class="border " colspan="2">
                            <div class="flex w-full">

                                <div>
                                    VIII.
                                </div>
                                <div>
                                    PERHATIAN : <br>
                                    Pejabat berwenang yang menerbitkan SPD, pegawai yang melakukan perjalanan dinas,
                                    para
                                    pejabat yang mengesahkan
                                    tanggal berangkatnya/tiba, serta bendahara pengeluaran bertanggung jawab berdasarkan
                                    peraturan - peraturan Keuangan
                                    Negara apabila negara menderita rugi akibat kesalahan, kelalaian dan kealpaannya.
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>



            </section>
        @endforeach


        {{-- sppd --}}


        {{-- visum --}}
        <section class="px-5 py-5 mt-24 h-[330mm] bg-white">
            <div class="text-xl font-extrabold text-center">
                VISUM HASIL KEGIATAN <br>
                (VHK)
            </div>
            <div class="grid w-full grid-cols-[40%_60%] text-left mt-2">
                <div>
                    Nama Kegiatan
                </div>
                <div>
                    : Rujukan Pasien {{ $nama }}
                </div>
                <div>
                    Tanggal
                </div>
                <div>
                    : {{ $berangkat }}
                </div>
                <div>
                    Tempat / Lokasi
                </div>
                <div>
                    : {{ $data->tempat }}
                </div>
                <div>
                    Hasil Kegiatan
                </div>
                <div>
                    :
                </div>
            </div>
            <div class="border border-black min-h-[700px] mt-4">

            </div>
            <div class="grid grid-cols-2">

                <div class="mt-2 text-center">
                    <div>
                        Pejabat yang dikunjungi
                    </div>

                    <div class="font-extrabold mt-28">
                        ...........................................................................
                    </div><br>
                    <p class="w-[50%] ms-20 font-extrabold  text-left">
                        NIP
                    </p>
                </div>
                <div class="text-center">
                    <div class="mt-2 text-center">
                        <div>
                            Garut, {{ $berangkat }} <br>
                            Petugas
                        </div>

                        <div class="mt-6 font-extrabold">
                            1. ...........................................................................
                        </div>
                        <div class="mt-6 font-extrabold ms-[70px]">
                            2. ...........................................................................
                        </div>
                        <div class="mt-6 font-extrabold ms-[140px]">
                            3. ..................................................................
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- kuitansi  --}}
        @foreach ($data->pns as $pns)
            <section class="bg-white h-[330mm] mb-10 p-10">
                <div class="flex justify-end py-2 border-t border-l border-r border-black">
                    <div class=" w-[30%] grid grid-cols-2">
                        <div>
                            Tanggal
                        </div>
                        <div>
                            :
                        </div>
                        <div>
                            Nomor

                        </div>
                        <div>:</div>
                    </div>
                </div>
                <table class="w-full border-b border-l border-r border-black">
                    <tr>
                        <td style="padding-left: 20px;" class="w-[30%]">
                            Kode Kegiatan
                        </td>
                        <td class="w-[2px]">:</td>
                        <td class="px-2">
                            1.02.01.2.10
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" class="w-[30%]">
                            Nama Kegiatan
                        </td>
                        <td class="w-[2px]">:</td>
                        <td class="w-full px-2 border border-black ms-2">
                            {{ $data->perihal }}

                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" colspan="3" class="py-2"></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" class="w-[30%] ">
                            Nama Sub Kegiatan
                        </td>
                        <td class="w-[2px]">:</td>
                        <td class="w-full px-2 border border-black ms-2">
                            {{ $data->perihal }} {{ $data->nama_rujukan }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" class="w-[30%]">
                            Sumber Dana
                        </td>
                        <td class="w-[2px]">:</td>
                        <td class="px-2">
                            Pendapatan Asli Daerah (PAD)
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" colspan="3" class="py-2"></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" class="font-bold text-center underline" colspan="3">
                            KWITANSI (TANDA PEMBAYARAN)
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" colspan="3" class="py-2"></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" class="w-[30%]">
                            SUDAH TERIMA DARI
                        </td>
                        <td class="w-[2px]">:</td>
                        <td class="w-full px-2 border border-black ms-2">
                            Bendahara Pengeluaran UOBK RSUD Malangbong pada Dinas Kesehatan Kabupaten Garut
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" colspan="3" class="py-2"></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" class="w-[30%]">
                            BANYAKNYA
                        </td>
                        <td class="w-[2px]">:</td>
                        <td class="w-full px-2 text-xl italic font-bold capitalize border border-black ms-2">
                            {{ $data->biaya_perdin_terbilang }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" colspan="3" class="py-2"></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" class="w-[30%] font-extrabold  px-2 border border-black ms-2">
                            {{ $data->biaya_perdin_rupiah }}

                        </td>
                        <td>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" colspan="3" class="py-2"></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" class="w-[30%]">
                            UNTUK PEMBAYARAN
                        <td class="w-[2px]">:</td>
                        </td>
                        <td class="px-2"> Biaya Perjalanan Dinas {{ $data->perihal }} ke {{ $data->tempat }} </td>

                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" colspan="3" class="py-2"></td>
                    </tr>
                </table>
                <div class="grid grid-cols-3">
                    <div class="text-center border-l border-black">
                        <p>

                            Mengetahui/Menyetujui <br>
                            Kuasa Pengguna Anggaran <br>
                            UOBK RSUD Malangbong <br>
                        </p>
                        <p class="mt-20 font-bold ">dr. Hj. Novita Silvana Mua <br>
                            NIP . 197711052014122001 </p>
                    </div>
                    <div class="text-center border-black border-x">
                        <p>
                            Lunas Dibayar Tgl: {{ $tgl_pulang }} <br>
                            Bendahara Pengeluaran <br>
                            UOBK RSUD Malangbong <br>
                        </p>
                        <p class="mt-20 font-bold ">Eka Komaswati,A.Md.KG <br>
                            NIP. 198609172019032011 </p>
                    </div>
                    <div class="text-center border-r border-black">
                        <p><br>
                            Garut, {{ $tgl_pulang }} <br>
                            Yang Menerima <br>
                        </p>
                        <p class="mt-20 font-bold "> {{ $pns->nama }} <br>
                            {{ $pns->nip }} </p>
                    </div>
                </div>
                <div>
                    <h2 class="py-2 font-bold text-center border-t border-black border-x ">
                        Daftar Terima Uang Perjalanan Dinas
                    </h2>
                    <table class="w-full">
                        <tr>
                            <td class="font-bold text-center border border-black">
                                No
                            </td>
                            <td class="font-bold text-center border border-black">
                                Nama
                            </td>
                            <td class="font-bold text-center border border-black">
                                NIP
                            </td>
                            <td class="font-bold text-center border border-black">
                                Pangkat/Golongan
                            </td>
                            <td class="font-bold text-center border border-black">
                                Jumlah Diterima
                            </td>
                            <td class="font-bold text-center border border-black">
                                Tanda Tangan
                            </td>
                        </tr>
                        <tr>
                            <td class="pt-8 text-center border border-black">
                                1

                            </td>
                            <td class="pt-8 text-center border border-black">
                                {{ $pns->nama }}
                            </td>
                            <td class="pt-8 text-center border border-black">
                                {{ $pns->nip }}

                            </td>
                            <td class="pt-8 text-center border border-black">
                                {{ $pns->pangkat_golongan }}
                            </td>
                            <td class="pt-8 text-center border border-black">
                                {{ $data->biaya_perdin_rupiah }}

                            </td>
                            <td class="pt-8 text-left border border-black">
                                1.

                            </td>
                        </tr>
                        <tr>
                            <td class="pt-8 text-center border border-black">


                            </td>
                            <td class="pt-8 text-center border border-black">

                            </td>
                            <td class="pt-8 text-center border border-black">


                            </td>
                            <td class="pt-8 text-center border border-black">


                            </td>
                            <td class="pt-8 text-center border border-black">



                            </td>
                            <td class="pt-8 text-left border border-black">

                            </td>
                        </tr>
                        <tr>
                            <td class="pt-8 text-center border border-black">


                            </td>
                            <td class="pt-8 text-center border border-black">

                            </td>
                            <td class="pt-8 text-center border border-black">


                            </td>
                            <td class="pt-8 text-center border border-black">


                            </td>
                            <td class="pt-8 text-center border border-black">



                            </td>
                            <td class="pt-8 text-left border border-black">

                            </td>
                        </tr>
                        <tr>
                            <td class="pt-8 text-center border border-black">


                            </td>
                            <td class="pt-8 text-center border border-black">

                            </td>
                            <td class="pt-8 text-center border border-black">


                            </td>
                            <td class="pt-8 text-center border border-black">


                            </td>
                            <td class="pt-8 text-center border border-black">



                            </td>
                            <td class="pt-8 text-left border border-black">

                            </td>
                        </tr>
                        <tr>
                            <td class="pt-8 text-center border border-black">


                            </td>
                            <td class="pt-8 text-center border border-black">

                            </td>
                            <td class="pt-8 text-center border border-black">


                            </td>
                            <td class="pt-8 text-center border border-black">


                            </td>
                            <td class="pt-8 text-center border border-black">



                            </td>
                            <td class="pt-8 text-left border border-black">

                            </td>
                        </tr>
                        <tr>

                            <td colspan="2" class="font-bold text-center border border-black ">
                                Jumlah
                            </td>
                            <td class="text-center border border-black ">


                            </td>
                            <td class="text-center border border-black ">


                            </td>
                            <td class="text-center border border-black ">

                                {{ $data->biaya_perdin_rupiah }}


                            </td>
                            <td class="text-left border border-black ">

                            </td>
                        </tr>
                        <tr>
                            <td colspan="5"
                                class="px-2 italic font-bold text-center capitalize bg-yellow-100 border border-black">
                                {{ $data->biaya_perdin_terbilang }}
                            </td>
                            <td class="border border-black bg-slate-400"></td>
                        </tr>
                        <tr class="border-l border-r border-black">
                            <td class="ps-4" colspan="3">
                                Untuk : Biaya Perjalanan Dinas kegiatan {{ $data->perihal }} Ke {{ $data->tempat }}
                            </td>
                            <td>

                            </td>
                        </tr>
                        <tr class="border-l border-r border-black">
                            <td class="pt-5 ps-4" colspan="3">
                                Ket: (Surat Tugas, SPD, Visum Hasil Kegiatan Terlampir) </td>
                            <td>

                            </td>
                        </tr>
                        <tr class="border-b border-l border-r border-black">
                            <td class="" colspan="3">
                                <div class="flex justify-center mb-1">
                                    <div class="text-center ">
                                        <div class="mb-1 ">Mengetahui <br> Kuasa Pengguna Anggaran <br>
                                            UOBK RSUD Malangbong </div>
                                        <div class="mt-16">
                                            <div class="font-bold underline">dr. Hj. Novita Silvana Mua</div>
                                            <div class="">NIP: 197711052014122001</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="flex justify-center mb-1">
                                    <div class="text-center ">
                                        <div class="mb-1 ">Lunas dibayar : <br> Bendahara Pengeluaran Pembantu
                                            <br>
                                            UOBK RSUD Malangbong
                                        </div>
                                        <div class="mt-16">
                                            <div class="font-bold underline">Eka Komaswati,A.Md.KG </div>
                                            <div class="">NIP. 198609172019032011 </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </section>
            <section class="bg-white h-[330mm] mb-10 px-10">
                <div class="w-full text-xl font-extrabold text-center">
                    RINCIAN KEBUTUHAN BIAYA PERJALANAN DINAS
                </div>
                <table class="w-full mt-5">
                    <tr>
                        <td style="padding-left: 20px;" class="w-[30%]">
                            Lampiran SPPD Nomor
                        </td>
                        <td class="w-[2px]">:</td>
                        <td class="px-2">
                            800.1.11.1/....../RSUD_Malangbong/2026
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" class="w-[30%]">
                            Tanggal
                        </td>
                        <td class="w-[2px]">:</td>
                        <td class="w-full px-2 ms-2">
                            {{ $tgl_pulang }}

                        </td>
                    </tr>


                </table>

                <div>

                    <table class="w-full">
                        <tr>
                            <td class="font-bold text-center border border-black">
                                NO
                            </td>
                            <td class="font-bold text-center border border-black">
                                PERINCIAN BIAYA
                            </td>
                            <td class="font-bold text-center border border-black">
                                JUMLAH
                            </td>
                            <td class="font-bold text-center border border-black">
                                KETERANGAN
                            </td>

                        </tr>
                        <tr>
                            <td class="pt-1 border border-black ps-2">
                                A

                            </td>
                            <td class="pt-1 border border-black ps-2">
                                Uang Harian
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                        </tr>
                        <tr>
                            <td class="pt-1 border border-black ps-2">


                            </td>
                            <td class="pt-1 border border-black ps-2">
                                Uang Makan
                            </td>
                            <td class="pt-1 border border-black ps-2">
                                Rp -
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                        </tr>
                        <tr>
                            <td class="pt-1 border border-black ps-2">


                            </td>
                            <td class="pt-1 border border-black ps-2">
                                Uang Transport Lokal
                            </td>
                            <td class="pt-1 border border-black ps-2">
                                {{ $data->biaya_perdin_rupiah }}
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                        </tr>
                        <tr>
                            <td class="pt-1 border border-black ps-2">


                            </td>
                            <td class="pt-1 border border-black ps-2">
                                Uang Saku
                            </td>
                            <td class="pt-1 border border-black ps-2">
                                Rp -
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                        </tr>
                        <tr>
                            <td class="pt-1 border border-black ps-2">

                                B
                            </td>
                            <td class="pt-1 border border-black ps-2">
                                Uang Refresentasi
                            </td>
                            <td class="pt-1 border border-black ps-2">
                                Rp -
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                        </tr>
                        <tr>
                            <td class="pt-1 border border-black ps-2">

                                C
                            </td>
                            <td class="pt-1 border border-black ps-2">
                                Fasilitas Angkutan
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                        </tr>
                        <tr>
                            <td class="pt-1 border border-black ps-2">

                                D
                            </td>
                            <td class="pt-1 border border-black ps-2">
                                - Tiket Pesawat Udara
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                        </tr>
                        <tr>
                            <td class="pt-1 border border-black ps-2">


                            </td>
                            <td class="pt-1 border border-black ps-2">
                                - Tiket Kapal Laut
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                        </tr>
                        <tr>
                            <td class="pt-1 border border-black ps-2">


                            </td>
                            <td class="pt-1 border border-black ps-2">
                                - Kereta Api
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                        </tr>
                        <tr>
                            <td class="pt-1 border border-black ps-2">
                                F

                            </td>
                            <td class="pt-1 border border-black ps-2">
                                Akomodasi/Penginapan
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                        </tr>

                        <tr>
                            <td class="pt-1 border border-black ps-2">


                            </td>
                            <td class="pt-1 font-bold border border-black ps-2">
                                DITETAPKAN SEJUMLAH
                            </td>
                            <td class="pt-1 font-bold border border-black ps-2">
                                {{ $data->biaya_perdin_rupiah }}
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                        </tr>
                        <tr>

                            <td colspan="2" class="pt-1 font-bold text-center border border-black ps-2">
                                Terbilang :
                            </td>
                            <td colspan="2" class="pt-1 italic font-bold capitalize border border-black ps-2">
                                {{ $data->biaya_perdin_terbilang }}
                            </td>
                        </tr>

                    </table>
                    <div class="grid grid-cols-2 mt-2">

                        <div class="text-center ">
                            <p>

                                Bendahara Pengeluaran Pembantu<br>
                                UOBK RSUD Malangbong <br>
                            </p>
                            <p class="mt-16 font-bold ">Eka Komaswati,A.Md.KG <br>
                                NIP. 198609172019032011 </p>
                        </div>
                        <div class="text-center ">
                            <p>
                                Garut, {{ $tgl_pulang }} <br>
                                Yang Menerima <br>
                            </p>
                            <p class="mt-16 font-bold "> {{ $pns->nama }} <br>
                                NIP. {{ $pns->nip }} </p>
                        </div>
                    </div>
                    <div class="mt-4 mb-2 font-bold text-center">
                        PERHITUNGAN SPPD RAMPUNG
                    </div>
                    <div class="w-full px-20">
                        <table class="w-full border">
                            <tr>
                                <td>
                                    Ditetapkan sejumlah
                                </td>
                                <td>
                                    Rp
                                </td>
                                <td>
                                    {{ $data->biaya_perdin_rupiah }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Yang telah dibayar semula
                                </td>
                                <td>
                                    Rp
                                </td>
                                <td>
                                    {{ $data->biaya_perdin_rupiah }}

                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Sisa Kurang / lebih
                                </td>
                                <td>
                                    Rp
                                </td>
                                <td>
                                    -
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="mt-2 text-center">
                        <p>
                            Kuasa Pengguna Anggaran <br>
                        </p>
                        <p class="mt-16 font-bold ">dr. Hj. Novita Silvana Mua <br>
                            NIP . 197711052014122001 </p>
                    </div>
                </div>
                <div class="w-full pt-1 mt-1 text-xl font-extrabold text-center border-t border-black">
                    DAFTAR PENGELUARAN RIIL
                </div>
                <div>
                    Yang bertanda tangan di bawah ini
                </div>
                <table class="w-full mt-2">
                    <tr>
                        <td style="padding-left: 20px;" class="w-[30%]">
                            Nama
                        </td>
                        <td class="w-[2px]">:</td>
                        <td class="px-2">
                            dr. Hj. Novita Silvana Mua
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" class="w-[30%]">
                            NIP
                        </td>
                        <td class="w-[2px]">:</td>
                        <td class="w-full px-2 ms-2">
                            197711052014122001
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" class="w-[30%]">
                            Jabatan
                        </td>
                        <td class="w-[2px]">:</td>
                        <td class="w-full px-2 ms-2">
                            Direktur UOBK RSUD Malangbong
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;" colspan="3" class="py-2"></td>
                    </tr>

                </table>
                <div>
                    Berdasarkan Surat Perjalanan Dinas (SPD) No: 800.1.11.1/......./RSUD_Malangbong/2026
                    {{ $berangkat }}, dengan ini kami
                    menyatakan dengan sesungguhnya bahwa :
                </div>
                <div class="my-2">
                    1. Biaya transpor pegawai dan/atau biaya penginapan di bawah ini yang tidak dapat diperoleh
                    bukti-bukti pengeluarannya, meliputi :
                </div>
                <div>

                    <table class="w-full">
                        <tr>
                            <td class="font-bold text-center border border-black">
                                NO
                            </td>
                            <td class="font-bold text-center border border-black">
                                RINCIAN BIAYA
                            </td>
                            <td class="font-bold text-center border border-black">
                                JUMLAH ( RP )
                            </td>
                            <td class="font-bold text-center border border-black">
                                KETERANGAN
                            </td>

                        </tr>

                        <tr>
                            <td class="pt-1 border border-black ps-2">

                                1.
                            </td>
                            <td class="pt-1 border border-black ps-2">
                                Uang Makan
                            </td>
                            <td class="pt-1 border border-black ps-2">
                                Rp -
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                        </tr>
                        <tr>
                            <td class="pt-1 border border-black ps-2">

                                2.
                            </td>
                            <td class="pt-1 border border-black ps-2">
                                Uang Transport Lokal
                            </td>
                            <td class="pt-1 border border-black ps-2">
                                {{ $data->biaya_perdin_rupiah }}
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                        </tr>
                        <tr>
                            <td class="pt-1 border border-black ps-2">
                                3.

                            </td>
                            <td class="pt-1 border border-black ps-2">
                                Uang Saku
                            </td>
                            <td class="pt-1 border border-black ps-2">
                                Rp -
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                        </tr>

                        <tr>
                            <td class="pt-1 border border-black ps-2">

                                4.
                            </td>
                            <td class="pt-1 border border-black ps-2">
                                Fasilitas Angkutan
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                        </tr>
                        <tr>
                            <td class="pt-1 border border-black ps-2">


                            </td>
                            <td class="pt-1 font-bold border border-black ps-2">
                                DITETAPKAN SEJUMLAH
                            </td>
                            <td class="pt-1 font-bold border border-black ps-2">
                                {{ $data->biaya_perdin_rupiah }}
                            </td>
                            <td class="pt-1 border border-black ps-2">
                            </td>
                        </tr>
                        <tr>

                            <td colspan="2" class="pt-1 font-bold text-center border border-black ps-2">
                                Terbilang :
                            </td>
                            <td colspan="2" class="pt-1 italic font-bold capitalize border border-black ps-2">
                                {{ $data->biaya_perdin_terbilang }}
                            </td>
                        </tr>
                    </table>
                    <div class="my-2">
                        2. Jumlah uang tersebut pada angka 1 diatas benar-benar dikeluarkan untuk pelaksanaan kelebihan
                        atas pembayaran, kami
                        bersedia untuk menyetorkan kelebihan tersebut ke Kas Daerah. perjalanan dinas dimaksud dan
                        apabila di kemudian hari
                        terdapat kelebihan atas pembayaran, Kami bersedia untuk menyetorkan kelebihan tersebut ke Kas
                        Daerah.
                    </div>
                    <div>
                        Demikian pernyataan ini kami buat dengan sebenarnya, untuk dipergunakan sebagaimana mestinya.
                    </div>
                    <div class="grid grid-cols-2 mt-2">
                        <div class="text-center ">
                            <p>

                                Mengetahui/Menyetujui <br>
                                Kuasa Pengguna Anggaran <br>
                            </p>
                            <p class="mt-16 font-bold ">dr. Hj. Novita Silvana Mua <br>
                                NIP . 197711052014122001 </p>
                        </div>
                        <div class="text-center ">
                            <p>
                                Garut, {{ $tgl_pulang }} <br>
                                Yang Menerima <br>
                            </p>
                            <p class="mt-16 font-bold "> {{ $pns->nama }} <br>
                                NIP. {{ $pns->nip }} </p>
                        </div>
                    </div>

                </div>
            </section>
        @endforeach
    </div>


</div>
