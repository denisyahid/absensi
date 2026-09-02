import { ChevronLeft, ChevronRight, Inbox, LoaderCircle, X } from 'lucide-react';
import { api } from '../api';

export function LoadingScreen({ label = 'Memuat data…', compact = false }) {
  return (
    <div className={compact ? 'loading-inline' : 'loading-screen'}>
      <LoaderCircle size={compact ? 18 : 28} className="spin" />
      <span>{label}</span>
    </div>
  );
}

export function EmptyState({ title = 'Belum ada data', description = 'Data akan muncul di sini.', action }) {
  return (
    <div className="empty-state">
      <span className="empty-icon"><Inbox size={26} /></span>
      <h3>{title}</h3>
      <p>{description}</p>
      {action}
    </div>
  );
}

export function Modal({ open, onClose, title, subtitle, children, size = 'medium' }) {
  if (!open) return null;
  return (
    <div className="modal-backdrop" role="presentation" onMouseDown={(event) => event.target === event.currentTarget && onClose()}>
      <section className={`modal modal-${size}`} role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <header className="modal-header">
          <div>
            <h2 id="modal-title">{title}</h2>
            {subtitle && <p>{subtitle}</p>}
          </div>
          <button type="button" className="icon-button" onClick={onClose} aria-label="Tutup"><X size={20} /></button>
        </header>
        <div className="modal-content">{children}</div>
      </section>
    </div>
  );
}

export function Avatar({ user, size = 'medium', className = '' }) {
  const photo = user?.foto || user?.user_foto;
  const name = user?.name || user?.user_name || '?';
  if (photo) {
    return <img className={`avatar avatar-${size} ${className}`} src={api.fileUrl(photo)} alt={name} />;
  }
  const initials = name.split(/\s+/).slice(0, 2).map((word) => word[0]).join('').toUpperCase();
  return <span className={`avatar avatar-${size} avatar-fallback ${className}`}>{initials}</span>;
}

export function Pagination({ meta, onPage }) {
  if (!meta || meta.last_page <= 1) return null;
  return (
    <div className="pagination">
      <span>Halaman {meta.current_page} dari {meta.last_page} · {meta.total} data</span>
      <div>
        <button type="button" className="button button-ghost button-small" disabled={meta.current_page <= 1} onClick={() => onPage(meta.current_page - 1)}>
          <ChevronLeft size={16} /> Sebelumnya
        </button>
        <button type="button" className="button button-ghost button-small" disabled={meta.current_page >= meta.last_page} onClick={() => onPage(meta.current_page + 1)}>
          Berikutnya <ChevronRight size={16} />
        </button>
      </div>
    </div>
  );
}

export function FieldError({ errors, name }) {
  const message = errors?.[name]?.[0];
  return message ? <span className="field-error">{message}</span> : null;
}

export function PageHeading({ eyebrow, title, description, actions }) {
  return (
    <div className="page-heading">
      <div>
        {eyebrow && <span className="eyebrow">{eyebrow}</span>}
        <h1>{title}</h1>
        {description && <p>{description}</p>}
      </div>
      {actions && <div className="heading-actions">{actions}</div>}
    </div>
  );
}

export function formatDate(value, options = {}) {
  if (!value) return '—';
  const normalized = /^\d{4}-\d{2}-\d{2}$/.test(value) ? `${value}T00:00:00` : value.replace(' ', 'T');
  const date = new Date(normalized);
  if (Number.isNaN(date.getTime())) return value;
  return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric', ...options }).format(date);
}

export function formatTime(value) {
  if (!value) return '—';
  const date = new Date(value.replace(' ', 'T'));
  if (Number.isNaN(date.getTime())) return value.slice(11, 16) || value;
  return new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', hourCycle: 'h23' }).format(date);
}

export function todayInput() {
  const now = new Date();
  const offset = now.getTimezoneOffset();
  return new Date(now.getTime() - offset * 60000).toISOString().slice(0, 10);
}

export function StatusBadge({ children, tone = 'neutral' }) {
  return <span className={`status-badge status-${tone}`}>{children}</span>;
}
