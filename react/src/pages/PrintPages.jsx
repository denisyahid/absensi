import { useCallback, useEffect, useMemo, useState } from 'react';
import { ArrowLeft, LoaderCircle, Printer } from 'lucide-react';
import { Link, useParams, useSearchParams } from 'react-router-dom';
import { api, queryString } from '../api';
import { formatDate, formatTime } from '../components/Common';
import { useAuth } from '../auth';

function PrintToolbar({ title, back = '/' }) {
  return <div className="print-toolbar only-screen"><Link to={back}><ArrowLeft size={17} /> Kembali</Link><strong>{title}</strong><button onClick={() => window.print()}><Printer size={17} /> Cetak / Simpan PDF</button></div>;
}

function PrintLoading() { return <div className="print-loading"><LoaderCircle className="spin" /> Menyiapkan dokumen…</div>; }
function PrintError({ message }) { return <div className="print-error"><strong>Dokumen tidak dapat dimuat</strong><p>{message}</p></div>; }

function Letterhead({ compact = false }) {
  return <header className={`letterhead ${compact ? 'compact' : ''}`}>
    <img src="/garut.jpg" alt="Lambang Kabupaten Garut" />
    <div><h2>PEMERINTAH KABUPATEN GARUT</h2><h3>DINAS KESEHATAN</h3><h1>UOBK RUMAH SAKIT UMUM DAERAH MALANGBONG</h1><p>Jl. Raya Malangbong–Ciawi, Sukamanah, Kec. Malangbong, Kabupaten Garut, Jawa Barat 44188</p><p>Email: rsudmalangbong@garutkab.go.id</p></div>
  </header>;
}

function Signatory({ place = 'Malangbong', label = 'Direktur UOBK RSUD Malangbong' }) {
  return <div className="signature-block"><p>{place}, {formatDate(new Date().toISOString())}</p><p>{label}</p><div className="signature-space" /><strong>dr. Hj. Novita Silvana Mua</strong><span>NIP. 197711052014122001</span></div>;
}

function DocumentPage({ children, className = '' }) { return <section className={`print-sheet ${className}`}>{children}</section>; }

export function ReferralPrintPage({ variant = 'standard' }) {
  const { id } = useParams();
  const [item, setItem] = useState(null);
  const [error, setError] = useState('');
  useEffect(() => { api.get(`/referrals/${id}`).then((response) => setItem(response.data)).catch((caught) => setError(caught.message)); }, [id]);
  if (error) return <><PrintToolbar title="Cetak SPPD" back="/perjalanan-dinas" /><PrintError message={error} /></>;
  if (!item) return <PrintLoading />;
  return <div className="print-document">
    <PrintToolbar title={variant === 'srikandi' ? 'SPPD Srikandi' : 'Surat Perjalanan Dinas'} back="/perjalanan-dinas" />
    {variant === 'srikandi' ? <SrikandiLetter item={item} /> : <AssignmentLetter item={item} />}
    <TravelOrder item={item} variant={variant} />
    {item.participants.map((participant, index) => <ExpenseReceipt item={item} participant={participant} key={`${participant.user_id}-${index}`} />)}
  </div>;
}

