import { useCallback, useEffect, useState } from 'react';
import { KeyRound, Pencil, Plus, Save, ShieldCheck, Trash2, UserCog } from 'lucide-react';
import { api } from '../api';
import { Avatar, EmptyState, LoadingScreen, Modal, PageHeading, StatusBadge } from '../components/Common';

export default function AccessPage() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [tab, setTab] = useState('users');
  const [editingUser, setEditingUser] = useState(null);
  const [notice, setNotice] = useState(null);

  const load = useCallback(async () => {
    setLoading(true);
    try { const response = await api.get('/access'); setData(response.data); }
    catch (error) { setNotice({ tone: 'error', text: error.message }); }
    finally { setLoading(false); }
  }, []);
  useEffect(() => { load(); }, [load]);

  async function create(type) {
    const label = type === 'roles' ? 'role' : 'permission';
    const name = window.prompt(`Nama ${label} baru:`)?.trim();
    if (!name) return;
    try { const response = await api.post(`/${type}`, { name }); setNotice({ tone: 'success', text: response.message }); load(); }
    catch (error) { setNotice({ tone: 'error', text: error.message }); }
  }
  async function rename(type, item) {
    const name = window.prompt('Nama baru:', item.name)?.trim();
    if (!name || name === item.name) return;
    try { const response = await api.post(`/${type}/${item.id}`, { name }); setNotice({ tone: 'success', text: response.message }); load(); }
    catch (error) { setNotice({ tone: 'error', text: error.message }); }
  }
  async function remove(type, item) {
    if (!window.confirm(`Hapus ${item.name}?`)) return;
    try { const response = await api.delete(`/${type}/${item.id}`); setNotice({ tone: 'success', text: response.message }); load(); }
    catch (error) { setNotice({ tone: 'error', text: error.message }); }
  }
  async function toggleRolePermission(role, permissionId) {
    const ids = role.permission_ids.includes(permissionId) ? role.permission_ids.filter((id) => id !== permissionId) : [...role.permission_ids, permissionId];
    setData((current) => ({ ...current, roles: current.roles.map((candidate) => candidate.id === role.id ? { ...candidate, permission_ids: ids } : candidate) }));
    try { const response = await api.post(`/roles/${role.id}/permissions`, { permission_ids: ids }); setNotice({ tone: 'success', text: response.message }); }
    catch (error) { setNotice({ tone: 'error', text: error.message }); load(); }
  }

  return <div>
    <PageHeading eyebrow="OTORISASI" title="Role dan permission" description="Atur hak akses pengguna secara terpusat dan kompatibel dengan Spatie Permission." />
    {notice && <div className={`alert alert-${notice.tone}`}>{notice.text}<button onClick={() => setNotice(null)}>×</button></div>}
    <div className="access-tabs"><button className={tab === 'users' ? 'active' : ''} onClick={() => setTab('users')}><UserCog size={17}/>Pengguna</button><button className={tab === 'roles' ? 'active' : ''} onClick={() => setTab('roles')}><ShieldCheck size={17}/>Role</button><button className={tab === 'permissions' ? 'active' : ''} onClick={() => setTab('permissions')}><KeyRound size={17}/>Permission</button></div>
    {loading ? <section className="panel"><LoadingScreen compact /></section> : tab === 'users' ? <UsersAccess data={data} onEdit={setEditingUser}/> : tab === 'roles' ? <RolesAccess data={data} onCreate={() => create('roles')} onRename={(item) => rename('roles', item)} onRemove={(item) => remove('roles', item)} onToggle={toggleRolePermission}/> : <PermissionsAccess data={data} onCreate={() => create('permissions')} onRename={(item) => rename('permissions', item)} onRemove={(item) => remove('permissions', item)}/>} 
    <AssignAccessModal user={editingUser} roles={data?.roles || []} permissions={data?.permissions || []} onClose={() => setEditingUser(null)} onSaved={(message) => { setEditingUser(null); setNotice({ tone: 'success', text: message }); load(); }}/>
  </div>;
}

function UsersAccess({ data, onEdit }) {
  return <section className="panel table-panel"><div className="table-scroll"><table className="data-table"><thead><tr><th>Pengguna</th><th>Jabatan</th><th>Role</th><th>Permission langsung</th><th/></tr></thead><tbody>{data.users.map((user) => <tr key={user.id}><td><div className="user-cell"><Avatar user={user}/><div><strong>{user.name}</strong><span>{user.email}</span></div></div></td><td>{user.jabatan || '—'}</td><td><div className="badge-list">{user.role_ids.length ? user.role_ids.map((id) => <StatusBadge tone="info" key={id}>{data.roles.find((r) => r.id === id)?.name}</StatusBadge>) : <span className="muted">Tanpa role</span>}</div></td><td>{user.permission_ids.length ? `${user.permission_ids.length} permission` : <span className="muted">Mengikuti role</span>}</td><td><button className="button button-ghost button-small" onClick={() => onEdit(user)}><UserCog size={15}/>Atur</button></td></tr>)}</tbody></table></div></section>;
}

