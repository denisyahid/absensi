import { useEffect, useState } from 'react';
import { Camera, KeyRound, Mail, Phone, Save, ShieldCheck, UserRound } from 'lucide-react';
import { ApiError, api } from '../api';
import { Avatar, FieldError, PageHeading } from '../components/Common';
import { useAuth } from '../auth';

export default function ProfilePage() {
  const { user, updateUser } = useAuth();
  const [form, setForm] = useState({ name: '', email: '', nomor_hp: '', current_password: '', password: '', foto: null });
  const [errors, setErrors] = useState(null);
  const [notice, setNotice] = useState(null);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => setForm((current) => ({ ...current, name: user?.name || '', email: user?.email || '', nomor_hp: user?.nomor_hp || '' })), [user]);
  const set = (name, value) => setForm((current) => ({ ...current, [name]: value }));

  async function submit(event) {
    event.preventDefault(); setSubmitting(true); setErrors(null); setNotice(null);
    const body = new FormData(); Object.entries(form).forEach(([key, value]) => { if (value !== null) body.append(key, value); });
    try {
      const response = await api.post('/profile', body);
      updateUser(response.data); setNotice({ tone: 'success', text: response.message }); setForm((current) => ({ ...current, current_password: '', password: '', foto: null }));
    } catch (error) { setNotice({ tone: 'error', text: error instanceof ApiError ? error.message : 'Profil gagal diperbarui.' }); setErrors(error.errors); }
    finally { setSubmitting(false); }
  }

  return <div>
    <PageHeading eyebrow="AKUN SAYA" title="Pengaturan profil" description="Perbarui informasi pribadi dan keamanan akun." />
    {notice && <div className={`alert alert-${notice.tone}`}>{notice.text}</div>}
    <form className="profile-layout" onSubmit={submit}>
      <aside className="panel profile-summary">
        <div className="profile-avatar-wrap"><Avatar user={user} size="xxlarge" /><label><Camera size={17} /><input type="file" accept="image/jpeg,image/png,image/webp" onChange={(e) => set('foto', e.target.files?.[0] || null)} /></label></div>
        <h2>{user?.name}</h2><p>{user?.email}</p><span className="role-badge"><ShieldCheck size={14} />{user?.jabatan || 'Pegawai'}</span>
        {form.foto && <small className="selected-file">Foto baru: {form.foto.name}</small>}
      </aside>
      <div className="profile-forms">
        <section className="panel entity-form">
          <div className="panel-heading"><div><span className="eyebrow">INFORMASI PRIBADI</span><h2>Detail akun</h2></div></div>
          <div className="form-grid two-columns">
            <label className="field"><span><UserRound size={15} /> Nama lengkap</span><input value={form.name} onChange={(e) => set('name', e.target.value)} /><FieldError errors={errors} name="name" /></label>
            <label className="field"><span><Mail size={15} /> Alamat email</span><input type="email" value={form.email} onChange={(e) => set('email', e.target.value)} /><FieldError errors={errors} name="email" /></label>
            <label className="field"><span><Phone size={15} /> Nomor HP</span><input value={form.nomor_hp} onChange={(e) => set('nomor_hp', e.target.value)} /></label>
            <label className="field field-readonly"><span><ShieldCheck size={15} /> Jabatan</span><input value={user?.jabatan || ''} readOnly /><small>Jabatan hanya dapat diubah manajemen.</small></label>
          </div>
        </section>
        <section className="panel entity-form">
          <div className="panel-heading"><div><span className="eyebrow">KEAMANAN</span><h2>Ganti password</h2><p>Kosongkan bila tidak ingin mengganti password.</p></div></div>
          <div className="form-grid two-columns">
            <label className="field"><span><KeyRound size={15} /> Password saat ini</span><input type="password" autoComplete="current-password" value={form.current_password} onChange={(e) => set('current_password', e.target.value)} /><FieldError errors={errors} name="current_password" /></label>
            <label className="field"><span><KeyRound size={15} /> Password baru</span><input type="password" autoComplete="new-password" value={form.password} onChange={(e) => set('password', e.target.value)} placeholder="Minimal 6 karakter" /><FieldError errors={errors} name="password" /></label>
          </div>
          <div className="form-actions"><button className="button button-primary" disabled={submitting}><Save size={17} />{submitting ? 'Menyimpan…' : 'Simpan perubahan'}</button></div>
        </section>
      </div>
    </form>
  </div>;
}
