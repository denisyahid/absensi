import { useCallback, useEffect, useMemo, useState } from 'react';
import { CalendarDays, ChevronLeft, ChevronRight, Info, Printer } from 'lucide-react';
import { Link } from 'react-router-dom';
import { api, queryString } from '../api';
import { Avatar, LoadingScreen, PageHeading } from '../components/Common';

const monthFormatter = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' });

function monthState(date = new Date()) { return { month: date.getMonth() + 1, year: date.getFullYear() }; }

export default function SchedulesPage() {
  const [period, setPeriod] = useState(monthState);
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState('');
  const [notice, setNotice] = useState(null);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const response = await api.get(`/schedules?${queryString(period)}`);
      setData(response.data);
    } catch (error) {
      setNotice({ tone: 'error', text: error.message });
    } finally {
      setLoading(false);
    }
  }, [period]);

  useEffect(() => { load(); }, [load]);

  const scheduleMap = useMemo(() => {
    const map = new Map();
    data?.schedules.forEach((item) => map.set(`${item.user_id}-${item.tanggal_masuk}`, item.status));
    return map;
  }, [data]);

  function moveMonth(direction) {
    const date = new Date(period.year, period.month - 1 + direction, 1);
    setPeriod(monthState(date));
  }

  async function changeSchedule(userId, day, status) {
    const date = `${period.year}-${String(period.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    const key = `${userId}-${date}`;
    setSaving(key);
    try {
      const response = await api.post('/schedules', { user_id: userId, date, status });
      setNotice({ tone: 'success', text: response.message });
      setData((current) => ({ ...current, schedules: [...current.schedules.filter((item) => !(Number(item.user_id) === Number(userId) && item.tanggal_masuk === date)), { user_id: userId, tanggal_masuk: date, status }] }));
    } catch (error) {
      setNotice({ tone: 'error', text: error.message });
    } finally {
      setSaving('');
    }
  }

  const periodLabel = monthFormatter.format(new Date(period.year, period.month - 1, 1));

  return (
    <div>
      <PageHeading eyebrow="PENJADWALAN" title="Jadwal pegawai" description="Lihat dan atur pembagian shift dalam satu kalender." actions={<>
        <Link className="button button-secondary" to={`/cetak/laporan-jadwal?month=${period.month}&year=${period.year}`} target="_blank"><Printer size={17}/> Cetak</Link>
        <div className="month-switcher"><button onClick={() => moveMonth(-1)} aria-label="Bulan sebelumnya"><ChevronLeft size={18} /></button><span><CalendarDays size={17} />{periodLabel}</span><button onClick={() => moveMonth(1)} aria-label="Bulan berikutnya"><ChevronRight size={18} /></button></div>
      </>} />
      {notice && <div className={`alert alert-${notice.tone}`}>{notice.text}<button onClick={() => setNotice(null)}>×</button></div>}
      {!data?.can_manage && <div className="info-strip"><Info size={18} /><span>Anda melihat jadwal pribadi. Perubahan jadwal hanya dapat dilakukan manajemen.</span></div>}

      <section className="panel schedule-panel">
        {loading ? <LoadingScreen compact /> : (
          <div className="schedule-scroll">
            <table className="schedule-table">
              <thead><tr><th className="sticky-person">Pegawai</th>{Array.from({ length: data.days_in_month }, (_, index) => {
                const day = index + 1;
                const date = new Date(period.year, period.month - 1, day);
                const weekend = [0, 6].includes(date.getDay());
                return <th key={day} className={weekend ? 'weekend' : ''}><span>{date.toLocaleDateString('id-ID', { weekday: 'short' })}</span><strong>{day}</strong></th>;
              })}</tr></thead>
              <tbody>{data.users.map((user) => (
                <tr key={user.id}>
                  <td className="sticky-person"><div className="schedule-user"><Avatar user={user} size="small" /><span><strong>{user.name}</strong><small>{user.jabatan || 'Pegawai'}</small></span></div></td>
                  {Array.from({ length: data.days_in_month }, (_, index) => {
                    const day = index + 1;
                    const date = `${period.year}-${String(period.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    const key = `${user.id}-${date}`;
                    const value = scheduleMap.get(key) || '';
                    return <td key={day} className={value ? `has-shift shift-${value.toLowerCase().replace(/\s+/g, '-')}` : ''}>{data.can_manage ? (
                      <select aria-label={`Jadwal ${user.name} tanggal ${day}`} value={value} disabled={saving === key} onChange={(event) => changeSchedule(user.id, day, event.target.value)}>
                        <option value="">—</option>{data.types.map((type) => <option value={type.name} key={type.id}>{type.name}</option>)}
                      </select>
                    ) : <span className="schedule-value">{value || '—'}</span>}</td>;
                  })}
                </tr>
              ))}</tbody>
            </table>
          </div>
        )}
      </section>

      <section className="schedule-legend">
        <strong>Keterangan shift</strong>
        <div>{data?.types?.map((type, index) => <span key={type.id}><i className={`legend-dot legend-${index % 5}`} />{type.name}<small>{String(type.masuk).slice(0, 5)}–{String(type.keluar).slice(0, 5)}</small></span>)}</div>
      </section>
    </div>
  );
}
