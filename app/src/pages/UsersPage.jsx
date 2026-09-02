import { useCallback, useEffect, useState } from 'react';
import { Mail, Pencil, Phone, Plus, Search, Trash2, UserRound, Users } from 'lucide-react';
import { ApiError, api, queryString } from '../api';
import { Avatar, EmptyState, FieldError, LoadingScreen, Modal, PageHeading, Pagination, StatusBadge } from '../components/Common';

const emptyForm = { name: '', email: '', jabatan: '', nomor_hp: '', jadwal: '', password: '', foto: null };

export default function UsersPage() {
  const [items, setItems] = useState([]);
  const [meta, setMeta] = useState(null);
  const [positions, setPositions] = useState([]);
  const [filters, setFilters] = useState({ search: '', jabatan: '', page: 1 });
  const [loading, setLoading] = useState(true);
  const [modal, setModal] = useState(null);
  const [notice, setNotice] = useState(null);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const response = await api.get(`/users?${queryString(filters)}`);
      setItems(response.data);
      setMeta(response.meta);
      setPositions(response.positions || []);
    } catch (error) {
      setNotice({ tone: 'error', text: error.message });
    } finally {
      setLoading(false);
    }
  }, [filters]);

  useEffect(() => { const timeout = setTimeout(load, 250); return () => clearTimeout(timeout); }, [load]);

  async function remove(item) {
    if (!window.confirm(`Hapus akun ${item.name}? Tindakan ini tidak dapat dibatalkan.`)) return;
    try {
      const response = await api.delete(`/users/${item.id}`);
      setNotice({ tone: 'success', text: response.message });
      load();
    } catch (error) {
      setNotice({ tone: 'error', text: error.message });
    }
  }

  return (
    <div>
      <PageHeading eyebrow="MANAJEMEN AKSES" title="Data pengguna" description="Kelola akun, jabatan, dan informasi pegawai." actions={
        <button className="button button-primary" type="button" onClick={() => setModal({ type: 'create' })}><Plus size={18} /> Tambah pengguna</button>
      } />
      {notice && <div className={`alert alert-${notice.tone}`}>{notice.text}<button onClick={() => setNotice(null)}>×</button></div>}

      <section className="panel filter-panel">
        <label className="search-field"><Search size={18} /><input placeholder="Cari nama, email, atau jabatan…" value={filters.search} onChange={(event) => setFilters({ ...filters, search: event.target.value, page: 1 })} /></label>
        <select value={filters.jabatan} onChange={(event) => setFilters({ ...filters, jabatan: event.target.value, page: 1 })}>
          <option value="">Semua jabatan</option>
          {positions.map((position) => <option key={position}>{position}</option>)}
        </select>
        <div className="filter-count"><Users size={17} /><span><strong>{meta?.total || 0}</strong> pengguna terdaftar</span></div>
      </section>

      <section className="panel table-panel">
        {loading ? <LoadingScreen compact /> : items.length ? (
          <div className="table-scroll">
            <table className="data-table">
              <thead><tr><th>Pengguna</th><th>Kontak</th><th>Jabatan</th><th>Jadwal</th><th><span className="sr-only">Aksi</span></th></tr></thead>
              <tbody>{items.map((item) => (
                <tr key={item.id}>
                  <td><div className="user-cell"><Avatar user={item} /><div><strong>{item.name}</strong><span>ID #{String(item.id).padStart(4, '0')}</span></div></div></td>
                  <td><div className="stacked-cell"><span><Mail size={14} />{item.email}</span><span><Phone size={14} />{item.nomor_hp || 'Belum diisi'}</span></div></td>
                  <td><StatusBadge tone={item.jabatan?.toLowerCase() === 'manajemen' ? 'info' : 'neutral'}>{item.jabatan || '—'}</StatusBadge></td>
                  <td>{item.jadwal || <span className="muted">Belum diatur</span>}</td>
                  <td><div className="row-actions"><button className="icon-button" title="Edit" onClick={() => setModal({ type: 'edit', item })}><Pencil size={17} /></button><button className="icon-button danger" title="Hapus" onClick={() => remove(item)}><Trash2 size={17} /></button></div></td>
                </tr>
              ))}</tbody>
            </table>
          </div>
        ) : <EmptyState title="Pengguna tidak ditemukan" description="Coba ubah kata kunci atau tambahkan pengguna baru." />}
        <Pagination meta={meta} onPage={(page) => setFilters({ ...filters, page })} />
      </section>

      <UserForm modal={modal} onClose={() => setModal(null)} onSaved={(message) => { setModal(null); setNotice({ tone: 'success', text: message }); load(); }} />
    </div>
  );
}

