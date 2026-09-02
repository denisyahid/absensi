 <div>
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
                     border border-black-collapse: collapse;
                     width: 100%;
                 }


                 .sppd td {
                     border border-black: 1px solid #000;
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
     <section class="bg-white  area-printer">
         <x-kop-surat />
         <div>

             <div class="-mt-3 text-xl font-extrabold text-center">
                 LEMBAR BUKTI PELAYANAN AMBULANCE ( UOBK ) RUMAH SAKIT <BR /> UMUM DAERAH MALANGBONG
             </div>

         </div>
         <div class="p-10">
             <table class="w-full text-lg border border-black-0">
                 <tr class="">
                     <td class="py-0 text-center border border-black">1</td>
                     <td class="py-0 text-center border border-black"> IDENTITAS PASIEN </td>
                     <td class="py-0 text-center border border-black"> TANDA TANGAN PASIEN</td>
                 </tr>
                 <tr class="">
                     <td class="py-0 border border-black"></td>
                     <td class="py-0 align-top border border-black">
                         <TABLE class="text-sm">
                             <TR>
                                 <TD class="py-1">
                                     NAMA PASIEN
                                 </TD>
                                 <td class="py-1">
                                     :
                                 </td>
                             </tr>
                             <tr>

                                 <td class="py-1">
                                     NO KARTU BPJS KESEHATAN
                                 </td>
                                 <td class="py-1">
                                     :
                                 </td>
                             </TR>
                             <tr>
                                 <td class="py-1">
                                     NIK
                                 </td>
                                 <td class="py-1">
                                     :
                                 </td>
                             </tr>
                         </TABLE>
                     </td>
                     <td class="pt-20 text-center align-bottom border border-black"> <br>
                         (.............................) <br> <span class="text-xs">nama jelas
                             &
                             stempel
                             Faskes</span></td>

                 </tr>
                 <tr class="">
                     <td class="px-2 py-0 text-center border border-black">2</td>
                     <td class="py-0 text-center border border-black"> KETERANGAN MEDIS / KETERANGAN PENGIRIMAN </td>
                     <td class="py-0 text-center border border-black"> TANDA TANGAN DOKTER <BR /> PENANGGUNG JAWAB</td>
                 </tr>
                 <tr class="">
                     <td class="py-0 border border-black"></td>
                     <td class="py-0 align-top border border-black">
                         <TABLE class="text-sm">
                             <TR>
                                 <TD class="py-1">
                                     DIAGNOSA
                                 </TD>
                                 <td class="py-1">
                                     :
                                 </td>
                             </tr>
                             <tr>

                                 <td class="pb-14">
                                     INDIKASI RUJUK
                                 </td>
                                 <td class="align-top">
                                     :
                                 </td>
                             </TR>
                             <tr>
                                 <td class="py-1">
                                     DOKTER PENDAMPING
                                 </td>
                                 <td class="py-1">
                                     :
                                 </td>
                             </tr>
                             <tr>
                                 <td class="py-1">
                                     PERAWAT PENDAMPING
                                 </td>
                                 <td class="py-1">
                                     :
                                 </td>
                             </tr>
                             <tr>
                                 <td class="py-1">
                                     NAMA DRIVER
                                 </td>
                                 <td class="py-1">
                                     :
                                 </td>
                             </tr>
                             <tr>
                                 <td class="py-1">
                                     TANGGAL BERANGKAT RUJUK
                                 </td>
                                 <td class="py-1">
                                     :
                                 </td>
                             </tr>
                             <tr>
                                 <td class="py-1">
                                     AMBULANCE YANG DI RUJUK
                                 </td>
                                 <td class="py-1">
                                     :
                                 </td>
                             </tr>
                         </TABLE>
                     </td>
                     <td class="pt-20 text-center align-bottom border border-black"> <br>
                         (.............................) <br> <span class="text-xs">nama jelas
                             &
                             stempel
                             Faskes</span> <br>
                         <p class="mb-20">PERAWAT PEDAMPING</p>
                         <p>(............................)</p>
                         <span class="text-xs">nama jelas
                             &
                             stempel
                             Faskes</span> <br>
                     </td>

                 </tr>
                 <tr class="">
                     <td class="px-2 py-0 text-center border border-black">3</td>
                     <td class="py-0 text-center border border-black"> FASILITAS KESEHATAN TUJUAN </td>
                     <td class="py-0 text-center border border-black"> FASKES PENERIMA </td>
                 </tr>
                 <tr class="">
                     <td class="py-0 border border-black"></td>
                     <td class="py-0 align-top border border-black">
                         <TABLE class="text-sm">
                             <TR>
                                 <TD class="py-1">
                                     NAMA FASKES
                                 </TD>
                                 <td class="py-1">
                                     :
                                 </td>
                             </tr>
                             <tr>

                                 <td class="pb-14">
                                     PERAWAT / DOKTER PENERIMA :
                                 </td>
                                 <td class="align-top">
                                     :
                                 </td>
                             </TR>

                         </TABLE>
                     </td>
                     <td class="pt-20 text-center align-bottom border border-black"> <br>
                         (.............................) <br> <span class="text-xs">nama jelas
                             &
                             stempel
                             Faskes</span> <br>

                     </td>

                 </tr>
                 <tr class="border border-black">
                     <td colspan="3" class="pb-20 align-top">CATATAN LAINNYA</td>
                 </tr>

             </table>
         </div>
     </section>
 </div>
