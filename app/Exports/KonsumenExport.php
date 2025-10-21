<?php

namespace App\Exports;

use App\Models\Konsumen;
use App\Models\UserRole;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KonsumenExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize {
    protected $filters;
    protected $user;
    protected $userRole;

    public function __construct($filters, $user, $userRole) {
        $this->filters = $filters;
        $this->user = $user;
        $this->userRole = $userRole;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection() {
        $search = $this->filters['search'] ?? null;
        $dateStart = $this->filters['dateStart'] ?? null;
        $dateEnd = $this->filters['dateEnd'] ?? null;
        $created_id = $this->filters['created_id'] ?? null;
        $prospek_id = $this->filters['prospek_id'] ?? null;
        $status = $this->filters['status'] ?? null;

        $query = Konsumen::with(['projek', 'prospek', 'createdBy', 'latestTransaksi'])
            ->where(function ($query) use ($search, $created_id) {
                if ($created_id) {
                    $query->where('created_id', $created_id);
                    $query->orWhere('added_by', $created_id);
                } else {
                    $query->where('created_id', $this->user->id);
                    $query->orWhere('added_by', $this->user->id);
                }

                if ($this->userRole->role->name === 'Admin' && !$created_id) {
                    // Get All Sales under Admin
                    $query->orWhere('status_delete', 'pending');
                }

                if ($search) {
                    $query
                        ->where('name', 'like', "%$search%")
                        ->orWhere('address', 'like', "%$search%")
                        ->orWhere('ktp_number', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                }
            })
            ->when($dateStart && $dateEnd, function ($query) use ($dateStart, $dateEnd) {
                $query->whereBetween('created_at', [$dateStart, $dateEnd]);
            })
            ->when($prospek_id, function ($query) use ($prospek_id) {
                $query->where('prospek_id', $prospek_id);
            })
            ->when($status, function ($query) use ($status) {
                $query->whereHas('latestTransaksi', function ($q) use ($status) {
                    $q->where('status', $status);
                });
            })
            ->orderBy('id', 'desc');

        return $query->get();
    }

    /**
     * @return array
     */
    public function headings(): array {
        return [
            'ID',
            'Nama',
            'Email',
            'Telepon',
            'No. KTP',
            'Alamat',
            'Projek',
            'Prospek',
            'Kesiapan Dana',
            'Pengalaman',
            'Deskripsi',
            'Status Transaksi',
            'Dibuat Oleh',
            'Tanggal Dibuat',
            'Tanggal Diupdate'
        ];
    }

    /**
     * @var Konsumen $konsumen
     */
    public function map($konsumen): array {
        return [
            $konsumen->id,
            $konsumen->name,
            $konsumen->email ?? '-',
            $konsumen->phone,
            $konsumen->ktp_number ?? '-',
            $konsumen->address,
            $konsumen->projek->name ?? '-',
            $konsumen->prospek->name ?? '-',
            'Rp ' . number_format($konsumen->kesiapan_dana ?? 0, 0, ',', '.'),
            $konsumen->pengalaman ?? '-',
            $konsumen->description ?? '-',
            $konsumen->latestTransaksi->status ?? 'Belum Ada Transaksi',
            $konsumen->createdBy->name ?? '-',
            $konsumen->created_at->format('d-m-Y H:i:s'),
            $konsumen->updated_at->format('d-m-Y H:i:s')
        ];
    }

    /**
     * @return array
     */
    public function styles(Worksheet $sheet) {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}
