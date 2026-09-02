import { useCallback, useEffect, useMemo, useState } from 'react';
import { CalendarDays, CheckCircle2, ChevronLeft, ChevronRight, Clock3, Download, Printer, Search } from 'lucide-react';
import { Link } from 'react-router-dom';
import { api, queryString } from '../api';
import { Avatar, EmptyState, formatTime, LoadingScreen, PageHeading } from '../components/Common';

function currentPeriod() { const now = new Date(); return { month: now.getMonth() + 1, year: now.getFullYear() }; }

export default function ReportsPage() {
  const [filters, setFilters] = useState({ ...currentPeriod(), search: '' });
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [notice, setNotice] = useState('');

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const response = await api.get(`/reports/attendance?${queryString(filters)}`);
      setData(response.data); setNotice('');
    } catch (error) { setNotice(error.message); }
    finally { setLoading(false); }
  }, [filters]);

  useEffect(() => { const timeout = setTimeout(load, 200); return () => clearTimeout(timeout); }, [load]);

  const records = useMemo(() => {
    const map = new Map();
    data?.attendance.forEach((item) => map.set(`${item.user_id}-${item.created_at.slice(0, 10)}`, item));
    return map;
  }, [data]);
  const schedules = useMemo(() => {
    const map = new Map(); data?.schedules.forEach((item) => map.set(`${item.user_id}-${item.tanggal_masuk}`, item)); return map;
  }, [data]);

  function moveMonth(direction) {
    const date = new Date(filters.year, filters.month - 1 + direction, 1);
    setFilters({ ...filters, month: date.getMonth() + 1, year: date.getFullYear() });
  }

  function exportCsv() {
    if (!data) return;
    const rows = [['Nama', 'Jabatan', 'Tanggal', 'Shift', 'Masuk', 'Pulang', 'Terlambat', 'Pulang awal']];
    data.users.forEach((user) => data.attendance.filter((item) => Number(item.user_id) === Number(user.id)).forEach((item) => rows.push([
      user.name, user.jabatan || '', item.created_at.slice(0, 10), item.status_shift, item.jam_masuk || '', item.jam_pulang || '', item.telat || '', item.pulang_awal || '',
    ])));
    const csv = rows.map((row) => row.map((cell) => `"${String(cell).replaceAll('"', '""')}"`).join(',')).join('\n');
    const link = document.createElement('a'); link.href = URL.createObjectURL(new Blob([`\ufeff${csv}`], { type: 'text/csv;charset=utf-8' })); link.download = `laporan-absensi-${filters.year}-${String(filters.month).padStart(2, '0')}.csv`; link.click(); URL.revokeObjectURL(link.href);
  }

  const label = new Date(filters.year, filters.month - 1, 1).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });

  return (
    <div>
      <PageHeading eyebrow="REKAPITULASI" title="Laporan kehadiran" description="Pantau kehadiran dan jadwal pegawai per bulan." actions={<><Link className="button button-secondary" to={`/cetak/laporan-kehadiran?month=${filters.month}&year=${filters.year}&search=${encodeURIComponent(filters.search)}`} target="_blank"><Printer size={17} /> Cetak</Link><button className="button button-secondary" onClick={exportCsv} disabled={!data?.attendance.length}><Download size={17} /> Ekspor CSV</button></>} />
      {notice && <div className="alert alert-error">{notice}</div>}
      <section className="panel filter-panel report-filter">
        <label className="search-field"><Search size={18} /><input placeholder="Cari nama atau jabatan…" value={filters.search} onChange={(e) => setFilters({ ...filters, search: e.target.value })} /></label>
        <div className="month-switcher"><button onClick={() => moveMonth(-1)}><ChevronLeft size={18} /></button><span><CalendarDays size={17} />{label}</span><button onClick={() => moveMonth(1)}><ChevronRight size={18} /></button></div>
      </section>

      <section className="report-summary">
        <article><span className="stat-icon stat-green"><CheckCircle2 size={20} /></span><div><small>Total kehadiran</small><strong>{data?.attendance.length || 0}</strong></div></article>
        <article><span className="stat-icon stat-amber"><Clock3 size={20} /></span><div><small>Total terlambat</small><strong>{data?.attendance.filter((item) => item.telat).length || 0}</strong></div></article>
        <article><span className="stat-icon stat-blue"><CalendarDays size={20} /></span><div><small>Pegawai ditampilkan</small><strong>{data?.users.length || 0}</strong></div></article>
      </section>

      <section className="panel report-panel">
        {loading ? <LoadingScreen compact /> : data?.users.length ? (
          <div className="report-scroll"><table className="report-table"><thead><tr><th className="sticky-person">Pegawai</th>{Array.from({ length: data.days_in_month }, (_, index) => <th key={index}>{index + 1}</th>)}</tr></thead><tbody>{data.users.map((user) => <tr key={user.id}>
            <td className="sticky-person"><div className="schedule-user"><Avatar user={user} size="small" /><span><strong>{user.name}</strong><small>{user.jabatan || 'Pegawai'}</small></span></div></td>
            {Array.from({ length: data.days_in_month }, (_, index) => {
              const day = index + 1; const date = `${filters.year}-${String(filters.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`; const attendance = records.get(`${user.id}-${date}`); const schedule = schedules.get(`${user.id}-${date}`);
              if (attendance) return <td key={day}><span className={`report-code ${attendance.telat ? 'late' : 'present'}`} title={`Masuk ${formatTime(attendance.jam_masuk)} · Pulang ${formatTime(attendance.jam_pulang)}${attendance.telat ? ` · Telat ${attendance.telat}` : ''}`}>{attendance.telat ? 'T' : 'H'}</span></td>;
              if (schedule) return <td key={day}><span className="report-code scheduled" title={`Jadwal: ${schedule.status}`}>J</span></td>;
              return <td key={day}><span className="report-empty">·</span></td>;
            })}
          </tr>)}</tbody></table></div>
        ) : <EmptyState title="Tidak ada data laporan" description="Belum ada pegawai atau hasil pencarian tidak ditemukan." />}
      </section>
      <div className="report-legend"><span><i className="report-code present">H</i> Hadir</span><span><i className="report-code late">T</i> Terlambat</span><span><i className="report-code scheduled">J</i> Terjadwal</span></div>
    </div>
  );
}
