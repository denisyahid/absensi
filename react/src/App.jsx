import { useCallback, useEffect, useMemo, useState } from 'react';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { api } from './api';
import { AuthContext } from './auth';
import Layout from './components/Layout';
import { LoadingScreen } from './components/Common';
import LoginPage from './pages/LoginPage';
import DashboardPage from './pages/DashboardPage';
import UsersPage from './pages/UsersPage';
import SchedulesPage from './pages/SchedulesPage';
import LogbooksPage from './pages/LogbooksPage';
import ReportsPage from './pages/ReportsPage';
import ReferralsPage from './pages/ReferralsPage';
import ProfilePage from './pages/ProfilePage';
import AccessPage from './pages/AccessPage';
import { ForgotPasswordPage, ResetPasswordPage, VerifyEmailPage } from './pages/PasswordPages';
import { AmbulancePrintPage, AttendancePrintPage, LogbookPrintPage, ReferralPrintPage, SchedulePrintPage } from './pages/PrintPages';

export default function App() {
  const [user, setUser] = useState(null);
  const [checking, setChecking] = useState(Boolean(api.token.get()));

  const clearSession = useCallback(() => {
    api.token.clear();
    setUser(null);
    setChecking(false);
  }, []);

  useEffect(() => {
    const token = api.token.get();
    if (!token) { setChecking(false); return; }
    api.get('/auth/me').then((response) => setUser(response.data)).catch(clearSession).finally(() => setChecking(false));
  }, [clearSession]);

  useEffect(() => {
    window.addEventListener('auth:expired', clearSession);
    return () => window.removeEventListener('auth:expired', clearSession);
  }, [clearSession]);

  const auth = useMemo(() => ({
    user,
    login(token, authenticatedUser) { api.token.set(token); setUser(authenticatedUser); },
    async logout() { try { await api.post('/auth/logout', {}); } catch { /* token tetap dibersihkan */ } clearSession(); },
    updateUser: setUser,
  }), [user, clearSession]);

  if (checking) return <LoadingScreen label="Memeriksa sesi…" />;

  const can = (permission) => Boolean(user?.is_manager || user?.permissions?.includes(permission));

  return (
    <AuthContext.Provider value={auth}>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={user ? <Navigate to="/" replace /> : <LoginPage />} />
          <Route path="/forgot-password" element={user ? <Navigate to="/" replace /> : <ForgotPasswordPage />} />
          <Route path="/reset-password" element={user ? <Navigate to="/" replace /> : <ResetPasswordPage />} />
          <Route path="/verify-email" element={<VerifyEmailPage />} />
          <Route path="/cetak/sppd/:id" element={user ? <ReferralPrintPage /> : <Navigate to="/login" replace />} />
          <Route path="/cetak/sppd-srikandi/:id" element={user ? <ReferralPrintPage variant="srikandi" /> : <Navigate to="/login" replace />} />
          <Route path="/cetak/logbook" element={user ? <LogbookPrintPage /> : <Navigate to="/login" replace />} />
          <Route path="/cetak/laporan-kehadiran" element={user ? <AttendancePrintPage /> : <Navigate to="/login" replace />} />
          <Route path="/cetak/laporan-jadwal" element={user ? <SchedulePrintPage /> : <Navigate to="/login" replace />} />
          <Route path="/cetak/bukti-ambulance" element={user ? <AmbulancePrintPage /> : <Navigate to="/login" replace />} />
          <Route element={user ? <Layout /> : <Navigate to="/login" replace />}>
            <Route index element={<DashboardPage />} />
            <Route path="jadwal" element={<SchedulesPage />} />
            <Route path="logbook" element={<LogbooksPage />} />
            <Route path="perjalanan-dinas" element={<ReferralsPage />} />
            <Route path="laporan" element={<ReportsPage />} />
            <Route path="profil" element={<ProfilePage />} />
            <Route path="pengguna" element={can('manage-users') ? <UsersPage /> : <Navigate to="/" replace />} />
            <Route path="hak-akses" element={can('manage-access') ? <AccessPage /> : <Navigate to="/" replace />} />
          </Route>
          <Route path="*" element={<Navigate to={user ? '/' : '/login'} replace />} />
        </Routes>
      </BrowserRouter>
    </AuthContext.Provider>
  );
}