function AssignmentLetter({ item }) {
  return <DocumentPage><Letterhead /><div className="document-title"><h1>SURAT PERINTAH TUGAS</h1><p>Nomor: {item.nomor_surat}</p></div>
    <table className="letter-meta"><tbody><tr><th>Dasar</th><td>{item.dasar_surat || 'Kebutuhan pelayanan rujukan pasien UOBK RSUD Malangbong.'}</td></tr><tr><th>Menimbang</th><td>{item.menimbang || 'Bahwa untuk kelancaran pelayanan perlu menugaskan pegawai sebagaimana tercantum di bawah ini.'}</td></tr></tbody></table>
    <h3 className="center-command">MEMERINTAHKAN</h3>
    <table className="official-table"><thead><tr><th>No.</th><th>Nama / NIP</th><th>Pangkat / Golongan</th><th>Jabatan</th></tr></thead><tbody>{item.participants.map((person, index) => <tr key={person.user_id}><td>{index + 1}</td><td><strong>{person.pns_nama || person.user_name}</strong>{person.pns_nip && <small>NIP. {person.pns_nip}</small>}</td><td>{person.pangkat_golongan || '—'}</td><td>{person.pns_jabatan || person.user_jabatan || 'Pegawai UOBK RSUD Malangbong'}</td></tr>)}</tbody></table>
    <table className="letter-meta assignment-details"><tbody><tr><th>Untuk</th><td>{item.perihal} ke {item.nama_rujukan}</td></tr><tr><th>Tujuan</th><td>{item.tempat}, {item.alamat_rujukan}</td></tr><tr><th>Waktu</th><td>{formatDate(item.tanggal_berangkat)} s.d. {formatDate(item.tanggal_kembali)}, {item.waktu}</td></tr><tr><th>Transportasi</th><td>{item.alat_angkut}</td></tr></tbody></table>
    <p className="document-note">Demikian surat perintah tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab dan setelah selesai agar menyampaikan laporan.</p><Signatory />
  </DocumentPage>;
}

function SrikandiLetter({ item }) {
  return <DocumentPage><Letterhead /><div className="srikandi-header"><div><strong>SURAT TUGAS</strong><span>NOMOR {item.nomor_surat}</span></div><div className="qr-placeholder">QR<br/><small>SRiKANDi</small></div></div>
    <p className="paragraph-indent">Dalam rangka {item.perihal.toLowerCase()} ke <strong>{item.nama_rujukan}</strong>, dengan ini Direktur UOBK RSUD Malangbong menugaskan:</p>
    <table className="official-table"><thead><tr><th>No.</th><th>Nama</th><th>NIP</th><th>Jabatan</th></tr></thead><tbody>{item.participants.map((person, index) => <tr key={person.user_id}><td>{index + 1}</td><td>{person.pns_nama || person.user_name}</td><td>{person.pns_nip || '—'}</td><td>{person.pns_jabatan || person.user_jabatan || 'Pegawai'}</td></tr>)}</tbody></table>
    <table className="letter-meta assignment-details"><tbody><tr><th>Hari / Tanggal</th><td>{formatDate(item.tanggal_berangkat, { weekday: 'long' })} s.d. {formatDate(item.tanggal_kembali, { weekday: 'long' })}</td></tr><tr><th>Waktu</th><td>{item.waktu}</td></tr><tr><th>Tempat</th><td>{item.tempat}</td></tr><tr><th>Alamat</th><td>{item.alamat_rujukan}</td></tr></tbody></table>
    <p className="paragraph-indent">Surat tugas ini disampaikan kepada yang bersangkutan untuk dilaksanakan sebagaimana mestinya.</p>
    <div className="digital-signature"><div className="qr-placeholder">QR<br/><small>TTD-el</small></div><div><p>Ditetapkan di Malangbong<br/>pada tanggal {formatDate(item.tanggal_surat || item.created_at)}</p><strong>DIREKTUR UOBK RSUD MALANGBONG</strong><div className="signature-space small"/><strong>dr. Hj. Novita Silvana Mua</strong><span>NIP. 197711052014122001</span></div></div>
    <footer className="electronic-note">Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh BSrE.</footer>
  </DocumentPage>;
}