function RolesAccess({ data, onCreate, onRename, onRemove, onToggle }) {
  return <><div className="section-actions"><p>Permission pada role otomatis diwarisi semua pengguna yang memiliki role tersebut.</p><button className="button button-primary" onClick={onCreate}><Plus size={17}/>Tambah role</button></div><div className="role-grid">{data.roles.map((role) => <article className="panel role-card" key={role.id}><header><span className="stat-icon stat-green"><ShieldCheck size={20}/></span><div><h2>{role.name}</h2><small>{role.permission_ids.length} permission</small></div><div className="row-actions"><button className="icon-button" onClick={() => onRename(role)}><Pencil size={15}/></button><button className="icon-button danger" onClick={() => onRemove(role)}><Trash2 size={15}/></button></div></header><div className="permission-checks">{data.permissions.map((permission) => <label key={permission.id}><input type="checkbox" checked={role.permission_ids.includes(permission.id)} onChange={() => onToggle(role, permission.id)}/><span>{permission.name}</span></label>)}</div></article>)}</div></>;
}

function PermissionsAccess({ data, onCreate, onRename, onRemove }) {
  return <section className="panel permission-panel"><div className="section-actions"><p>Permission adalah kemampuan spesifik yang dapat diberikan melalui role atau langsung.</p><button className="button button-primary" onClick={onCreate}><Plus size={17}/>Tambah permission</button></div>{data.permissions.length ? <div className="permission-list">{data.permissions.map((permission) => <div key={permission.id}><span><KeyRound size={16}/><strong>{permission.name}</strong><small>Guard: {permission.guard_name}</small></span><div className="row-actions"><button className="icon-button" onClick={() => onRename(permission)}><Pencil size={15}/></button><button className="icon-button danger" onClick={() => onRemove(permission)}><Trash2 size={15}/></button></div></div>)}</div> : <EmptyState/>}</section>;
}

function AssignAccessModal({ user, roles, permissions, onClose, onSaved }) {
  const [roleIds, setRoleIds] = useState([]); const [permissionIds, setPermissionIds] = useState([]); const [saving, setSaving] = useState(false); const [error, setError] = useState('');
  useEffect(() => { if (user) { setRoleIds(user.role_ids || []); setPermissionIds(user.permission_ids || []); setError(''); } }, [user]);
  if (!user) return null;
  const toggle=(list,setList,id)=>setList(list.includes(id)?list.filter((item)=>item!==id):[...list,id]);
  async function save(){setSaving(true);setError('');try{const response=await api.post('/access/assign',{user_id:user.id,role_ids:roleIds,permission_ids:permissionIds});onSaved(response.message)}catch(caught){setError(caught.message)}finally{setSaving(false)}}
  return <Modal open onClose={onClose} title={`Hak akses ${user.name}`} subtitle="Role memberi sekumpulan permission; permission langsung hanya untuk pengecualian." size="large"><div className="access-assign">{error&&<div className="alert alert-error">{error}</div>}<section><h3>Role pengguna</h3><div className="access-options">{roles.map((role)=><label key={role.id}><input type="checkbox" checked={roleIds.includes(role.id)} onChange={()=>toggle(roleIds,setRoleIds,role.id)}/><span><ShieldCheck size={16}/><strong>{role.name}</strong><small>{role.permission_ids.length} permission</small></span></label>)}</div></section><section><h3>Permission langsung</h3><p>Gunakan hanya bila akses tidak cocok dimasukkan ke role.</p><div className="access-options permissions">{permissions.map((permission)=><label key={permission.id}><input type="checkbox" checked={permissionIds.includes(permission.id)} onChange={()=>toggle(permissionIds,setPermissionIds,permission.id)}/><span><KeyRound size={15}/><strong>{permission.name}</strong></span></label>)}</div></section><div className="form-actions"><button className="button button-ghost" onClick={onClose}>Batal</button><button className="button button-primary" onClick={save} disabled={saving}><Save size={16}/>{saving?'Menyimpan…':'Simpan hak akses'}</button></div></div></Modal>;
}
