import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  ArrowRight,
  BookOpenText,
  BriefcaseMedical,
  Camera,
  CheckCircle2,
  Clock3,
  LogIn,
  LogOut,
  RotateCcw,
  Sparkles,
  TimerOff,
  UserCheck,
} from 'lucide-react';
import { ApiError, api } from '../api';
import { Avatar, formatDate, formatTime, LoadingScreen, Modal, StatusBadge } from '../components/Common';
import { useAuth } from '../auth';

function greeting() {
  const hour = new Date().getHours();
  if (hour < 11) return 'Selamat pagi';
  if (hour < 15) return 'Selamat siang';
  if (hour < 19) return 'Selamat sore';
  return 'Selamat malam';
}

function readImage(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
}

export default function DashboardPage() {
  const { user } = useAuth();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [attendanceOpen, setAttendanceOpen] = useState(false);
  const [notice, setNotice] = useState(null);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const response = await api.get('/dashboard');
      setData(response.data);
    } catch (error) {
      setNotice({ tone: 'error', text: error.message });
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { load(); }, [load]);

  if (loading) return <LoadingScreen />;
  const attendance = data?.attendance;
  const pendingClockOut = attendance && !attendance.foto_pulang;
  const action = pendingClockOut ? 'clock_out' : 'clock_in';
  const stats = [
    { label: 'Hadir bulan ini', value: data?.stats.hadir || 0, icon: UserCheck, tone: 'green' },
    { label: 'Terlambat', value: data?.stats.terlambat || 0, icon: TimerOff, tone: 'amber' },
    { label: 'Logbook terisi', value: data?.stats.logbook || 0, icon: BookOpenText, tone: 'blue' },
    { label: 'Perjalanan dinas', value: data?.stats.perjalanan_dinas || 0, icon: BriefcaseMedical, tone: 'purple' },
  ];

  return (
    <div className="dashboard-page">
      {notice && <div className={`alert alert-${notice.tone}`}>{notice.text}<button onClick={() => setNotice(null)}>×</button></div>}
      <section className="welcome-banner">
        <div className="welcome-copy">
          <span className="welcome-kicker"><Sparkles size={15} /> {formatDate(new Date().toISOString(), { weekday: 'long' })}</span>
          <h1>{greeting()}, {user?.name?.split(' ')[0]}!</h1>
          <p>Semoga hari Anda produktif. Jangan lupa mencatat kehadiran sesuai shift.</p>
          <button className="button button-light" type="button" onClick={() => setAttendanceOpen(true)}>
            {pendingClockOut ? <><LogOut size={18} /> Absen pulang</> : <><LogIn size={18} /> Absen masuk</>}
          </button>
        </div>
        <div className="welcome-profile">
          <Avatar user={user} size="xlarge" />
          <div><strong>{user?.name}</strong><span>{user?.jabatan || 'Pegawai'}</span></div>
        </div>
        <div className="welcome-pattern" />
      </section>

      <section className="stats-grid">
        {stats.map(({ label, value, icon: Icon, tone }) => (
          <article className="stat-card" key={label}>
            <span className={`stat-icon stat-${tone}`}><Icon size={21} /></span>
            <div><strong>{value}</strong><span>{label}</span></div>
          </article>
        ))}
      </section>

      <div className="dashboard-grid">
        <section className="panel attendance-panel">
          <div className="panel-heading">
            <div><span className="eyebrow">KEHADIRAN HARI INI</span><h2>Ringkasan absensi</h2></div>
            {attendance ? <StatusBadge tone={pendingClockOut ? 'warning' : 'success'}>{pendingClockOut ? 'Sedang bertugas' : 'Selesai'}</StatusBadge> : <StatusBadge>Belum absen</StatusBadge>}
          </div>

          {attendance ? (
            <>
              <div className="shift-summary">
                <span className="shift-symbol"><Clock3 size={22} /></span>
                <div><small>Shift dipilih</small><strong>{data.shifts?.[attendance.status_shift]?.label || attendance.status_shift}</strong></div>
                <span>{data.shifts?.[attendance.status_shift]?.times?.join(' — ')}</span>
              </div>
              <div className="attendance-timeline">
                <div className="timeline-row complete">
                  <span className="timeline-dot"><CheckCircle2 size={16} /></span>
                  <div><small>Absen masuk</small><strong>{formatTime(attendance.jam_masuk)}</strong><span>{formatDate(attendance.jam_masuk)}</span></div>
                  {attendance.telat && <StatusBadge tone="warning">Telat {attendance.telat}</StatusBadge>}
                </div>
                <div className={`timeline-row ${attendance.jam_pulang ? 'complete' : ''}`}>
                  <span className="timeline-dot">{attendance.jam_pulang ? <CheckCircle2 size={16} /> : <Clock3 size={16} />}</span>
                  <div><small>Absen pulang</small><strong>{formatTime(attendance.jam_pulang)}</strong><span>{attendance.jam_pulang ? formatDate(attendance.jam_pulang) : 'Menunggu absen pulang'}</span></div>
                  {attendance.pulang_awal && <StatusBadge tone="warning">Awal {attendance.pulang_awal}</StatusBadge>}
                </div>
              </div>
            </>
          ) : (
            <div className="attendance-empty">
              <span><Clock3 size={27} /></span>
              <div><strong>Belum ada absensi hari ini</strong><p>Pilih shift dan ambil foto untuk memulai.</p></div>
            </div>
          )}

          <button type="button" className="button button-primary button-wide" onClick={() => setAttendanceOpen(true)}>
            {pendingClockOut ? <><LogOut size={18} /> Catat absen pulang</> : <><Camera size={18} /> Catat absen masuk</>} <ArrowRight size={17} />
          </button>
        </section>

        <aside className="panel quick-info-panel">
          <div className="panel-heading"><div><span className="eyebrow">PANDUAN SINGKAT</span><h2>Sebelum absen</h2></div></div>
          <ol className="guide-list">
            <li><span>1</span><div><strong>Pilih shift yang benar</strong><p>Waktu keterlambatan dihitung dari shift.</p></div></li>
            <li><span>2</span><div><strong>Pastikan wajah terlihat</strong><p>Gunakan cahaya cukup dan foto yang jelas.</p></div></li>
            <li><span>3</span><div><strong>Periksa sebelum simpan</strong><p>Foto tidak dapat diganti setelah dikirim.</p></div></li>
          </ol>
          <div className="server-time"><Clock3 size={17} /><span>Waktu server<strong>{data?.server_time ? formatTime(data.server_time) : '—'}</strong></span></div>
        </aside>
      </div>

      <AttendanceModal
        open={attendanceOpen}
        onClose={() => setAttendanceOpen(false)}
        action={action}
        shifts={data?.shifts || {}}
        currentShift={attendance?.status_shift}
        onSaved={(message) => { setAttendanceOpen(false); setNotice({ tone: 'success', text: message }); load(); }}
      />
    </div>
  );
}

