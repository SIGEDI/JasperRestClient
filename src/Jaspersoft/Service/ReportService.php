<?php

declare(strict_types=1);

namespace Jaspersoft\Service;

use Jaspersoft\Client\Client;
use Jaspersoft\Exception\RESTRequestException;
use Jaspersoft\Tool\RESTRequest;
use Jaspersoft\Tool\Util;

/**
 * Class ReportService.
 */
class ReportService
{
    protected RESTRequest $service;
    protected string $restUrl2;

    public function __construct(Client $client)
    {
        $this->service = $client->getService();
        $this->restUrl2 = $client->getURL();
    }

    /**
     * This function runs and retrieves the binary data of a report.
     *
     * @param string      $uri               URI for the report you wish to run
     * @param string      $format            The format you wish to receive the report in (default: pdf)
     * @param string|null $pages             Request a specific page, or range of pages. Separate multiple pages or ranges by commas.
     *                                       (e.g: "1,4-22,42,55-100")
     * @param string|null $attachmentsPrefix a URI to prefix all image attachment sources with
     *                                       (must include trailing slash if needed)
     * @param array|null  $inputControls     associative array of key => value for any input controls
     * @param bool        $interactive       Should reports using Highcharts be interactive?
     * @param bool        $onePagePerSheet   Produce paginated XLS or XLSX?
     * @param string|null $transformerKey    For use when running a report as a JasperPrint. Specifies print element transformers
     *
     * @return string Binary data of report
     *
     * @throws RESTRequestException
     */
    public function runReport(
        string $uri,
        string $format = 'pdf',
        ?string $pages = null,
        ?string $attachmentsPrefix = null,
        ?array $inputControls = null,
        bool $interactive = true,
        bool $onePagePerSheet = false,
        bool $freshData = true,
        bool $saveDataSnapshot = false,
        ?string $transformerKey = null
    ): string {
        $url = $this->restUrl2.'/reports'.$uri.'.'.$format;
        if (empty($inputControls)) {
            $url .= '?'.Util::query_suffix(compact('pages', 'attachmentsPrefix', 'interactive', 'onePagePerSheet', 'freshData', 'saveDataSnapshot', 'transformerKey'));
        } else {
            $url .= '?'.Util::query_suffix(array_merge(compact('pages', 'attachmentsPrefix', 'interactive', 'onePagePerSheet', 'freshData', 'saveDataSnapshot', 'transformerKey'), $inputControls));
        }

        return $this->service->prepAndSend($url, [200], 'GET', null, true);
    }
}
