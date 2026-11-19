import { FlashMessage } from '@/components/alert-message';
import { useSharedProps } from '@/hooks/use-shared-props';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
type Ruas = {
    id: number;
    nama: string;
    lokasi: string;
    panjang: string;
    lebar: string;
    kondisi: string;
};
type Fields = Omit<Ruas, 'id'>;
const empty: Fields = {
    nama: '',
    lokasi: '',
    panjang: '',
    lebar: '',
    kondisi: 'Baik',
};
export default function Index({
    rows,
    q,
}: {
    rows: {
        data: Ruas[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    q: string;
}) {
    const { errors } = usePage().props;
    const { isAdmin } = useSharedProps();
    const [editing, setEditing] = useState<number | null>(null);
    const [search, setSearch] = useState(q);
    const form = useForm<Fields>(empty);
    function save(e: FormEvent) {
        e.preventDefault();
        const options = {
            onSuccess: () => {
                form.reset();
                setEditing(null);
            },
        };
        if (editing) form.put('/ruas-jalan/' + editing, options);
        else form.post('/ruas-jalan', options);
    }
    return (
        <AppLayout breadcrumbs={[{ title: 'Ruas Jalan', href: '/ruas-jalan' }]}>
            <Head title="Ruas Jalan" />
            <main className="space-y-6 p-6">
                <FlashMessage />
                {errors.hapus && (
                    <p role="alert" className="text-red-600">
                        {errors.hapus}
                    </p>
                )}
                <h1 className="text-2xl font-bold">Ruas Jalan Citeureup</h1>
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        router.get(
                            '/ruas-jalan',
                            { q: search },
                            { preserveState: true },
                        );
                    }}
                    className="flex gap-2"
                >
                    <input
                        aria-label="Cari ruas"
                        placeholder="Cari nama ruas…"
                        className="rounded border p-2"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                    />
                    <button className="rounded border px-4">Cari</button>
                </form>
                {isAdmin && (
                    <form
                        onSubmit={save}
                        className="grid gap-3 rounded-xl border p-4 md:grid-cols-2"
                    >
                        <h2 className="font-semibold md:col-span-2">
                            {editing ? 'Edit' : 'Tambah'} ruas jalan
                        </h2>
                        {(
                            [
                                'nama',
                                'lokasi',
                                'panjang',
                                'lebar',
                                'kondisi',
                            ] as const
                        ).map((key) => (
                            <label className="grid gap-1" key={key}>
                                {key === 'panjang'
                                    ? 'Panjang (meter)'
                                    : key === 'lebar'
                                      ? 'Lebar (meter)'
                                      : key}
                                {key === 'kondisi' ? (
                                    <select
                                        className="rounded border bg-background p-2"
                                        value={form.data[key]}
                                        onChange={(e) =>
                                            form.setData(key, e.target.value)
                                        }
                                    >
                                        {[
                                            'Baik',
                                            'Rusak Ringan',
                                            'Rusak Berat',
                                        ].map((x) => (
                                            <option key={x}>{x}</option>
                                        ))}
                                    </select>
                                ) : (
                                    <input
                                        className="rounded border p-2"
                                        type={
                                            key === 'panjang' || key === 'lebar'
                                                ? 'number'
                                                : 'text'
                                        }
                                        step="0.01"
                                        value={form.data[key]}
                                        onChange={(e) =>
                                            form.setData(key, e.target.value)
                                        }
                                    />
                                )}
                                <span className="text-sm text-red-600">
                                    {form.errors[key]}
                                </span>
                            </label>
                        ))}
                        <div className="flex gap-3">
                            <button
                                disabled={form.processing}
                                className="rounded bg-primary px-4 py-2 text-primary-foreground"
                            >
                                Simpan
                            </button>
                            <button
                                type="button"
                                onClick={() => {
                                    setEditing(null);
                                    form.reset();
                                    form.clearErrors();
                                }}
                            >
                                Batal / kosongkan
                            </button>
                        </div>
                    </form>
                )}
                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full text-left">
                        <thead>
                            <tr>
                                {[
                                    'Nama',
                                    'Lokasi',
                                    'Panjang (m)',
                                    'Lebar (m)',
                                    'Kondisi',
                                    'Aksi',
                                ].map((x) => (
                                    <th className="p-3" key={x}>
                                        {x}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {rows.data.map((row) => (
                                <tr className="border-t" key={row.id}>
                                    <td className="p-3">{row.nama}</td>
                                    <td>{row.lokasi}</td>
                                    <td>{row.panjang}</td>
                                    <td>{row.lebar}</td>
                                    <td>{row.kondisi}</td>
                                    <td>
                                        {isAdmin && (
                                            <div className="flex gap-3 p-2">
                                                <button
                                                    onClick={() => {
                                                        setEditing(row.id);
                                                        form.setData({
                                                            nama: row.nama,
                                                            lokasi: row.lokasi,
                                                            panjang: String(
                                                                row.panjang,
                                                            ),
                                                            lebar: String(
                                                                row.lebar,
                                                            ),
                                                            kondisi:
                                                                row.kondisi,
                                                        });
                                                        form.clearErrors();
                                                    }}
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    className="text-red-600"
                                                    onClick={() => {
                                                        if (
                                                            confirm(
                                                                'Hapus ruas ini?',
                                                            )
                                                        )
                                                            router.delete(
                                                                '/ruas-jalan/' +
                                                                    row.id,
                                                            );
                                                    }}
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
                    {!rows.data.length && (
                        <p className="p-4">Belum ada ruas yang cocok.</p>
                    )}
                </div>
                <nav className="flex flex-wrap gap-2">
                    {rows.links.map((l, i) =>
                        l.url ? (
                            <Link
                                className={
                                    l.active
                                        ? 'rounded bg-primary p-2 text-primary-foreground'
                                        : 'rounded border p-2'
                                }
                                key={i}
                                href={l.url}
                            >
                                {l.label
                                    .replace(/&laquo;/g, '«')
                                    .replace(/&raquo;/g, '»')}
                            </Link>
                        ) : (
                            <span key={i} className="p-2 text-muted-foreground">
                                {l.label
                                    .replace(/&laquo;/g, '«')
                                    .replace(/&raquo;/g, '»')}
                            </span>
                        ),
                    )}
                </nav>
            </main>
        </AppLayout>
    );
}
