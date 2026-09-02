import { useCallback, useEffect, useState } from 'react';
import { CalendarDays, Camera, Pencil, Plus, Printer, Search, Trash2 } from 'lucide-react';
import { Link } from 'react-router-dom';
import { ApiError, api, queryString } from '../api';
import { Avatar, EmptyState, FieldError, formatDate, LoadingScreen, Modal, PageHeading, Pagination, todayInput } from '../components/Common';

const initialForm = { name: '', keterangan: '', tanggal: todayInput(), foto: null };

export default function LogbooksPage() {
  const now = new Date();
  const [filters, setFilters] = useState({ search: '', month: now.getMonth() + 1, year: now.getFullYear(), page: 1 });
  const [items, setItems] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [modal, setModal] = useState(null);
  const [notice, setNotice] = useState(null);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const response = await api.get(`/logbooks?${queryString(filters)}`);
      setItems(response.data);
      setMeta(response.meta);
    } catch (error) {
      setNotice({ tone: 'error', text: error.message });
    } finally {
      setLoading(false);
    }
  }, [filters]);

  useEffect(() => { const timeout = setTimeout(load, 200); return () => clearTimeout(timeout); }, [load]);

  async function remove(item) {
    if (!window.confirm(`Hapus logbook “${item.name}”?`)) return;
    try {
      const response = await api.delete(`/logbooks/${item.id}`);
      setNotice({ tone: 'success', text: response.message });
      load();
    } catch (error) { setNotice({ tone: 'error', text: error.message }); }
  }

  return (
    <div>
      <PageHeading eyebrow="AKTIVITAS HARIAN" title="Logbook pegawai" description="Dokumentasikan kegiatan dan bukti pekerjaan harian." actions={<><Link className="button button-secondary" to={`/cetak/logbook?month=${filters.month}&year=${filters.year}`} target="_blank"><Printer size={17} /> Cetak laporan</Link><button className="button button-primary" onClick={() => setModal({ type: 'create' })}><Plus size={18} /> Isi logbook</button></>} />
      {notice && <div className={`alert alert-${notice.tone}`}>{notice.text}<button onClick={() => setNotice(null)}>×</button></div>}
      <section className="panel filter-panel logbook-filters">
        <label className="search-field"><Search size={18} /><input placeholder="Cari kegiatan atau pegawai…" value={filters.search} onChange={(e) => setFilters({ ...filters, search: e.target.value, page: 1 })} /></label>
        <select value={filters.month} onChange={(e) => setFilters({ ...filters, month: Number(e.target.value), page: 1 })}>{Array.from({ length: 12 }, (_, index) => <option value={index + 1} key={index}>{new Date(2026, index, 1).toLocaleDateString('id-ID', { month: 'long' })}</option>)}</select>
        <select value={filters.year} onChange={(e) => setFilters({ ...filters, year: Number(e.target.value), page: 1 })}>{Array.from({ length: 5 }, (_, index) => <option key={index}>{now.getFullYear() - 2 + index}</option>)}</select>
      </section>

      {loading ? <section className="panel"><LoadingScreen compact /></section> : items.length ? (
        <section className="logbook-grid">
          {items.map((item) => (
            <article className="logbook-card" key={item.id}>
              <div className="logbook-image"><img src={api.fileUrl(item.foto)} alt={item.name} /><span><CalendarDays size={14} />{formatDate(item.tanggal)}</span></div>
              <div className="logbook-body">
                <div className="logbook-author"><Avatar user={{ name: item.user_name, foto: item.user_foto }} size="small" /><span><strong>{item.user_name}</strong><small>{item.user_jabatan || 'Pegawai'}</small></span></div>
                <h2>{item.name}</h2><p>{item.keterangan}</p>
                <div className="card-actions"><button className="button button-ghost button-small" onClick={() => setModal({ type: 'edit', item })}><Pencil size={15} /> Edit</button><button className="button button-danger-ghost button-small" onClick={() => remove(item)}><Trash2 size={15} /> Hapus</button></div>
              </div>
            </article>
          ))}
        </section>
      ) : <section className="panel"><EmptyState title="Belum ada logbook" description="Isi kegiatan pertama Anda untuk periode ini." action={<button className="button button-primary button-small" onClick={() => setModal({ type: 'create' })}><Plus size={16} /> Isi logbook</button>} /></section>}
      <Pagination meta={meta} onPage={(page) => setFilters({ ...filters, page })} />
      <LogbookForm modal={modal} onClose={() => setModal(null)} onSaved={(message) => { setModal(null); setNotice({ tone: 'success', text: message }); load(); }} />
    </div>
  );
}

function LogbookForm({ modal, onClose, onSaved }) {
  const item = modal?.item;
  const [form, setForm] = useState(initialForm);
  const [errors, setErrors] = useState(null);
  const [message, setMessage] = useState('');
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (modal) setForm(item ? { name: item.name, keterangan: item.keterangan, tanggal: item.tanggal, foto: null } : initialForm);
    setErrors(null); setMessage('');
  }, [modal, item]);

  if (!modal) return null;
  const update = (name, value) => setForm((current) => ({ ...current, [name]: value }));

  async function submit(event) {
    event.preventDefault();
    const body = new FormData();
    Object.entries(form).forEach(([key, value]) => { if (value !== null) body.append(key, value); });
    setSubmitting(true); setMessage(''); setErrors(null);
    try {
      const response = await api.post(item ? `/logbooks/${item.id}` : '/logbooks', body);
      onSaved(response.message);
    } catch (error) {
      setMessage(error instanceof ApiError ? error.message : 'Logbook gagal disimpan.');
      setErrors(error.errors);
    } finally { setSubmitting(false); }
  }

  return (
    <Modal open onClose={onClose} title={item ? 'Edit logbook' : 'Isi logbook'} subtitle="Catat pekerjaan yang telah dilakukan hari ini." size="large">
      <form className="entity-form" onSubmit={submit}>
        {message && <div className="alert alert-error">{message}</div>}
        <div className="form-grid two-columns">
          <label className="field"><span>Nama kegiatan *</span><input value={form.name} onChange={(e) => update('name', e.target.value)} placeholder="Contoh: Serah terima pasien" /><FieldError errors={errors} name="name" /></label>
          <label className="field"><span>Tanggal *</span><input type="date" value={form.tanggal} onChange={(e) => update('tanggal', e.target.value)} /><FieldError errors={errors} name="tanggal" /></label>
        </div>
        <label className="field"><span>Keterangan *</span><textarea rows="5" value={form.keterangan} onChange={(e) => update('keterangan', e.target.value)} placeholder="Jelaskan aktivitas yang dilakukan…" /><FieldError errors={errors} name="keterangan" /></label>
        <label className="upload-field"><span className="upload-icon"><Camera size={21} /></span><span><strong>{item ? 'Ganti foto kegiatan' : 'Foto kegiatan *'}</strong><small>JPG, PNG, atau WebP · maks. 10 MB</small></span><input type="file" capture="environment" accept="image/jpeg,image/png,image/webp" onChange={(e) => update('foto', e.target.files?.[0] || null)} /><em>{form.foto?.name || (item ? 'Gunakan foto lama' : 'Ambil foto')}</em></label>
        <FieldError errors={errors} name="foto" />
        <div className="form-actions"><button type="button" className="button button-ghost" onClick={onClose}>Batal</button><button className="button button-primary" disabled={submitting}>{submitting ? 'Menyimpan…' : 'Simpan logbook'}</button></div>
      </form>
    </Modal>
  );
}
