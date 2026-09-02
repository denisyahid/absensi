import { useCallback, useEffect, useState } from 'react';
import {
  CalendarRange,
  Check,
  CircleCheck,
  FileImage,
  MapPin,
  Pencil,
  Plus,
  Printer,
  Search,
  Trash2,
  UserPlus,
} from 'lucide-react';
import { Link } from 'react-router-dom';
import { ApiError, api, queryString } from '../api';
import { Avatar, EmptyState, FieldError, formatDate, LoadingScreen, Modal, PageHeading, Pagination, StatusBadge, todayInput } from '../components/Common';
import { useAuth } from '../auth';

const baseForm = () => ({
  nama_rujukan: '', alamat_rujukan: '', perihal: 'Rujukan Pasien', dasar_surat: '', menimbang: '', waktu: '08.00 WIB s.d selesai', tempat: '', biaya_perdin: '70000', alat_angkut: 'Roda Empat', tanggal_berangkat: todayInput(), tanggal_kembali: todayInput(), participants: [], bukti_rujukan: null, kuitansi_bensin: null,
});

export default function ReferralsPage() {
  const { user } = useAuth();
  const canManage = Boolean(user?.is_manager || user?.permissions?.includes('manage-referrals'));
  const [items, setItems] = useState([]);
  const [meta, setMeta] = useState(null);
  const [filters, setFilters] = useState({ search: '', from_date: '', to_date: '', page: 1 });
  const [loading, setLoading] = useState(true);
  const [modal, setModal] = useState(null);
  const [notice, setNotice] = useState(null);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const response = await api.get(`/referrals?${queryString(filters)}`);
      setItems(response.data); setMeta(response.meta);
    } catch (error) { setNotice({ tone: 'error', text: error.message }); }
    finally { setLoading(false); }
  }, [filters]);

  useEffect(() => { const timeout = setTimeout(load, 250); return () => clearTimeout(timeout); }, [load]);

  async function remove(item) {
    if (!window.confirm(`Hapus perjalanan dinas ke ${item.nama_rujukan}?`)) return;
    try { const response = await api.delete(`/referrals/${item.id}`); setNotice({ tone: 'success', text: response.message }); load(); }
    catch (error) { setNotice({ tone: 'error', text: error.message }); }
  }
  async function confirm(item) {
    if (!window.confirm(`Konfirmasi perjalanan dinas ke ${item.nama_rujukan}?`)) return;
    try { const response = await api.post(`/referrals/${item.id}/confirm`, {}); setNotice({ tone: 'success', text: response.message }); load(); }
    catch (error) { setNotice({ tone: 'error', text: error.message }); }
  }

  return (
    <div>
      <PageHeading eyebrow="ADMINISTRASI" title="Perjalanan dinas" description="Kelola rujukan pasien, peserta, dan bukti perjalanan." actions={<><Link className="button button-secondary" to="/cetak/bukti-ambulance" target="_blank"><Printer size={17} /> Form ambulance</Link><button className="button button-primary" onClick={() => setModal({ type: 'create' })}><Plus size={18} /> Buat perjalanan</button></>} />
      {notice && <div className={`alert alert-${notice.tone}`}>{notice.text}<button onClick={() => setNotice(null)}>×</button></div>}
      <section className="panel filter-panel referral-filters">
        <label className="search-field"><Search size={18} /><input placeholder="Cari tujuan, lokasi, atau peserta…" value={filters.search} onChange={(e) => setFilters({ ...filters, search: e.target.value, page: 1 })} /></label>
        <label className="compact-field"><span>Dari</span><input type="date" value={filters.from_date} onChange={(e) => setFilters({ ...filters, from_date: e.target.value, page: 1 })} /></label>
        <label className="compact-field"><span>Sampai</span><input type="date" value={filters.to_date} onChange={(e) => setFilters({ ...filters, to_date: e.target.value, page: 1 })} /></label>
      </section>

      <section className="panel table-panel">
        {loading ? <LoadingScreen compact /> : items.length ? <div className="table-scroll"><table className="data-table referral-table">
          <thead><tr><th>Tujuan perjalanan</th><th>Peserta</th><th>Tanggal</th><th>Bukti</th><th>Status</th><th><span className="sr-only">Aksi</span></th></tr></thead>
          <tbody>{items.map((item) => <tr key={item.id}>
            <td><div className="destination-cell"><span className="destination-icon"><MapPin size={19} /></span><div><strong>{item.nama_rujukan}</strong><span>{item.tempat || item.alamat_rujukan}</span><small>{item.nomor_surat}</small></div></div></td>
            <td><div className="participant-stack">{item.participants.slice(0, 3).map((participant) => <Avatar key={participant.user_id} user={{ name: participant.user_name }} size="tiny" />)}{item.participants.length > 3 && <span className="avatar avatar-tiny avatar-fallback">+{item.participants.length - 3}</span>}<span>{item.participants.length} orang</span></div></td>
            <td><div className="date-range"><CalendarRange size={16} /><span><strong>{formatDate(item.tanggal_berangkat)}</strong><small>s.d. {formatDate(item.tanggal_kembali)}</small></span></div></td>
            <td><div className="proof-links">{item.bukti_rujukan ? <a href={api.fileUrl(item.bukti_rujukan)} target="_blank" rel="noreferrer"><FileImage size={15} /> Rujukan</a> : <span>—</span>}{item.kuitansi_bensin && <a href={api.fileUrl(item.kuitansi_bensin)} target="_blank" rel="noreferrer"><FileImage size={15} /> Kuitansi</a>}</div></td>
            <td>{item.status === 'confirmed' ? <StatusBadge tone="success"><CircleCheck size={13} /> Dikonfirmasi</StatusBadge> : <StatusBadge tone="warning">Menunggu</StatusBadge>}</td>
            <td><div className="row-actions"><Link className="icon-button" title="Cetak SPPD" to={`/cetak/sppd/${item.id}`} target="_blank"><Printer size={16} /></Link><Link className="icon-button srikandi" title="Cetak SPPD Srikandi" to={`/cetak/sppd-srikandi/${item.id}`} target="_blank">S</Link><button className="icon-button" title="Edit" onClick={() => setModal({ type: 'edit', item })}><Pencil size={17} /></button>{canManage && item.status !== 'confirmed' && <button className="icon-button success" title="Konfirmasi" onClick={() => confirm(item)}><Check size={17} /></button>}<button className="icon-button danger" title="Hapus" onClick={() => remove(item)}><Trash2 size={17} /></button></div></td>
          </tr>)}</tbody>
        </table></div> : <EmptyState title="Belum ada perjalanan dinas" description="Buat perjalanan baru untuk mulai mencatat rujukan." />}
        <Pagination meta={meta} onPage={(page) => setFilters({ ...filters, page })} />
      </section>
      <ReferralForm modal={modal} currentUser={user} canManage={canManage} onClose={() => setModal(null)} onSaved={(message) => { setModal(null); setNotice({ tone: 'success', text: message }); load(); }} />
    </div>
  );
}

