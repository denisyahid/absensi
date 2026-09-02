import { useEffect, useState } from 'react';
import { NavLink, Outlet, useLocation } from 'react-router-dom';
import {
  BookOpenText,
  CalendarDays,
  ChevronDown,
  ClipboardCheck,
  FileBarChart,
  LayoutDashboard,
  LogOut,
  Menu,
  Settings,
  ShieldCheck,
  Stethoscope,
  Users,
  X,
} from 'lucide-react';
import { Avatar } from './Common';
import { useAuth } from '../auth';
import { api } from '../api';

const primaryNav = [
  { to: '/', label: 'Dashboard', icon: LayoutDashboard },
  { to: '/jadwal', label: 'Jadwal', icon: CalendarDays },
  { to: '/logbook', label: 'Logbook', icon: BookOpenText },
  { to: '/perjalanan-dinas', label: 'Perjalanan Dinas', icon: Stethoscope },
  { to: '/laporan', label: 'Laporan', icon: FileBarChart },
];

export default function Layout() {
  const { user, logout } = useAuth();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [profileOpen, setProfileOpen] = useState(false);
  const location = useLocation();

  useEffect(() => {
    setSidebarOpen(false);
    setProfileOpen(false);
  }, [location.pathname]);

  const can = (permission) => Boolean(user?.is_manager || user?.permissions?.includes(permission));
  const managementNav = [
    ...(can('manage-users') ? [{ to: '/pengguna', label: 'Data Pengguna', icon: Users }] : []),
    ...(can('manage-access') ? [{ to: '/hak-akses', label: 'Hak Akses', icon: ShieldCheck }] : []),
  ];
  const nav = [...primaryNav.slice(0, 1), ...managementNav, ...primaryNav.slice(1)];

  return (
    <div className="app-shell">
      {sidebarOpen && <button className="sidebar-scrim" aria-label="Tutup menu" onClick={() => setSidebarOpen(false)} />}
      <aside className={`sidebar ${sidebarOpen ? 'sidebar-open' : ''}`}>
        <div className="brand">
          <span className="brand-mark"><ClipboardCheck size={23} /></span>
          <div><strong>SiHadir</strong><small>RSUD Malangbong</small></div>
          <button type="button" className="sidebar-close" onClick={() => setSidebarOpen(false)}><X size={20} /></button>
        </div>

        <div className="sidebar-label">MENU UTAMA</div>
        <nav className="sidebar-nav">
          {nav.map(({ to, label, icon: Icon }) => (
            <NavLink key={to} to={to} end={to === '/'} className={({ isActive }) => isActive ? 'nav-item active' : 'nav-item'}>
              <Icon size={19} /><span>{label}</span>
            </NavLink>
          ))}
        </nav>

        <div className="sidebar-bottom">
          <div className="help-card">
            <span className="help-card-icon"><ClipboardCheck size={20} /></span>
            <strong>Absensi lebih ringkas</strong>
            <p>Pastikan foto dan shift sudah tepat sebelum menyimpan.</p>
          </div>
          <button type="button" className="nav-item logout-nav" onClick={logout}><LogOut size={19} /><span>Keluar</span></button>
        </div>
      </aside>

      <div className="main-column">
        <header className="topbar">
          <button type="button" className="menu-button" onClick={() => setSidebarOpen(true)} aria-label="Buka menu"><Menu size={22} /></button>
          <div className="topbar-context">
            <span>Sistem Informasi Kehadiran</span>
            <strong>RSUD Malangbong</strong>
          </div>
          <div className="topbar-actions">
            <div className="profile-menu">
              <button type="button" className="profile-trigger" onClick={() => setProfileOpen((open) => !open)}>
                <Avatar user={user} size="small" />
                <span><strong>{user?.name}</strong><small>{user?.jabatan || 'Pegawai'}</small></span>
                <ChevronDown size={16} />
              </button>
              {profileOpen && (
                <div className="profile-dropdown">
                  <NavLink to="/profil"><Settings size={17} /> Pengaturan profil</NavLink>
                  <button type="button" onClick={logout}><LogOut size={17} /> Keluar</button>
                </div>
              )}
            </div>
          </div>
        </header>
        {!user?.email_verified && <VerificationBanner />}
        <main className="page-content"><Outlet /></main>
        <footer className="app-footer">SiHadir · Sistem internal RSUD Malangbong</footer>
      </div>
    </div>
  );
}

function VerificationBanner() {
  const [state, setState] = useState({ sending: false, message: '', url: '' });
  async function send() {
    setState({ sending: true, message: '', url: '' });
    try {
      const response = await api.post('/auth/verification/send', {});
      setState({ sending: false, message: response.message, url: response.debug_verification_url || '' });
    } catch (error) {
      setState({ sending: false, message: error.message, url: '' });
    }
  }
  return <div className="verification-banner">
    <span>Alamat email belum diverifikasi. {state.message}</span>
    {state.url && <a href={state.url}>Verifikasi sekarang</a>}
    <button type="button" onClick={send} disabled={state.sending}>{state.sending ? 'Mengirim…' : 'Kirim tautan verifikasi'}</button>
  </div>;
}
