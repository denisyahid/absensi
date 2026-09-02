const API_ENDPOINT = import.meta.env.VITE_API_URL || '/backend.php';
const TOKEN_KEY = 'absensi_api_token';

export class ApiError extends Error {
  constructor(message, status = 0, errors = null) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.errors = errors;
  }
}

function endpointUrl(route) {
  const url = new URL(API_ENDPOINT, window.location.origin);
  const requested = new URL(route, 'https://internal.local');
  url.searchParams.set('route', requested.pathname.replace(/^\//, ''));
  requested.searchParams.forEach((value, key) => url.searchParams.append(key, value));
  return url;
}

async function request(route, options = {}) {
  const token = localStorage.getItem(TOKEN_KEY);
  const headers = new Headers(options.headers || {});
  headers.set('Accept', 'application/json');
  if (token) headers.set('Authorization', `Bearer ${token}`);

  let body = options.body;
  if (body && !(body instanceof FormData) && typeof body !== 'string') {
    headers.set('Content-Type', 'application/json');
    body = JSON.stringify(body);
  }

  let response;
  try {
    response = await fetch(endpointUrl(route), { ...options, body, headers });
  } catch {
    throw new ApiError('Backend tidak dapat dihubungi. Pastikan server PHP sedang berjalan.');
  }

  const payload = await response.json().catch(() => null);
  if (!response.ok) {
    if (response.status === 401 && !route.startsWith('/auth/login')) {
      localStorage.removeItem(TOKEN_KEY);
      window.dispatchEvent(new CustomEvent('auth:expired'));
    }
    throw new ApiError(payload?.message || 'Permintaan gagal diproses.', response.status, payload?.errors);
  }
  return payload;
}

export const api = {
  request,
  get: (route) => request(route),
  post: (route, body) => request(route, { method: 'POST', body }),
  put: (route, body) => request(route, { method: 'PUT', body }),
  delete: (route) => request(route, { method: 'DELETE' }),
  token: {
    get: () => localStorage.getItem(TOKEN_KEY),
    set: (token) => localStorage.setItem(TOKEN_KEY, token),
    clear: () => localStorage.removeItem(TOKEN_KEY),
  },
  fileUrl(path) {
    if (!path) return '';
    const url = endpointUrl(`/files?path=${encodeURIComponent(path)}`);
    const token = localStorage.getItem(TOKEN_KEY);
    if (token) url.searchParams.set('token', token);
    return url.toString();
  },
};

export function queryString(values) {
  const params = new URLSearchParams();
  Object.entries(values).forEach(([key, value]) => {
    if (value !== '' && value !== null && value !== undefined) params.set(key, value);
  });
  return params.toString();
}
