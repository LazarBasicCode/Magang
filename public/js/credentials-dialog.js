(function (window, document) {
    'use strict';

    const esc = (value) => {
        const node = document.createElement('span');
        node.textContent = String(value ?? '');
        return node.innerHTML;
    };
    const toast = (type, message) => window.Toast?.show({ type, message });

    async function copy(text) {
        if (!navigator.clipboard?.writeText) return false;
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (error) {
            return false;
        }
    }

    function show(credentials, onClose) {
        if (!Array.isArray(credentials) || credentials.length === 0) return null;

        const overlay = document.createElement('div');
        overlay.className = 'cred-overlay';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.innerHTML = `
            <section class="cred-modal" aria-labelledby="credTitle">
                <header class="cred-head">
                    <span class="material-symbols-outlined" aria-hidden="true">key</span>
                    <h2 id="credTitle">Password Akun Baru</h2>
                </header>
                <p class="cred-note">Catat atau salin sekarang. Password ditampilkan sekali dan tidak bisa dilihat lagi setelah jendela ini ditutup.</p>
                <div class="cred-scroll">
                    <table>
                        <thead><tr><th>Nama</th><th>Username / NIM / NIDN</th><th>Password</th><th></th></tr></thead>
                        <tbody>${credentials.map((item, index) => `
                            <tr>
                                <td>${esc(item.nama)}</td>
                                <td class="cred-mono">${esc(item.id || '-')}</td>
                                <td><code>${esc(item.password)}</code></td>
                                <td><button type="button" class="cred-copy" data-index="${index}" aria-label="Salin password ${esc(item.nama)}" title="Salin password"><span class="material-symbols-outlined">content_copy</span></button></td>
                            </tr>`).join('')}
                        </tbody>
                    </table>
                </div>
                <footer class="cred-foot">
                    <button type="button" class="cred-secondary" data-action="csv"><span class="material-symbols-outlined">download</span> Unduh CSV</button>
                    <button type="button" class="cred-secondary" data-action="all"><span class="material-symbols-outlined">content_copy</span> Salin Semua</button>
                    <button type="button" class="cred-done" data-action="done">Selesai</button>
                </footer>
            </section>`;
        document.body.append(overlay);
        requestAnimationFrame(() => overlay.classList.add('is-open'));

        let closed = false;
        const close = () => {
            if (closed) return;
            closed = true;
            overlay.classList.remove('is-open');
            setTimeout(() => {
                overlay.remove();
                if (typeof onClose === 'function') onClose();
            }, 180);
        };

        overlay.addEventListener('click', async (event) => {
            event.stopPropagation();
            const copyButton = event.target.closest('.cred-copy');
            if (copyButton) {
                const ok = await copy(credentials[Number(copyButton.dataset.index)].password);
                toast(ok ? 'success' : 'error', ok ? 'Password disalin.' : 'Tidak bisa menyalin password.');
                return;
            }

            const action = event.target.closest('[data-action]')?.dataset.action;
            if (action === 'done') {
                close();
            } else if (action === 'all') {
                const text = ['Nama\tUsername/NIM/NIDN\tPassword', ...credentials.map((item) =>
                    `${item.nama}\t${item.id || ''}\t${item.password}`)].join('\n');
                const ok = await copy(text);
                toast(ok ? 'success' : 'error', ok ? `${credentials.length} akun disalin.` : 'Tidak bisa menyalin data akun.');
            } else if (action === 'csv') {
                const cell = (value) => {
                    let text = String(value ?? '');
                    if (/^[=+\-@\t\r]/.test(text)) text = "'" + text;
                    return '"' + text.replace(/"/g, '""') + '"';
                };
                const rows = [
                    ['Nama', 'Username/NIM/NIDN', 'Password'],
                    ...credentials.map((item) => [item.nama, item.id, item.password]),
                ];
                const blob = new Blob(['\ufeff' + rows.map((row) => row.map(cell).join(',')).join('\r\n')], {
                    type: 'text/csv;charset=utf-8',
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `akun-baru-${new Date().toISOString().slice(0, 10)}.csv`;
                document.body.append(link);
                link.click();
                setTimeout(() => {
                    URL.revokeObjectURL(link.href);
                    link.remove();
                }, 1000);
            }
        });

        return { close, element: overlay };
    }

    window.SIDACredentials = { show };
})(window, document);
