import { beforeEach, describe, expect, it, vi } from 'vitest';
import { ApiError, api, queryString } from '../api';

describe('API client', () => {
  beforeEach(() => {
    localStorage.clear();
    vi.stubGlobal('fetch', vi.fn());
  });

  it('mengirim route, JSON, dan Bearer token dengan benar', async () => {
    api.token.set('token-rahasia');
    fetch.mockResolvedValue(new Response(JSON.stringify({ success: true, data: { id: 1 } }), {
      status: 200,
      headers: { 'Content-Type': 'application/json' },
    }));

    const result = await api.post('/attendance', { action: 'clock_in' });
    const [url, options] = fetch.mock.calls[0];

    expect(new URL(url).searchParams.get('route')).toBe('attendance');
    expect(options.headers.get('Authorization')).toBe('Bearer token-rahasia');
    expect(options.headers.get('Content-Type')).toBe('application/json');
    expect(JSON.parse(options.body)).toEqual({ action: 'clock_in' });
    expect(result.data.id).toBe(1);
  });

  it('mengubah response gagal menjadi ApiError terstruktur', async () => {
    fetch.mockResolvedValue(new Response(JSON.stringify({ message: 'Data belum benar.', errors: { email: ['Email salah.'] } }), {
      status: 422,
      headers: { 'Content-Type': 'application/json' },
    }));

    await expect(api.post('/auth/login', {})).rejects.toMatchObject({
      name: 'ApiError', status: 422, message: 'Data belum benar.', errors: { email: ['Email salah.'] },
    });
  });

  it('membersihkan token pada response 401', async () => {
    api.token.set('kedaluwarsa');
    fetch.mockResolvedValue(new Response(JSON.stringify({ message: 'Sesi berakhir.' }), { status: 401 }));
    await expect(api.get('/dashboard')).rejects.toBeInstanceOf(ApiError);
    expect(api.token.get()).toBeNull();
  });

  it('membuat query hanya dari nilai yang terisi', () => {
    expect(queryString({ page: 2, search: '', month: 9, optional: null })).toBe('page=2&month=9');
  });
});