function UserForm({ modal, onClose, onSaved }) {
  const item = modal?.item;
  const [form, setForm] = useState(emptyForm);
  const [errors, setErrors] = useState(null);
  const [message, setMessage] = useState('');
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (modal) setForm(item ? { name: item.name || '', email: item.email || '', jabatan: item.jabatan || '', nomor_hp: item.nomor_hp || '', jadwal: item.jadwal || '', password: '', foto: null } : emptyForm);
    setErrors(null);
    setMessage('');
  }, [modal, item]);

  if (!modal) return null;

  function update(name, value) { setForm((current) => ({ ...current, [name]: value })); }

  async function submit(event) {
    event.preventDefault();
    setSubmitting(true);
    setErrors(null);
    setMessage('');
    const body = new FormData();
    Object.entries(form).forEach(([key, value]) => { if (value !== null) body.append(key, value); });
    try {
      const response = await api.post(item ? `/users/${item.id}` : '/users', body);
      onSaved(response.message);
    } catch (error) {
      setMessage(error instanceof ApiError ? error.message : 'Data gagal disimpan.');
      setErrors(error.errors);
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <Modal open onClose={onClose} title={item ? 'Edit pengguna' : 'Tambah pengguna'} subtitle={item ? 'Perbarui informasi akun pegawai.' : 'Buat akun baru untuk pegawai.'} size="large">
      <form className="entity-form" onSubmit={submit}>
        {message && <div className="alert alert-error">{message}</div>}
        <div className="form-grid two-columns">
          <label className="field"><span>Nama lengkap *</span><input value={form.name} onChange={(e) => update('name', e.target.value)} placeholder="Nama lengkap pegawai" /><FieldError errors={errors} name="name" /></label>
          <label className="field"><span>Alamat email *</span><input type="email" value={form.email} onChange={(e) => update('email', e.target.value)} placeholder="nama@email.com" /><FieldError errors={errors} name="email" /></label>
          <label className="field"><span>Jabatan *</span><input value={form.jabatan} onChange={(e) => update('jabatan', e.target.value)} placeholder="Contoh: Perawat" /><FieldError errors={errors} name="jabatan" /></label>
          <label className="field"><span>Nomor HP *</span><input value={form.nomor_hp} onChange={(e) => update('nomor_hp', e.target.value)} placeholder="08xxxxxxxxxx" /><FieldError errors={errors} name="nomor_hp" /></label>
          <label className="field"><span>Jadwal default</span><input value={form.jadwal} onChange={(e) => update('jadwal', e.target.value)} placeholder="Contoh: Non shift" /></label>
          <label className="field"><span>{item ? 'Password baru' : 'Password *'}</span><input type="password" autoComplete="new-password" value={form.password} onChange={(e) => update('password', e.target.value)} placeholder={item ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter'} /><FieldError errors={errors} name="password" /></label>
        </div>
        <label className="upload-field"><span className="upload-icon"><UserRound size={21} /></span><span><strong>Foto profil</strong><small>JPG, PNG, atau WebP · maks. 10 MB</small></span><input type="file" accept="image/jpeg,image/png,image/webp" onChange={(e) => update('foto', e.target.files?.[0] || null)} /><em>{form.foto?.name || (item?.foto ? 'Biarkan untuk memakai foto lama' : 'Pilih file')}</em></label>
        <div className="form-actions"><button type="button" className="button button-ghost" onClick={onClose}>Batal</button><button className="button button-primary" disabled={submitting}>{submitting ? 'Menyimpan…' : 'Simpan pengguna'}</button></div>
      </form>
    </Modal>
  );
}