function TravelOrder({ item, variant }) {
  const days = Math.max(1, Math.round((new Date(`${item.tanggal_kembali}T00:00:00`) - new Date(`${item.tanggal_berangkat}T00:00:00`)) / 86400000) + 1);
  return <DocumentPage><Letterhead compact /><div className="document-title"><h1>SURAT PERJALANAN DINAS (SPD)</h1><p>Nomor: {item.nomor_surat}</p></div>
    <table className="spd-table"><tbody>
      <tr><th>1.</th><td>Pejabat pembuat komitmen</td><td>Direktur UOBK RSUD Malangbong</td></tr>
      <tr><th>2.</th><td>Nama pegawai yang diperintah</td><td>{item.participants.map((p) => p.pns_nama || p.user_name).join('; ')}</td></tr>
      <tr><th>3.</th><td>a. Pangkat dan golongan<br/>b. Jabatan / Instansi</td><td>a. {item.participants.map((p) => p.pangkat_golongan || '—').join('; ')}<br/>b. UOBK RSUD Malangbong</td></tr>
      <tr><th>4.</th><td>Maksud perjalanan dinas</td><td>{item.perihal}</td></tr>
      <tr><th>5.</th><td>Alat angkut yang digunakan</td><td>{item.alat_angkut}</td></tr>
      <tr><th>6.</th><td>a. Tempat berangkat<br/>b. Tempat tujuan</td><td>a. UOBK RSUD Malangbong<br/>b. {item.tempat}, {item.alamat_rujukan}</td></tr>
      <tr><th>7.</th><td>a. Lamanya perjalanan dinas<br/>b. Tanggal berangkat<br/>c. Tanggal harus kembali</td><td>a. {days} ({terbilang(days)}) hari<br/>b. {formatDate(item.tanggal_berangkat)}<br/>c. {formatDate(item.tanggal_kembali)}</td></tr>
      <tr><th>8.</th><td>Pembebanan anggaran</td><td>Belanja perjalanan dinas UOBK RSUD Malangbong</td></tr>
      <tr><th>9.</th><td>Keterangan lain-lain</td><td>{variant === 'srikandi' ? 'Naskah dinas elektronik melalui aplikasi SRIKANDI.' : 'Dilaksanakan sesuai ketentuan yang berlaku.'}</td></tr>
    </tbody></table><Signatory />
    <div className="approval-grid"><div><p>Berangkat dari: UOBK RSUD Malangbong<br/>Pada tanggal: {formatDate(item.tanggal_berangkat)}</p><strong>Direktur</strong><div className="signature-space small"/><strong>dr. Hj. Novita Silvana Mua</strong></div><div><p>Tiba di: {item.tempat}<br/>Pada tanggal: {formatDate(item.tanggal_berangkat)}</p><strong>Pejabat yang mengesahkan</strong><div className="signature-space small"/><span>(........................................)</span></div></div>
  </DocumentPage>;
}

function ExpenseReceipt({ item, participant }) {
  const amount = Number(item.biaya_perdin || 0);
  return <DocumentPage><Letterhead compact /><div className="document-title"><h1>RINCIAN BIAYA PERJALANAN DINAS</h1><p>Lampiran SPD Nomor: {item.nomor_surat}</p></div>
    <table className="letter-meta"><tbody><tr><th>Nama</th><td>{participant.pns_nama || participant.user_name}</td></tr><tr><th>NIP</th><td>{participant.pns_nip || '—'}</td></tr><tr><th>Tujuan</th><td>{item.nama_rujukan} — {item.tempat}</td></tr></tbody></table>
    <table className="official-table expense-table"><thead><tr><th>No.</th><th>Perincian biaya</th><th>Jumlah</th><th>Keterangan</th></tr></thead><tbody><tr><td>1</td><td>Uang harian / biaya perjalanan dinas</td><td>{rupiah(amount)}</td><td>Sesuai ketentuan</td></tr><tr><td>2</td><td>Transportasi</td><td>—</td><td>{item.alat_angkut}</td></tr><tr className="total-row"><td colSpan="2">JUMLAH</td><td>{rupiah(amount)}</td><td /></tr><tr><td colSpan="4"><strong>Terbilang: {capitalize(terbilang(amount))} rupiah</strong></td></tr></tbody></table>
    <div className="receipt-box"><h2>KWITANSI</h2><p>Sudah terima dari: Bendahara Pengeluaran UOBK RSUD Malangbong</p><p>Uang sebesar: <strong>{capitalize(terbilang(amount))} rupiah</strong></p><p>Untuk pembayaran: Biaya perjalanan dinas dalam rangka {item.perihal.toLowerCase()} ke {item.nama_rujukan}.</p></div>
    <div className="dual-signatures"><div><p>Setuju dibayar,<br/>Pejabat Pembuat Komitmen</p><div className="signature-space"/><strong>Hadi Hadiansyah, AMK</strong><span>NIP. 198104272008011004</span></div><div><p>Malangbong, {formatDate(item.tanggal_kembali)}<br/>Yang menerima,</p><div className="signature-space"/><strong>{participant.pns_nama || participant.user_name}</strong><span>{participant.pns_nip ? `NIP. ${participant.pns_nip}` : ''}</span></div></div>
  </DocumentPage>;
}

