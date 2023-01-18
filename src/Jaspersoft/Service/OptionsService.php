<?php

namespace Jaspersoft\Service;

use Jaspersoft\Client\Client;
use Jaspersoft\Dto\Options\ReportOptions;
use Jaspersoft\Tool\Util;

/**
 * Class OptionsService.
 */
class OptionsService
{
    protected $service;
    protected $restUrl2;

    public function __construct(Client &$client)
    {
        $this->service = $client->getService();
        $this->restUrl2 = $client->getURL();
    }

    /**
     * Get report options.
     *
     * @param string $uri
     */
    public function getReportOptions($uri): array
    {
        $url = $this->restUrl2.'/reports'.$uri.'/options';
        $data = $this->service->prepAndSend($url, [200], 'GET', null, true, 'application/json', 'application/json');

        return ReportOptions::createFromJSON($data);
    }

    /**
     * Update or Create new Report Options.
     *
     * The argument $controlOptions must be an array in the following form:
     *
     * array('key' => array('value1', 'value2'), 'key2' => array('value1-2', 'value2-2'))
     *
     * Note that even when there is only one value, it must be encapsulated within an array.
     */
    public function updateReportOptions(string $uri, array $controlOptions, string $label, bool $overwrite): ReportOptions
    {
        $url = $this->restUrl2.'/reports'.$uri.'/options';
        $url .= '?'.Util::query_suffix(['label' => utf8_encode($label), 'overwrite' => $overwrite]);
        $body = json_encode($controlOptions);
        $data = $this->service->prepAndSend($url, [200], 'POST', $body, true);
        $data_array = json_decode($data, true);

        return new ReportOptions($data_array['uri'], $data_array['id'], $data_array['label']);
    }

    /**
     * Remove a pre-existing report options. Provide the URI and Label of the report options you wish to remove.
     * this function is limited in its ability to accept labels with whitespace. If you must delete a report option with whitespace
     * in the label name, use the deleteResources function instead. Using the URL to the report option.
     */
    public function deleteReportOptions(string $uri, string $optionsLabel)
    {
        $url = $this->restUrl2.'/reports'.$uri.'/options/'.$optionsLabel;
        $this->service->prepAndSend($url, [200], 'DELETE');
    }
}
