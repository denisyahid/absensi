import assert from 'node:assert/strict';

const endpoint = process.env.API_URL || 'http://127.0.0.1:8080/backend.php';
let token = '';

async function call(route, { method = 'GET', body, authenticated = true, expected = 200 } = {}) {
  const target = new URL(endpoint);
  const routeUrl = new URL(route, 'http://internal');
  target.searchParams.set('route', routeUrl.pathname.replace(/^\//, ''));
  routeUrl.searchParams.forEach((value, key) => target.searchParams.append(key, value));
  const headers = { Accept: 'application/json' };
  if (authenticated && token) headers.Authorization = `Bearer ${token}`;
  if (body && !(body instanceof FormData)) { headers['Content-Type'] = 'application/json'; body = JSON.stringify(body); }
  const response = await fetch(target, { method, headers, body });
  const payload = await response.json().catch(() => null);
  if (response.status !== expected) throw new Error(`${method} ${route}: expected ${expected}, got ${response.status}\n${JSON.stringify(payload, null, 2)}`);
  return payload;
}

const png = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', 'base64');
const dataImage = `data:image/png;base64,${png.toString('base64')}`;

console.log('1. health dan login');
const health = await call('/health', { authenticated: false });
assert.equal(health.data.status, 'ok');
const login = await call('/auth/login', { method: 'POST', authenticated: false, body: { email: 'deni@example.com', password: 'password', remember: false } });
token = login.data.token;
assert.equal(login.data.user.email, 'deni@example.com');

console.log('2. dashboard, profil, dan hak akses');
assert.equal((await call('/auth/me')).data.is_manager, true);
assert.ok((await call('/dashboard')).data.shifts.malam);
const access = await call('/access');
assert.ok(access.data.roles.some((role) => role.name === 'manajemen'));
assert.ok(access.data.permissions.some((permission) => permission.name === 'manage-users'));
assert.ok(access.data.permissions.some((permission) => permission.name === 'manage-logbooks'));

console.log('3. CRUD pengguna dan jadwal');
const userForm = new FormData();
for (const [key, value] of Object.entries({ name: 'Pegawai Integrasi', email: 'integration@example.com', jabatan: 'Perawat', nomor_hp: '081234567890', jadwal: 'Pagi', password: 'password123' })) userForm.append(key, value);
await call('/users', { method: 'POST', body: userForm, expected: 201 });
const userList = await call('/users?search=integration@example.com');
assert.equal(userList.data.length, 1);
const employee = userList.data[0];
await call('/schedules', { method: 'POST', body: { user_id: employee.id, date: '2026-09-02', status: 'Malam' } });
const schedule = await call('/schedules?month=9&year=2026');
assert.ok(schedule.data.schedules.some((item) => Number(item.user_id) === Number(employee.id) && item.status === 'Malam'));

console.log('4. CRUD role/permission dan enforcement permission langsung');
await call('/permissions', { method: 'POST', body: { name: 'permission-integrasi' }, expected: 201 });
await call('/roles', { method: 'POST', body: { name: 'role-integrasi' }, expected: 201 });
let updatedAccess = await call('/access');
const customPermission = updatedAccess.data.permissions.find((item) => item.name === 'permission-integrasi');
const customRole = updatedAccess.data.roles.find((item) => item.name === 'role-integrasi');
const manageUsers = updatedAccess.data.permissions.find((item) => item.name === 'manage-users');
await call(`/roles/${customRole.id}`, { method: 'PUT', body: { name: 'role-integrasi-updated' } });
await call(`/roles/${customRole.id}/permissions`, { method: 'POST', body: { permission_ids: [customPermission.id] } });
await call('/access/assign', { method: 'POST', body: { user_id: employee.id, role_ids: [], permission_ids: [manageUsers.id] } });
const managementToken = token;
const employeeLogin = await call('/auth/login', { method: 'POST', authenticated: false, body: { email: 'integration@example.com', password: 'password123' } });
token = employeeLogin.data.token;
assert.ok((await call('/users?search=integration@example.com')).data.length === 1);
await call('/access', { expected: 403 });
token = managementToken;
await call(`/roles/${customRole.id}`, { method: 'DELETE' });
await call(`/permissions/${customPermission.id}`, { method: 'DELETE' });

console.log('5. absensi masuk dan pulang');
await call('/attendance', { method: 'POST', body: { action: 'clock_in', shift: 'nonshift', photo: dataImage }, expected: 201 });
await call('/attendance', { method: 'POST', body: { action: 'clock_out', shift: 'nonshift', photo: dataImage } });
const afterAttendance = await call('/dashboard');
assert.ok(afterAttendance.data.attendance.jam_pulang);

console.log('6. CRUD logbook dan file privat');
const logForm = new FormData();
logForm.append('name', 'Kegiatan integrasi API'); logForm.append('keterangan', 'Pengujian alur logbook secara otomatis.'); logForm.append('tanggal', '2026-09-02'); logForm.append('foto', new Blob([png], { type: 'image/png' }), 'logbook.png');
await call('/logbooks', { method: 'POST', body: logForm, expected: 201 });
const logs = await call('/logbooks?month=9&year=2026&search=integrasi');
assert.equal(logs.data.length, 1);
const fileUrl = new URL(endpoint); fileUrl.searchParams.set('route', 'files'); fileUrl.searchParams.set('path', logs.data[0].foto); fileUrl.searchParams.set('token', token);
const fileResponse = await fetch(fileUrl); assert.equal(fileResponse.status, 200); assert.match(fileResponse.headers.get('content-type'), /^image\/png/);
await call(`/logbooks/${logs.data[0].id}`, { method: 'DELETE' });

console.log('7. CRUD dan konfirmasi perjalanan dinas');
const referralForm = new FormData();
const referralValues = { nama_rujukan: 'RS Tujuan Integrasi', alamat_rujukan: 'Jalan Pengujian Nomor 1', perihal: 'Rujukan Pasien', waktu: '08.00 WIB s.d selesai', tempat: 'Garut', biaya_perdin: '70000', alat_angkut: 'Roda Empat', tanggal_berangkat: '2026-09-02', tanggal_kembali: '2026-09-02', participants: JSON.stringify([{ user_id: login.data.user.id, pns_id: null }, { user_id: employee.id, pns_id: null }]) };
for (const [key, value] of Object.entries(referralValues)) referralForm.append(key, value);
await call('/referrals', { method: 'POST', body: referralForm, expected: 201 });
const referrals = await call('/referrals?search=Tujuan+Integrasi');
assert.equal(referrals.data.length, 1); assert.equal(referrals.data[0].participants.length, 2);
await call(`/referrals/${referrals.data[0].id}/confirm`, { method: 'POST', body: {} });
assert.equal((await call(`/referrals/${referrals.data[0].id}`)).data.status, 'confirmed');
await call(`/referrals/${referrals.data[0].id}`, { method: 'DELETE' });

console.log('8. laporan');
const report = await call('/reports/attendance?month=9&year=2026');
assert.ok(report.data.attendance.length >= 1);
await call('/reports/logbooks?month=9&year=2026');

console.log('9. reset password dan invalidasi token');
const forgot = await call('/auth/forgot-password', { method: 'POST', authenticated: false, body: { email: 'deni@example.com' } });
assert.ok(forgot.debug_reset_url, 'APP_DEBUG harus mengembalikan URL reset pada CI');
const resetUrl = new URL(forgot.debug_reset_url);
await call('/auth/reset-password', { method: 'POST', authenticated: false, body: { email: 'deni@example.com', token: resetUrl.searchParams.get('token'), password: 'password-baru', password_confirmation: 'password-baru' } });
await call('/auth/me', { expected: 401 });
const relogin = await call('/auth/login', { method: 'POST', authenticated: false, body: { email: 'deni@example.com', password: 'password-baru' } });
assert.ok(relogin.data.token);

console.log('Semua smoke test API berhasil.');
