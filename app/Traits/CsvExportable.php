<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

trait CsvExportable
{

    /**
     * Datetime carbon format
     *
     * @var string
     */
    private $datetime_format = 'd/m/Y H:i';

    /**
     * Date carbon format
     *
     * @var string
     */
    private $date_format = 'd/m/Y';

    /**
     * Main export method
     *
     * @param array $request
     * @param string $separator
     * @return false|resource
     */
    public final static function export(array $request = [], $separator = ';')
    {
        $data = self::loadData($request);

        $metadata = self::loadMetadata();

        return self::buildCsv(
            $separator,
            $data->map(fn($item) => $item->toCsv($metadata))->toArray()
        );
    }

    /**
     * Export file and download
     *
     * @param array $request
     * @param $separator
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public final static function exportAndDownload(array $request = [], $separator = ';')
    {
        self::export($request, $separator);

        $headers = array(
            'Content-Type' => 'text/csv'
        );

        return Response::download(
            self::setFileName(),
            self::setFileName(),
            $headers
        )->deleteFileAfterSend(true);
    }

    /**
     * Abstract method to transform model into array
     *
     * @param array $metadata
     * @return array
     */
    abstract protected function toCsv(array $metadata = []) : array;

    /**
     * Abstract method to load data
     *
     * @param array $request
     * @return Collection
     */
    abstract protected static function loadData(array $request = []) : Collection;

    /**
     * Build Csv file
     *
     * @param $separator
     * @param array $data
     * @return false|resource
     */
    private static function buildCsv($separator, array $data = [])
    {
        $file = fopen(
            self::setFileName(),
            'w+'
        );

        $metadata = self::loadMetadata();

        $headers = self::setHeaders($metadata);

        if ($headers && count($headers) > 0)
        {
            fputcsv(
                $file,
                $headers,
                $separator
            );
        }

        foreach ($data as $fields)
        {
            fputcsv(
                $file,
                $fields,
                $separator
            );
        }

        fclose($file);

        return $file;
    }

    /**
     * Set Exported file name
     *
     * @return string
     */
    protected static function setFileName()
    {
        return 'export_csv_' . now()->format('d-m-Y-Hi') . '.csv';
    }

    /**
     * Set header labels
     *
     * @param array $metadata
     * @return array
     */
    protected static function setHeaders(array $metadata = []) : array
    {
        return [];
    }

    /**
     * Load meta data for building csv
     *
     * @return array
     */
    protected static function loadMetadata()
    {
        return [];
    }
}