import { useEffect, useState } from 'react';
import { ArrowLeft, ArrowRight, CheckCircle2, ClipboardCheck, KeyRound, Mail } from 'lucide-react';
import { Link, useSearchParams } from 'react-router-dom';
import { ApiError, api } from '../api';
import { FieldError } from '../components/Common';

function AuthFrame({ eyebrow, title, description, children }) {
  return <main className="auth-simple-page">
    <section className="auth-simple-card">
      <Link className="brand auth-brand" to="/login"><span className="brand-mark"><ClipboardCheck size={23} /></span><div><strong>SiHadir</strong><small>RSUD Malangbong</small></div></Link>
      <div className="login-heading"><span className="eyebrow">{eyebrow}</span><h2>{title}</h2><p>{description}</p></div>
      {children}
    </section>
  </main>;
}

export function ForgotPasswordPage() {
  const [email, setEmail] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [notice, setNotice] = useState(null);
  const [errors, setErrors] = useState(null);

  async function submit(event) {
    event.preventDefault(); setSubmitting(true); setNotice(null); setErrors(null);
    try {
      const response = await api.post('/auth/forgot-password', { email });
      setNotice({ tone: 'success', text: response.message, url: response.debug_reset_url });
    } catch (error) { setNotice({ tone: 'error', text: error instanceof ApiError ? error.message : 'Permintaan gagal.' }); setErrors(error.errors); }
    finally { setSubmitting(false); }
  }

  return <AuthFrame eyebrow="PEMULIHAN AKUN" title="Lupa password?" description="Masukkan email akun. Kami akan mengirim tautan untuk membuat password baru.">
    {notice && <div className={`alert alert-${notice.tone}`}>{notice.text}</div>}
    {notice?.url && <a className="debug-link" href={notice.url}>Buka tautan reset development <ArrowRight size={15} /></a>}
    <form onSubmit={submit}>
      <label className="field"><span><Mail size={15} /> Alamat email</span><input type="email" autoFocus value={email} onChange={(e) => setEmail(e.target.value)} placeholder="nama@email.com" /><FieldError errors={errors} name="email" /></label>
      <button className="button button-primary button-login auth-submit" disabled={submitting}>{submitting ? 'Mengirim…' : <>Kirim tautan reset <ArrowRight size={17} /></>}</button>
    </form>
    <Link className="back-login" to="/login"><ArrowLeft size={16} /> Kembali ke halaman login</Link>
  </AuthFrame>;
}

export function ResetPasswordPage() {
  const [params] = useSearchParams();
  const [form, setForm] = useState({ token: params.get('token') || '', email: params.get('email') || '', password: '', password_confirmation: '' });
  const [submitting, setSubmitting] = useState(false);
  const [notice, setNotice] = useState(null);
  const [errors, setErrors] = useState(null);
  const [done, setDone] = useState(false);

  async function submit(event) {
    event.preventDefault(); setSubmitting(true); setNotice(null); setErrors(null);
    try { const response = await api.post('/auth/reset-password', form); setNotice({ tone: 'success', text: response.message }); setDone(true); }
    catch (error) { setNotice({ tone: 'error', text: error instanceof ApiError ? error.message : 'Reset password gagal.' }); setErrors(error.errors); }
    finally { setSubmitting(false); }
  }

  return <AuthFrame eyebrow="PASSWORD BARU" title="Buat password baru" description="Gunakan minimal 8 karakter dan jangan gunakan password lama.">
    {notice && <div className={`alert alert-${notice.tone}`}>{notice.text}</div>}
    {done ? <Link className="button button-primary button-login" to="/login"><CheckCircle2 size={18} /> Masuk dengan password baru</Link> : <form onSubmit={submit} className="entity-form">
      <label className="field"><span><Mail size={15} /> Alamat email</span><input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} /><FieldError errors={errors} name="email" /></label>
      <label className="field"><span><KeyRound size={15} /> Password baru</span><input type="password" autoComplete="new-password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} /><FieldError errors={errors} name="password" /></label>
      <label className="field"><span><KeyRound size={15} /> Ulangi password</span><input type="password" autoComplete="new-password" value={form.password_confirmation} onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })} /><FieldError errors={errors} name="password_confirmation" /></label>
      <button className="button button-primary button-login" disabled={submitting}>{submitting ? 'Menyimpan…' : 'Simpan password baru'}</button>
    </form>}
    <Link className="back-login" to="/login"><ArrowLeft size={16} /> Kembali ke halaman login</Link>
  </AuthFrame>;
}

export function VerifyEmailPage() {
  const [params] = useSearchParams();
  const [state, setState] = useState({ loading: true, success: false, message: 'Memverifikasi alamat email…' });
  useEffect(() => {
    const query = new URLSearchParams({ id: params.get('id') || '', token: params.get('token') || '' });
    api.get(`/auth/verification/verify?${query}`).then((response) => setState({ loading: false, success: true, message: response.message })).catch((error) => setState({ loading: false, success: false, message: error.message }));
  }, [params]);
  return <AuthFrame eyebrow="VERIFIKASI EMAIL" title={state.loading ? 'Mohon tunggu' : state.success ? 'Email terverifikasi' : 'Verifikasi gagal'} description={state.message}>
    <div className={`verification-symbol ${state.success ? 'success' : ''}`}>{state.success ? <CheckCircle2 size={36} /> : <Mail size={36} />}</div>
    <Link className="button button-primary button-login" to="/login">Kembali ke aplikasi</Link>
  </AuthFrame>;
}