export function LogbookPrintPage() {
  const [params] = useSearchParams();
  const { user } = useAuth();
  const period = { month: Number(params.get('month')) || new Date().getMonth() + 1, year: Number(params.get('year')) || new Date().getFullYear(), user_id: params.get('user_id') || '' };
  const [data, setData] = useState(null); const [error, setError] = useState('');
  useEffect(() => { api.get(`/reports/logbooks?${queryString(period)}`).then((r) => setData(r.data)).catch((e) => setError(e.message)); }, [params]);
  if (error) return <><PrintToolbar title="Laporan Logbook" back="/logbook"/><PrintError message={error}/></>;
  if (!data) return <PrintLoading/>;
  const users = data.users.length ? data.users : [user];
  return <div className="print-document"><PrintToolbar title="Laporan Logbook" back="/logbook"/>{users.map((person) => {
    const logs = data.items.filter((item) => Number(item.user_id) === Number(person.id));
    return <DocumentPage key={person.id}><Letterhead/><div className="document-title"><h1>LAPORAN KEGIATAN PEGAWAI</h1><p>Periode {monthName(data.month)} {data.year}</p></div>
      <table className="letter-meta"><tbody><tr><th>Nama</th><td>{person.name}</td></tr><tr><th>Jabatan / Unit</th><td>{person.jabatan || 'Pegawai UOBK RSUD Malangbong'}</td></tr></tbody></table>
      <table className="official-table logbook-print-table"><thead><tr><th>No.</th><th>Hari / Tanggal</th><th>Kegiatan</th><th>Penjelasan pelaksanaan</th><th>Foto</th></tr></thead><tbody>{logs.length ? logs.map((log, index) => <tr key={log.id}><td>{index + 1}</td><td>{formatDate(log.tanggal, { weekday: 'long' })}</td><td>{log.name}</td><td>{log.keterangan}</td><td>{log.foto && <img src={api.fileUrl(log.foto)} alt="Bukti kegiatan"/>}</td></tr>) : <tr><td colSpan="5">Tidak ada kegiatan pada periode ini.</td></tr>}</tbody></table><Signatory label="Mengetahui, Kepala Subbag Tata Usaha"/>
    </DocumentPage>;
  })}</div>;
}

export function SchedulePrintPage() {
  const [params] = useSearchParams();
  const period = { month: Number(params.get('month')) || new Date().getMonth() + 1, year: Number(params.get('year')) || new Date().getFullYear() };
  const [data, setData] = useState(null); const [error, setError] = useState('');
  useEffect(() => { api.get(`/schedules?${queryString(period)}`).then((r) => setData(r.data)).catch((e) => setError(e.message)); }, [params]);
  const scheduleMap = useMemo(() => { const map = new Map(); data?.schedules.forEach((s) => map.set(`${s.user_id}-${s.tanggal_masuk}`, s)); return map; }, [data]);
  if (error) return <><PrintToolbar title="Laporan Jadwal" back="/jadwal"/><PrintError message={error}/></>;
  if (!data) return <PrintLoading/>;
  return <div className="print-document"><PrintToolbar title="Laporan Jadwal" back="/jadwal"/><DocumentPage className="landscape"><Letterhead compact/><div className="document-title"><h1>JADWAL DINAS PEGAWAI</h1><p>Bulan {monthName(data.month)} Tahun {data.year}</p></div>
    <table className="attendance-print-table schedule-print-table"><thead><tr><th>No.</th><th>Nama / Jabatan</th>{Array.from({length:data.days_in_month},(_,i)=><th key={i}>{i+1}</th>)}</tr></thead><tbody>{data.users.map((person,index)=><tr key={person.id}><td>{index+1}</td><td><strong>{person.name}</strong><small>{person.jabatan}</small></td>{Array.from({length:data.days_in_month},(_,i)=>{const date=`${data.year}-${String(data.month).padStart(2,'0')}-${String(i+1).padStart(2,'0')}`;const schedule=scheduleMap.get(`${person.id}-${date}`);return <td key={i}>{abbreviateShift(schedule?.status)}</td>})}</tr>)}</tbody></table>
    <div className="print-legend">{data.types.map((type)=><span key={type.id}><strong>{abbreviateShift(type.name)}</strong> = {type.name} ({String(type.masuk).slice(0,5)}–{String(type.keluar).slice(0,5)})</span>)}</div><Signatory/>
  </DocumentPage></div>;
}