function AttendanceModal({ open, onClose, action, shifts, currentShift, onSaved }) {
  const [shift, setShift] = useState('');
  const [photo, setPhoto] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const clockOut = action === 'clock_out';

  useEffect(() => {
    if (open) {
      setShift(clockOut ? currentShift || '' : '');
      setPhoto('');
      setError('');
    }
  }, [open, clockOut, currentShift]);

  const selected = useMemo(() => shifts[shift], [shifts, shift]);

  async function pickPhoto(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    if (file.size > 10 * 1024 * 1024) {
      setError('Ukuran foto maksimal 10 MB.');
      return;
    }
    try {
      setPhoto(await readImage(file));
      setError('');
    } catch {
      setError('Foto tidak dapat dibaca.');
    }
  }

  async function submit(event) {
    event.preventDefault();
    if (!clockOut && !shift) return setError('Pilih shift terlebih dahulu.');
    if (!photo) return setError('Ambil atau pilih foto terlebih dahulu.');
    setSubmitting(true);
    setError('');
    try {
      const response = await api.post('/attendance', { action, shift, photo });
      onSaved(response.message);
    } catch (caught) {
      setError(caught instanceof ApiError ? caught.message : 'Absensi gagal disimpan.');
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <Modal open={open} onClose={onClose} title={clockOut ? 'Catat absen pulang' : 'Catat absen masuk'} subtitle="Pastikan data sudah sesuai sebelum disimpan." size="large">
      <form className="attendance-form" onSubmit={submit}>
        {!clockOut && (
          <div className="form-section">
            <label className="section-label">1. Pilih shift kerja</label>
            <div className="shift-grid">
              {Object.entries(shifts).map(([key, item]) => (
                <button type="button" key={key} className={`shift-option ${shift === key ? 'selected' : ''}`} onClick={() => setShift(key)}>
                  <span>{item.icon?.includes('moon') ? '🌙' : item.icon === 'ambulance' ? '🚑' : '☀️'}</span>
                  <strong>{item.label}</strong><small>{item.times.join(' – ')}</small>
                </button>
              ))}
            </div>
          </div>
        )}

        {clockOut && selected && <div className="selected-shift"><Clock3 size={20} /><span>Shift {selected.label}<strong>{selected.times.join(' – ')}</strong></span></div>}

        <div className="form-section">
          <label className="section-label">{clockOut ? '1' : '2'}. Ambil foto</label>
          <label className={`photo-picker ${photo ? 'has-photo' : ''}`}>
            {photo ? <img src={photo} alt="Pratinjau absensi" /> : <><span><Camera size={28} /></span><strong>Ambil atau pilih foto</strong><small>JPG, PNG, atau WebP · maksimal 10 MB</small></>}
            <input type="file" accept="image/jpeg,image/png,image/webp" capture="user" onChange={pickPhoto} />
          </label>
          {photo && <label className="replace-photo"><RotateCcw size={15} /> Ganti foto<input type="file" accept="image/jpeg,image/png,image/webp" capture="user" onChange={pickPhoto} /></label>}
        </div>

        {error && <div className="alert alert-error">{error}</div>}
        <div className="form-actions">
          <button type="button" className="button button-ghost" onClick={onClose}>Batal</button>
          <button type="submit" className="button button-primary" disabled={submitting}>{submitting ? 'Menyimpan…' : clockOut ? 'Simpan absen pulang' : 'Simpan absen masuk'}</button>
        </div>
      </form>
    </Modal>
  );
}