function ReferralForm({ modal, currentUser, canManage, onClose, onSaved }) {
  const item = modal?.item;
  const [form, setForm] = useState(baseForm);
  const [directory, setDirectory] = useState([]);
  const [pns, setPns] = useState([]);
  const [userSearch, setUserSearch] = useState('');
  const [loadingOptions, setLoadingOptions] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [message, setMessage] = useState('');
  const [errors, setErrors] = useState(null);

  useEffect(() => {
    if (!modal) return;
    const participants = item?.participants?.map((participant) => ({ user_id: Number(participant.user_id), pns_id: participant.pns_id ? Number(participant.pns_id) : null })) || [{ user_id: Number(currentUser.id), pns_id: null }];
    setForm(item ? {
      ...baseForm(),
      nama_rujukan: item.nama_rujukan || '', alamat_rujukan: item.alamat_rujukan || '', perihal: item.perihal || 'Rujukan Pasien', dasar_surat: item.dasar_surat || '', menimbang: item.menimbang || '', waktu: item.waktu || '', tempat: item.tempat || '', biaya_perdin: String(item.biaya_perdin || 70000), alat_angkut: item.alat_angkut || 'Roda Empat', tanggal_berangkat: String(item.tanggal_berangkat).slice(0, 10), tanggal_kembali: String(item.tanggal_kembali).slice(0, 10), participants,
    } : { ...baseForm(), participants });
    setMessage(''); setErrors(null); setUserSearch('');
    setLoadingOptions(true);
    Promise.all([api.get('/directory'), api.get('/pns')]).then(([users, civilServants]) => { setDirectory(users.data); setPns(civilServants.data); }).catch((error) => setMessage(error.message)).finally(() => setLoadingOptions(false));
  }, [modal, item, currentUser.id]);

  if (!modal) return null;
  const update = (name, value) => setForm((current) => ({ ...current, [name]: value }));
  const selectedIds = form.participants.map((participant) => Number(participant.user_id));
  const filteredUsers = directory.filter((person) => !selectedIds.includes(Number(person.id)) && `${person.name} ${person.jabatan || ''}`.toLowerCase().includes(userSearch.toLowerCase()));

  function addParticipant(person) {
    update('participants', [...form.participants, { user_id: Number(person.id), pns_id: null }]); setUserSearch('');
  }
  function removeParticipant(userId) { update('participants', form.participants.filter((participant) => Number(participant.user_id) !== Number(userId))); }
  function updatePns(userId, pnsId) { update('participants', form.participants.map((participant) => Number(participant.user_id) === Number(userId) ? { ...participant, pns_id: pnsId ? Number(pnsId) : null } : participant)); }

  async function submit(event) {
    event.preventDefault(); setSubmitting(true); setMessage(''); setErrors(null);
    const body = new FormData();
    Object.entries(form).forEach(([key, value]) => {
      if (key === 'participants') body.append(key, JSON.stringify(value));
      else if (value !== null) body.append(key, value);
    });
    try { const response = await api.post(item ? `/referrals/${item.id}` : '/referrals', body); onSaved(response.message); }
    catch (error) { setMessage(error instanceof ApiError ? error.message : 'Perjalanan gagal disimpan.'); setErrors(error.errors); }
    finally { setSubmitting(false); }
  }

  return <Modal open onClose={onClose} title={item ? 'Edit perjalanan dinas' : 'Buat perjalanan dinas'} subtitle="Lengkapi tujuan, tanggal, peserta, dan dokumen pendukung." size="xlarge">
    <form className="entity-form referral-form" onSubmit={submit}>
      {message && <div className="alert alert-error">{message}</div>}
      <div className="form-section-card"><div className="section-card-title"><span>01</span><div><strong>Informasi tujuan</strong><small>Data rujukan dan lokasi perjalanan</small></div></div>
        <div className="form-grid two-columns">
          <label className="field"><span>Nama tujuan *</span><input value={form.nama_rujukan} onChange={(e) => update('nama_rujukan', e.target.value)} placeholder="Contoh: RS Hasan Sadikin" /><FieldError errors={errors} name="nama_rujukan" /></label>
          <label className="field"><span>Tempat *</span><input value={form.tempat} onChange={(e) => update('tempat', e.target.value)} placeholder="Kota / fasilitas tujuan" /><FieldError errors={errors} name="tempat" /></label>
          <label className="field span-two"><span>Alamat lengkap *</span><textarea rows="2" value={form.alamat_rujukan} onChange={(e) => update('alamat_rujukan', e.target.value)} placeholder="Alamat tujuan perjalanan" /><FieldError errors={errors} name="alamat_rujukan" /></label>
          <label className="field"><span>Perihal *</span><input value={form.perihal} onChange={(e) => update('perihal', e.target.value)} /></label>
          <label className="field"><span>Waktu *</span><input value={form.waktu} onChange={(e) => update('waktu', e.target.value)} /></label>
          <label className="field span-two"><span>Dasar surat</span><textarea rows="2" value={form.dasar_surat} onChange={(e) => update('dasar_surat', e.target.value)} placeholder="Dasar penerbitan surat tugas (opsional)" /></label>
          <label className="field span-two"><span>Menimbang</span><textarea rows="2" value={form.menimbang} onChange={(e) => update('menimbang', e.target.value)} placeholder="Pertimbangan penerbitan surat tugas (opsional)" /></label>
        </div>
      </div>
      <div className="form-section-card"><div className="section-card-title"><span>02</span><div><strong>Rencana perjalanan</strong><small>Tanggal, transportasi, dan biaya</small></div></div>
        <div className="form-grid four-columns">
          <label className="field"><span>Tanggal berangkat *</span><input type="date" value={form.tanggal_berangkat} onChange={(e) => update('tanggal_berangkat', e.target.value)} /><FieldError errors={errors} name="tanggal_berangkat" /></label>
          <label className="field"><span>Tanggal kembali *</span><input type="date" value={form.tanggal_kembali} onChange={(e) => update('tanggal_kembali', e.target.value)} /><FieldError errors={errors} name="tanggal_kembali" /></label>
          <label className="field"><span>Alat angkut *</span><input value={form.alat_angkut} onChange={(e) => update('alat_angkut', e.target.value)} /></label>
          <label className="field"><span>Biaya perjalanan</span><input type="number" min="0" value={form.biaya_perdin} onChange={(e) => update('biaya_perdin', e.target.value)} /></label>
        </div>
      </div>
      <div className="form-section-card"><div className="section-card-title"><span>03</span><div><strong>Peserta perjalanan</strong><small>Pilih pegawai dan data PNS pendamping</small></div></div>
        <div className="participant-picker"><label className="search-field"><Search size={17} /><input value={userSearch} onChange={(e) => setUserSearch(e.target.value)} placeholder="Cari pegawai untuk ditambahkan…" /></label>{userSearch && <div className="user-suggestions">{filteredUsers.slice(0, 6).map((person) => <button type="button" key={person.id} onClick={() => addParticipant(person)}><Avatar user={person} size="tiny" /><span><strong>{person.name}</strong><small>{person.jabatan}</small></span><UserPlus size={16} /></button>)}</div>}</div>
        {loadingOptions ? <LoadingScreen compact /> : <div className="selected-participants">{form.participants.map((participant) => { const person = directory.find((candidate) => Number(candidate.id) === Number(participant.user_id)) || (Number(participant.user_id) === Number(currentUser.id) ? currentUser : { name: `User #${participant.user_id}` }); return <div className="participant-row" key={participant.user_id}><Avatar user={person} size="small" /><span><strong>{person.name}</strong><small>{person.jabatan || 'Pegawai'}</small></span><label><small>Data PNS (opsional)</small><select value={participant.pns_id || ''} onChange={(e) => updatePns(participant.user_id, e.target.value)}><option value="">Tidak meminjam data PNS</option>{pns.map((official) => <option value={official.id} key={official.id}>{official.nama} · {official.nip}</option>)}</select></label><button type="button" className="icon-button danger" onClick={() => removeParticipant(participant.user_id)} disabled={!canManage && Number(participant.user_id) === Number(currentUser.id)}><Trash2 size={16} /></button></div>; })}</div>}
        <FieldError errors={errors} name="participants" />
      </div>
      <div className="form-section-card"><div className="section-card-title"><span>04</span><div><strong>Dokumen pendukung</strong><small>Opsional, format gambar maksimal 10 MB</small></div></div><div className="form-grid two-columns">
        <label className="upload-field compact-upload"><FileImage size={20} /><span><strong>Bukti rujukan</strong><small>{form.bukti_rujukan?.name || (item?.bukti_rujukan ? 'Gunakan file lama' : 'Pilih gambar')}</small></span><input type="file" accept="image/*" onChange={(e) => update('bukti_rujukan', e.target.files?.[0] || null)} /></label>
        <label className="upload-field compact-upload"><FileImage size={20} /><span><strong>Kuitansi bensin</strong><small>{form.kuitansi_bensin?.name || (item?.kuitansi_bensin ? 'Gunakan file lama' : 'Pilih gambar')}</small></span><input type="file" accept="image/*" onChange={(e) => update('kuitansi_bensin', e.target.files?.[0] || null)} /></label>
      </div></div>
      <div className="form-actions"><button type="button" className="button button-ghost" onClick={onClose}>Batal</button><button className="button button-primary" disabled={submitting || loadingOptions}>{submitting ? 'Menyimpan…' : item ? 'Simpan perubahan' : 'Buat perjalanan'}</button></div>
    </form>
  </Modal>;
}
