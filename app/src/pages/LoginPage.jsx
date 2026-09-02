import { useState } from 'react';
import { ArrowRight, CheckCircle2, ClipboardCheck, Eye, EyeOff, ShieldCheck } from 'lucide-react';
import { ApiError, api } from '../api';
import { FieldError } from '../components/Common';
import { useAuth } from '../auth';

export default function LoginPage() {
  const { login } = useAuth();
  const [form, setForm] = useState({ email: '', password: '', remember: true });
  const [showPassword, setShowPassword] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [errors, setErrors] = useState(null);

  async function handleSubmit(event) {
    event.preventDefault();
    setSubmitting(true);
    setError('');
    setErrors(null);
    try {
      const response = await api.post('/auth/login', form);
      login(response.data.token, response.data.user);
    } catch (caught) {
      setError(caught instanceof ApiError ? caught.message : 'Login gagal diproses.');
      setErrors(caught.errors);
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <main className="login-page">
      <section className="login-showcase">
        <div className="login-glow login-glow-one" />
        <div className="login-glow login-glow-two" />
        <div className="showcase-content">
          <div className="brand brand-light">
            <span className="brand-mark"><ClipboardCheck size={23} /></span>
            <div><strong>SiHadir</strong><small>RSUD Malangbong</small></div>
          </div>
          <div className="showcase-copy">
            <span className="showcase-pill"><ShieldCheck size={15} /> Portal internal pegawai</span>
            <h1>Satu ruang kerja untuk kehadiran dan aktivitas harian.</h1>
            <p>Catat absensi, logbook, jadwal, dan perjalanan dinas secara lebih sederhana dan terorganisasi.</p>
            <div className="showcase-points">
              <span><CheckCircle2 size={18} /> Rekap kehadiran terpusat</span>
              <span><CheckCircle2 size={18} /> Akses aman sesuai peran</span>
              <span><CheckCircle2 size={18} /> Nyaman di desktop dan ponsel</span>
            </div>
          </div>
          <p className="showcase-footer">© {new Date().getFullYear()} RSUD Malangbong</p>
        </div>
      </section>

      <section className="login-panel">
        <div className="mobile-login-brand brand">
          <span className="brand-mark"><ClipboardCheck size={23} /></span>
          <div><strong>SiHadir</strong><small>RSUD Malangbong</small></div>
        </div>
        <form className="login-card" onSubmit={handleSubmit}>
          <div className="login-heading">
            <span className="eyebrow">SELAMAT DATANG</span>
            <h2>Masuk ke akun Anda</h2>
            <p>Gunakan akun pegawai yang telah terdaftar.</p>
          </div>

          {error && <div className="alert alert-error">{error}</div>}

          <label className="field">
            <span>Alamat email</span>
            <input type="email" autoComplete="email" placeholder="nama@rsudmalangbong.id" value={form.email} onChange={(event) => setForm({ ...form, email: event.target.value })} />
            <FieldError errors={errors} name="email" />
          </label>

          <label className="field">
            <span>Password</span>
            <div className="password-field">
              <input type={showPassword ? 'text' : 'password'} autoComplete="current-password" placeholder="Masukkan password" value={form.password} onChange={(event) => setForm({ ...form, password: event.target.value })} />
              <button type="button" aria-label={showPassword ? 'Sembunyikan password' : 'Tampilkan password'} onClick={() => setShowPassword((show) => !show)}>
                {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
              </button>
            </div>
            <FieldError errors={errors} name="password" />
          </label>

          <div className="login-options">
            <label className="check-field">
              <input type="checkbox" checked={form.remember} onChange={(event) => setForm({ ...form, remember: event.target.checked })} />
              <span>Ingat saya di perangkat ini</span>
            </label>
            <a href="/forgot-password">Lupa password?</a>
          </div>

          <button className="button button-primary button-login" type="submit" disabled={submitting}>
            {submitting ? 'Memeriksa akun…' : <>Masuk sekarang <ArrowRight size={18} /></>}
          </button>

          <div className="login-help">
            <strong>Kesulitan masuk?</strong>
            <p>Hubungi administrator untuk mereset password atau mengaktifkan akun.</p>
          </div>
        </form>
      </section>
    </main>
  );
}
