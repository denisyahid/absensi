import { describe, expect, it, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import { Avatar, EmptyState, Pagination, StatusBadge } from '../components/Common';

describe('komponen umum', () => {
  it('menampilkan inisial ketika foto profil kosong', () => {
    render(<Avatar user={{ name: 'Deni Yahid' }} />);
    expect(screen.getByText('DY')).toBeInTheDocument();
  });

  it('menampilkan empty state yang dapat disesuaikan', () => {
    render(<EmptyState title="Data kosong" description="Belum ada catatan." />);
    expect(screen.getByRole('heading', { name: 'Data kosong' })).toBeInTheDocument();
    expect(screen.getByText('Belum ada catatan.')).toBeInTheDocument();
  });

  it('menjalankan perpindahan halaman', () => {
    const onPage = vi.fn();
    render(<Pagination meta={{ current_page: 2, last_page: 3, total: 25 }} onPage={onPage} />);
    screen.getByRole('button', { name: /sebelumnya/i }).click();
    screen.getByRole('button', { name: /berikutnya/i }).click();
    expect(onPage).toHaveBeenNthCalledWith(1, 1);
    expect(onPage).toHaveBeenNthCalledWith(2, 3);
  });

  it('menerapkan tone pada status', () => {
    render(<StatusBadge tone="success">Dikonfirmasi</StatusBadge>);
    expect(screen.getByText('Dikonfirmasi')).toHaveClass('status-success');
  });
});