export function AttendancePrintPage() {
  const [params] = useSearchParams(); const period = { month: Number(params.get('month')) || new Date().getMonth() + 1, year: Number(params.get('year')) || new Date().getFullYear(), search: params.get('search') || '' };
  const [data, setData] = useState(null); const [error, setError] = useState('');
  useEffect(() => { api.get(`/reports/attendance?${queryString(period)}`).then((r) => setData(r.data)).catch((e) => setError(e.message)); }, [params]);
  const attendanceMap = useMemo(() => { const map = new Map(); data?.attendance.forEach((a) => map.set(`${a.user_id}-${a.created_at.slice(0,10)}`, a)); return map; }, [data]);
  const scheduleMap = useMemo(() => { const map = new Map(); data?.schedules.forEach((s) => map.set(`${s.user_id}-${s.tanggal_masuk}`, s)); return map; }, [data]);
  if (error) return <><PrintToolbar title="Laporan Kehadiran" back="/laporan"/><PrintError message={error}/></>;
  if (!data) return <PrintLoading/>;
  return <div className="print-document"><PrintToolbar title="Laporan Kehadiran" back="/laporan"/><DocumentPage className="landscape"><Letterhead compact/><div className="document-title"><h1>REKAPITULASI KEHADIRAN PEGAWAI</h1><p>Bulan {monthName(data.month)} Tahun {data.year}</p></div>
    <table className="attendance-print-table"><thead><tr><th rowSpan="2">No.</th><th rowSpan="2">Nama / Jabatan</th><th colSpan={data.days_in_month}>Tanggal</th><th rowSpan="2">H</th><th rowSpan="2">T</th></tr><tr>{Array.from({length:data.days_in_month},(_,i)=><th key={i}>{i+1}</th>)}</tr></thead><tbody>{data.users.map((person,index)=>{let present=0,late=0;return <tr key={person.id}><td>{index+1}</td><td><strong>{person.name}</strong><small>{person.jabatan}</small></td>{Array.from({length:data.days_in_month},(_,i)=>{const date=`${data.year}-${String(data.month).padStart(2,'0')}-${String(i+1).padStart(2,'0')}`;const a=attendanceMap.get(`${person.id}-${date}`);const s=scheduleMap.get(`${person.id}-${date}`);if(a){present++;if(a.telat)late++;}return <td key={i} title={a?`${formatTime(a.jam_masuk)}-${formatTime(a.jam_pulang)}`:s?.status}>{a?(a.telat?'T':'H'):(s?'J':'-')}</td>})}<td>{present}</td><td>{late}</td></tr>})}</tbody></table>
    <div className="print-legend">Keterangan: H = Hadir, T = Terlambat, J = Terjadwal, - = Tidak ada data</div><Signatory/>
  </DocumentPage></div>;
}

