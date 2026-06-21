<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ActivityModel;
use App\Models\ExportModel;
use App\Services\ActivityLogger;

class ExportController extends Controller
{
    private ExportModel $exports;
    private ActivityLogger $activityLogger;

    public function __construct()
    {
        $this->exports = new ExportModel();
        $this->activityLogger = new ActivityLogger();
    }

    public function leads(): never
    {
        $user = $this->currentUser();
        $this->logExport('leads', $user);

        $this->streamCsv(self::filename('leads.csv'), [
            'ID',
            'Name',
            'Company',
            'Email',
            'Phone',
            'Source',
            'Status',
            'Priority',
            'Estimated Value',
            'Assigned User',
            'Converted Customer ID',
            'Converted At',
            'Created At',
        ], $this->exports->leads($user));
    }

    public function customers(): never
    {
        $user = $this->currentUser();
        $this->logExport('customers', $user);

        $this->streamCsv(self::filename('customers.csv'), [
            'ID',
            'Name',
            'Company',
            'Email',
            'Phone',
            'City',
            'Country',
            'Status',
            'Assigned User',
            'Created At',
        ], $this->exports->customers($user));
    }

    public function followUps(): never
    {
        $user = $this->currentUser();
        $this->logExport('follow-ups', $user);

        $this->streamCsv(self::filename('follow-ups.csv'), [
            'ID',
            'Title',
            'Related Type',
            'Related Name',
            'Assigned User',
            'Status',
            'Priority',
            'Due At',
            'Completed At',
            'Created At',
        ], $this->exports->followUps($user));
    }

    public function activities(): never
    {
        $user = $this->currentUser();
        $this->logExport('activities', $user);

        $this->streamCsv(self::filename('activities.csv'), [
            'ID',
            'User',
            'Entity Type',
            'Entity ID',
            'Action',
            'Description',
            'Created At',
        ], $this->exports->activities($user));
    }

    public function reportSummary(): never
    {
        $user = $this->currentUser();
        $this->logExport('report summary', $user);
        $summary = $this->exports->reportSummary($user);

        $this->streamCsv(self::filename('report-summary.csv'), [
            'Total Leads',
            'Converted Leads',
            'Conversion Rate',
            'Total Customers',
            'Open Follow-Ups',
            'Completed Follow-Ups',
            'Follow-Up Completion Rate',
            'Open Pipeline Value',
        ], [[
            $summary['total_leads'],
            $summary['converted_leads'],
            $summary['conversion_rate'],
            $summary['total_customers'],
            $summary['open_follow_ups'],
            $summary['completed_follow_ups'],
            $summary['follow_up_completion_rate'],
            $summary['open_pipeline_value'],
        ]]);
    }

    public static function filename(string $filename): string
    {
        $filename = strtolower(trim($filename));
        $filename = preg_replace('/[^a-z0-9._-]+/', '-', $filename) ?? '';
        $filename = trim($filename, '.-_');

        if ($filename === '') {
            $filename = 'export';
        }

        if (! str_ends_with($filename, '.csv')) {
            $filename .= '.csv';
        }

        return $filename;
    }

    /**
     * @param array<int, string> $headers
     * @param iterable<int, array<int|string, mixed>> $rows
     */
    protected function streamCsv(string $filename, array $headers, iterable $rows): never
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . self::filename($filename) . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        $output = fopen('php://output', 'w');

        if ($output !== false) {
            fputcsv($output, $headers, ',', '"', '', "\n");

            foreach ($rows as $row) {
                fputcsv($output, array_values($row), ',', '"', '', "\n");
            }

            fclose($output);
        }

        exit();
    }

    /**
     * @return array<string, mixed>
     */
    private function currentUser(): array
    {
        $user = Auth::user();

        if ($user === null) {
            $this->abort(403);
        }

        return $user;
    }

    /**
     * @param array<string, mixed> $user
     */
    private function logExport(string $exportType, array $user): void
    {
        $this->activityLogger->logSystemAction(
            ActivityModel::ACTION_EXPORTED,
            'User exported ' . $exportType . '.',
            [
                'export_type' => $exportType,
                'scope' => ExportModel::canAccessGlobal($user) ? 'global' : 'assigned',
            ],
            (int) $user['id']
        );
    }
}
