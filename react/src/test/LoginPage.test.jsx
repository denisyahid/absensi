import { describe, expect, it, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { BrowserRouter } from 'react-router-dom';
import { AuthContext } from '../auth';
import LoginPage from '../pages/LoginPage';

const { post } = vi.hoisted(() => ({ post: vi.fn() }));
vi.mock('../api', () => ({
  ApiError: class ApiError extends Error { constructor(message, status, errors) { super(message); this.status = status; this.errors = errors; } },
  api: { post },
}));

describe('halaman login', () => {
  it('mengirim kredensial lalu menyimpan hasil login', async () => {
    const login = vi.fn();
    post.mockResolvedValueOnce({ data: { token: 'token-1', user: { id: 1, name: 'Deni' } } });
    render(<BrowserRouter><AuthContext.Provider value={{ login }}><LoginPage /></AuthContext.Provider></BrowserRouter>);

    await userEvent.type(screen.getByLabelText('Alamat email'), 'deni@example.com');
    await userEvent.type(screen.getByLabelText('Password'), 'password');
    await userEvent.click(screen.getByRole('button', { name: /masuk sekarang/i }));

    expect(post).toHaveBeenCalledWith('/auth/login', { email: 'deni@example.com', password: 'password', remember: true });
    expect(login).toHaveBeenCalledWith('token-1', { id: 1, name: 'Deni' });
  });
});
