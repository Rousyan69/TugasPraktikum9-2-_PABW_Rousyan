import { useSharedProps } from '@/hooks/use-shared-props';
import AppLayout from '@/layouts/app-layout';
import { http, HttpError, type ValidationErrors } from '@/lib/http';
import { Head } from '@inertiajs/react';
import { useEffect, useRef, useState, type FormEvent } from 'react';
type Row = {
    id: number;
    ruas_jalan_id: number;
    judul: string;
    deskripsi: string;
    tanggal: string;
    status: string;
    ruas: { nama: string };
    pelapor: { name: string } | null;
};
const blank = {
    ruas_jalan_id: '',
    judul: '',
    deskripsi: '',
    tanggal: new Date().toLocaleDateString('en-CA'),
    status: 'Diajukan',
};
export default function Index({
    ruas,
}: {
    ruas: { id: number; nama: string }[];
}) {
    const { isAdmin } = useSharedProps();
    const [rows, setRows] = useState<Row[]>([]);
    const [data, setData] = useState(blank);
    const [id, setId] = useState<number | null>(null);
    const [errors, setErrors] = useState<ValidationErrors>({});
    const [message, setMessage] = useState('');
    const [busy, setBusy] = useState(false);
    const file = useRef<HTMLInputElement>(null);
    async function load() {
        setRows(await http<Row[]>('/laporan-jalan/data'));
    }
    function failure(e: unknown) {
        if (e instanceof HttpError) {
            setErrors(e.data?.errors ?? {});
            setMessage(
                e.status === 419
                    ? 'Sesi berakhir. Muat ulang halaman lalu login kembali.'
                    : e.message,
            );
        } else setMessage('Koneksi gagal. Silakan coba kembali.');
    }
    useEffect(() => {
        load().catch(failure);
    }, []);
    function reset() {
        setId(null);
        setData(blank);
        setErrors({});
        if (file.current) file.current.value = '';
    }
    async function save(e: FormEvent) {
        e.preventDefault();
        setBusy(true);
        setErrors({});
        setMessage('');
        const body = new FormData();
        Object.entries(data).forEach(([k, v]) => {
            if (k !== 'status' || isAdmin) body.append(k, v);
        });
        if (file.current?.files?.[0])
            body.append('foto', file.current.files[0]);
        if (id) body.append('_method', 'PUT');
        try {
            const result = await http<{ message: string }>(
                '/laporan-jalan' + (id ? '/' + id : ''),
                { method: 'POST', body },
            );
            reset();
            setMessage(result.message);
            await load();
        } catch (e) {
            failure(e);
        } finally {
            setBusy(false);
        }
    }
    async function remove(row: Row) {
        if (!confirm('Hapus laporan dan fotonya?')) return;
        setBusy(true);
        try {
            const r = await http<{ message: string }>(
                '/laporan-jalan/' + row.id,
                { method: 'DELETE' },
            );
            if (id === row.id) reset();
            setMessage(r.message);
            await load();
        } catch (e) {
            failure(e);
        } finally {
            setBusy(false);
        }
    }
    return (
        <AppLayout
            breadcrumbs={[{ title: 'Laporan Jalan', href: '/laporan-jalan' }]}
        >
            <Head title="Laporan Jalan" />
            <main className="space-y-5 p-6">
                <h1 className="text-2xl font-bold">Laporan Kerusakan Jalan</h1>
                <p className="text-muted-foreground">
                    {isAdmin
                        ? 'Kelola seluruh laporan warga.'
                        : 'Laporan milik Anda. Perubahan diperbolehkan selama status Diajukan.'}
                </p>
                {message && (
                    <p role="status" className="rounded border p-3">
                        {message}
                    </p>
                )}
                <form
                    onSubmit={save}
                    className="grid gap-3 rounded-xl border p-4 md:grid-cols-2"
                >
                    <h2 className="font-semibold md:col-span-2">
                        {id ? 'Edit' : 'Tambah'} laporan
                    </h2>
                    {(
                        [
                            'ruas_jalan_id',
                            'judul',
                            'deskripsi',
                            'tanggal',
                            'status',
                        ] as const
                    )
                        .filter((k) => k !== 'status' || isAdmin)
                        .map((k) => (
                            <label key={k} className="grid gap-1">
                                {k === 'ruas_jalan_id' ? 'Ruas jalan' : k}
                                {k === 'ruas_jalan_id' ? (
                                    <select
                                        className="rounded border bg-background p-2"
                                        value={data[k]}
                                        onChange={(e) =>
                                            setData({
                                                ...data,
                                                [k]: e.target.value,
                                            })
                                        }
                                    >
                                        <option value="">
                                            Pilih ruas jalan
                                        </option>
                                        {ruas.map((r) => (
                                            <option key={r.id} value={r.id}>
                                                {r.nama}
                                            </option>
                                        ))}
                                    </select>
                                ) : k === 'status' ? (
                                    <select
                                        className="rounded border bg-background p-2"
                                        value={data[k]}
                                        onChange={(e) =>
                                            setData({
                                                ...data,
                                                [k]: e.target.value,
                                            })
                                        }
                                    >
                                        {[
                                            'Diajukan',
                                            'Diproses',
                                            'Selesai',
                                        ].map((s) => (
                                            <option key={s}>{s}</option>
                                        ))}
                                    </select>
                                ) : k === 'deskripsi' ? (
                                    <textarea
                                        className="rounded border p-2"
                                        value={data[k]}
                                        onChange={(e) =>
                                            setData({
                                                ...data,
                                                [k]: e.target.value,
                                            })
                                        }
                                    />
                                ) : (
                                    <input
                                        className="rounded border p-2"
                                        type={k === 'tanggal' ? 'date' : 'text'}
                                        value={data[k]}
                                        onChange={(e) =>
                                            setData({
                                                ...data,
                                                [k]: e.target.value,
                                            })
                                        }
                                    />
                                )}
                                {errors[k]?.map((s) => (
                                    <span
                                        key={s}
                                        className="text-sm text-red-600"
                                    >
                                        {s}
                                    </span>
                                ))}
                            </label>
                        ))}
                    <label className="grid gap-1">
                        Foto JPG/PNG, maksimal 2 MB{' '}
                        {id ? '(kosongkan untuk mempertahankan foto)' : ''}
                        <input
                            ref={file}
                            type="file"
                            accept="image/jpeg,image/png"
                        />
                        {errors.foto?.map((s) => (
                            <span key={s} className="text-sm text-red-600">
                                {s}
                            </span>
                        ))}
                    </label>
                    <div className="flex gap-3">
                        <button
                            disabled={busy}
                            className="rounded bg-primary px-4 py-2 text-primary-foreground"
                        >
                            {busy ? 'Memproses…' : 'Simpan'}
                        </button>
                        <button type="button" onClick={reset}>
                            Batal / kosongkan
                        </button>
                    </div>
                </form>
                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full text-left">
                        <thead>
                            <tr>
                                {[
                                    'Laporan',
                                    'Ruas / Pelapor',
                                    'Tanggal',
                                    'Status',
                                    'Foto',
                                    'Aksi',
                                ].map((s) => (
                                    <th key={s} className="p-3">
                                        {s}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {rows.map((row) => (
                                <tr key={row.id} className="border-t">
                                    <td className="max-w-sm p-3">
                                        <strong>{row.judul}</strong>
                                        <p className="text-sm whitespace-pre-wrap">
                                            {row.deskripsi}
                                        </p>
                                    </td>
                                    <td>
                                        {row.ruas.nama}
                                        <p className="text-sm">
                                            {row.pelapor?.name ??
                                                'Akun dihapus'}
                                        </p>
                                    </td>
                                    <td>{row.tanggal}</td>
                                    <td>{row.status}</td>
                                    <td>
                                        <a
                                            className="underline"
                                            target="_blank"
                                            rel="noreferrer"
                                            href={
                                                '/laporan-jalan/' +
                                                row.id +
                                                '/foto'
                                            }
                                        >
                                            Buka foto
                                        </a>
                                    </td>
                                    <td>
                                        {(isAdmin ||
                                            row.status === 'Diajukan') && (
                                            <div className="flex gap-3 p-2">
                                                <button
                                                    disabled={busy}
                                                    onClick={() => {
                                                        setId(row.id);
                                                        setData({
                                                            ruas_jalan_id:
                                                                String(
                                                                    row.ruas_jalan_id,
                                                                ),
                                                            judul: row.judul,
                                                            deskripsi:
                                                                row.deskripsi,
                                                            tanggal:
                                                                row.tanggal,
                                                            status: row.status,
                                                        });
                                                        setErrors({});
                                                        if (file.current)
                                                            file.current.value =
                                                                '';
                                                        window.scrollTo({
                                                            top: 0,
                                                            behavior: 'smooth',
                                                        });
                                                    }}
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    disabled={busy}
                                                    className="text-red-600"
                                                    onClick={() => remove(row)}
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    {!rows.length && <p className="p-4">Belum ada laporan.</p>}
                </div>
            </main>
        </AppLayout>
    );
}
