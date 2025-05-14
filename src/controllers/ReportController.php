<?php

namespace App\Controllers;

use App\Models\CarModel;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportController
{
    private $twig;
    private $carModel;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => true,
        ]);

        $this->twig->addGlobal('session', $_SESSION);
        $this->carModel = new CarModel();
    }

    private function checkAuth(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
    }

    private function getReportData(): array
    {
        $this->checkAuth();

        date_default_timezone_set('Europe/Moscow');

        return [
            'cars' => $this->carModel->getAllCars(),
            'isAdmin' => $_SESSION['user']['role'] === 'admin',
            'username' => $_SESSION['user']['username'],
            'currentDate' => date('d.m.Y H:i:s')
        ];
    }

    public function generatePdfReport(): void
    {
        $data = $this->getReportData();

        $html = $this->generatePdfHtml($data);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('cars_report.pdf', ['Attachment' => true]);
        exit;
    }

    private function generatePdfHtml(array $data): string
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Отчет по автомобилям</title>
            <style>
                body { font-family: DejaVu Sans, sans-serif; }
                .report-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .report-table th { background-color: #f2f2f2; }
                .report-table th, .report-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .report-header { text-align: center; margin-bottom: 20px; }
                .page-break { page-break-after: always; }
                .date-generated { text-align: right; font-size: 0.8em; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="report-header">
                <h1>Отчет по автомобилям</h1>
                <p>Сформирован: ' . $data['currentDate'] . '</p>
            </div>
            
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Марка</th>
                        <th>Модель</th>
                        <th>Год</th>
                        <th>Цвет</th>
                        ' . ($data['isAdmin'] ? '<th>Владелец</th>' : '') . '
                    </tr>
                </thead>
                <tbody>';

        foreach ($data['cars'] as $car) {
            $html .= '
                <tr>
                    <td>' . htmlspecialchars($car['brand']) . '</td>
                    <td>' . htmlspecialchars($car['model']) . '</td>
                    <td>' . htmlspecialchars($car['year']) . '</td>
                    <td>' . htmlspecialchars($car['color']) . '</td>';

            if ($data['isAdmin']) {
                $ownerName = $car['owner_name'] ?? 'Не указан';
                $html .= '<td>' . htmlspecialchars($ownerName) . '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '
                </tbody>
            </table>
            
            <div class="date-generated">
                <p>Пользователь: ' . htmlspecialchars($data['username']) . '</p>
            </div>
        </body>
        </html>';

        return $html;
    }

    public function generateExcelReport(): void
    {
        $data = $this->getReportData();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Автомобили');

        $headers = ['Марка', 'Модель', 'Год', 'Цвет'];
        if ($data['isAdmin']) {
            $headers[] = 'Владелец';
        }

        // Добавление заголовков
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }

        $row = 2;
        foreach ($data['cars'] as $car) {
            $sheet->setCellValue('A' . $row, $car['brand']);
            $sheet->setCellValue('B' . $row, $car['model']);
            $sheet->setCellValue('C' . $row, $car['year']);
            $sheet->setCellValue('D' . $row, $car['color']);

            if ($data['isAdmin']) {
                $sheet->setCellValue('E' . $row, $car['owner_name'] ?? 'Не указан');
            }
            $row++;
        }

        $infoRow = $row + 2;
        $sheet->setCellValue('A' . $infoRow, 'Отчет сформирован:');
        $sheet->setCellValue('B' . $infoRow, $data['currentDate']);
        $sheet->setCellValue('A' . ($infoRow + 1), 'Пользователь:');
        $sheet->setCellValue('B' . ($infoRow + 1), $data['username']);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="cars_report.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function generateCsvReport(): void
    {
        $data = $this->getReportData();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="cars_report.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Заголовки
        $headers = ['Марка', 'Модель', 'Год', 'Цвет'];
        if ($data['isAdmin']) {
            $headers[] = 'Владелец';
        }
        fputcsv($output, $headers);

        foreach ($data['cars'] as $car) {
            $row = [
                $car['brand'],
                $car['model'],
                $car['year'],
                $car['color']
            ];

            if ($data['isAdmin']) {
                $row[] = $car['owner_name'] ?? 'Не указан';
            }

            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