export function AmbulancePrintPage() {
  const [form, setForm] = useState({ patient:'', bpjs:'', nik:'', diagnosis:'', indication:'', origin:'UOBK RSUD Malangbong', destination:'', date:new Date().toISOString().slice(0,10), driver:'', nurse:'', vehicle:'' });
  const set=(name,value)=>setForm((current)=>({...current,[name]:value}));
  return <div className="print-document ambulance-document"><PrintToolbar title="Bukti Pelayanan Ambulance" back="/perjalanan-dinas"/><DocumentPage><Letterhead/><div className="document-title"><h1>LEMBAR BUKTI PELAYANAN AMBULANCE</h1><p>UOBK RUMAH SAKIT UMUM DAERAH MALANGBONG</p></div>
    <table className="ambulance-table"><tbody><tr><th>1</th><th>IDENTITAS PASIEN</th><th>TANDA TANGAN PASIEN / KELUARGA</th></tr><tr><td/><td><Editable label="Nama pasien" value={form.patient} onChange={(v)=>set('patient',v)}/><Editable label="No. kartu BPJS" value={form.bpjs} onChange={(v)=>set('bpjs',v)}/><Editable label="NIK" value={form.nik} onChange={(v)=>set('nik',v)}/></td><td className="signature-cell">(................................)</td></tr>
    <tr><th>2</th><th>KETERANGAN MEDIS / PENGIRIMAN</th><th>DOKTER PENANGGUNG JAWAB</th></tr><tr><td/><td><Editable label="Diagnosis" value={form.diagnosis} onChange={(v)=>set('diagnosis',v)}/><Editable label="Indikasi rujuk" value={form.indication} onChange={(v)=>set('indication',v)}/></td><td className="signature-cell">(................................)</td></tr>
    <tr><th>3</th><th>INFORMASI PELAYANAN</th><th>PETUGAS</th></tr><tr><td/><td><Editable label="Asal" value={form.origin} onChange={(v)=>set('origin',v)}/><Editable label="Tujuan" value={form.destination} onChange={(v)=>set('destination',v)}/><Editable label="Tanggal" type="date" value={form.date} onChange={(v)=>set('date',v)}/><Editable label="Nomor kendaraan" value={form.vehicle} onChange={(v)=>set('vehicle',v)}/></td><td><Editable label="Pengemudi" value={form.driver} onChange={(v)=>set('driver',v)}/><Editable label="Perawat pendamping" value={form.nurse} onChange={(v)=>set('nurse',v)}/></td></tr></tbody></table>
    <p className="document-note">Lembar ini merupakan bukti pelayanan ambulance dan harus dilengkapi tanda tangan pihak terkait.</p><Signatory/>
  </DocumentPage></div>;
}
function Editable({label,value,onChange,type='text'}){return <label className="print-editable"><span>{label}</span><input className="only-screen" type={type} value={value} onChange={(e)=>onChange(e.target.value)}/><strong className="only-print">: {value || '................................................'}</strong></label>}

function monthName(month){return new Date(2026,Number(month)-1,1).toLocaleDateString('id-ID',{month:'long'})}
function abbreviateShift(value){if(!value)return '-';return value.split(/[\s-]+/).map((part)=>part[0]).join('').slice(0,3).toUpperCase()}
function rupiah(number){return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(number)}
function capitalize(value){return value ? value.charAt(0).toUpperCase()+value.slice(1) : value}
function terbilang(value){const n=Math.floor(Math.abs(Number(value)||0));const words=['','satu','dua','tiga','empat','lima','enam','tujuh','delapan','sembilan','sepuluh','sebelas'];if(n<12)return words[n];if(n<20)return `${terbilang(n-10)} belas`;if(n<100)return `${terbilang(Math.floor(n/10))} puluh ${terbilang(n%10)}`.trim();if(n<200)return `seratus ${terbilang(n-100)}`.trim();if(n<1000)return `${terbilang(Math.floor(n/100))} ratus ${terbilang(n%100)}`.trim();if(n<2000)return `seribu ${terbilang(n-1000)}`.trim();if(n<1_000_000)return `${terbilang(Math.floor(n/1000))} ribu ${terbilang(n%1000)}`.trim();if(n<1_000_000_000)return `${terbilang(Math.floor(n/1_000_000))} juta ${terbilang(n%1_000_000)}`.trim();return `${terbilang(Math.floor(n/1_000_000_000))} miliar ${terbilang(n%1_000_000_000)}`.trim()}
