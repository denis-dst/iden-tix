<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class OperationalReportExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    private Collection $rows;
    private string $title;
    private array $customQuestionLabels;

    public function __construct(
        Collection $rows,
        string $title = 'Laporan Pendaftar',
    ) {
        $this->rows = $rows;
        $this->title = $title;

        // Extract all unique custom question labels configured across the rows
        $this->customQuestionLabels = $this->rows->flatMap(function (array $row) {
            return collect($row['custom_fields'] ?? [])->pluck('label');
        })->unique()->filter()->values()->all();
    }

    public function collection(): Collection
    {
        return $this->rows->values()->map(function (array $row, int $index) {
            $record = [
                $index + 1,
                $row['ticket_code'] ?? '-',
                $row['reference_no'] ?? '-',
                $row['name'] ?? '-',
                $this->formatGender($row['gender'] ?? null),
                $row['phone'] ?? '-',
                $row['email'] ?? '-',
            ];

            if (!empty($this->customQuestionLabels)) {
                $customMap = collect($row['custom_fields'] ?? [])->pluck('value', 'label');
                foreach ($this->customQuestionLabels as $label) {
                    $val = $customMap->get($label);
                    $record[] = ($val !== null && $val !== '') ? $val : '-';
                }
            } else {
                $record[] = $row['custom_question_label'] ?? '-';
                $record[] = $row['umroh_answer'] ?? '-';
            }

            $record[] = $row['event_name'] ?? '-';
            $record[] = $row['category_name'] ?? '-';
            $record[] = strtoupper((string) ($row['status'] ?? '-'));
            $record[] = !empty($row['first_checkin_at']) && method_exists($row['first_checkin_at'], 'format')
                ? $row['first_checkin_at']->format('d/m/Y H:i')
                : '-';

            return $record;
        });
    }

    public function headings(): array
    {
        $headers = [
            'Nomor',
            'Kode Tiket',
            'No Invoice',
            'Nama Peserta',
            'Gender',
            'No WA',
            'Email',
        ];

        if (!empty($this->customQuestionLabels)) {
            foreach ($this->customQuestionLabels as $label) {
                $headers[] = $label;
            }
        } else {
            $headers[] = 'Pertanyaan Custom';
            $headers[] = 'Jawaban Custom';
        }

        $headers[] = 'Event';
        $headers[] = 'Kategori Tiket';
        $headers[] = 'Status';
        $headers[] = 'Waktu Checkin';

        return $headers;
    }

    public function title(): string
    {
        return $this->title;
    }

    private function formatGender(?string $gender): string
    {
        if (!$gender) {
            return '-';
        }

        $g = strtolower(trim($gender));
        return match ($g) {
            'ikhwan' => 'Ikhwan',
            'akhwat' => 'Akhwat',
            'laki-laki', 'male' => 'Laki-laki',
            'perempuan', 'female' => 'Perempuan',
            default => ucfirst($gender),
        };
    }
}
