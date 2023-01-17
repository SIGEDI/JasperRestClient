<?php

require_once 'BaseTest.php';

class ReportServiceTest extends BaseTest
{
    protected $jc;
    protected $sample_report;

    public function setUp()
    {
        parent::setUp();
        $this->rs = $this->jc->reportService();
        $this->ros = $this->jc->optionsService();
        $this->res = $this->jc->repositoryService();

        $this->sample_report = '/reports/samples/AllAccounts';
        $this->sample_report_size = 220000;	// pre-determined
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Checks whether the sample report when acquired as a PDF file has a plausible content length.
     */
    public function testRunReportGetsSomewhatProperFileSize()
    {
        $data = $this->rs->runReport($this->sample_report, 'pdf');
        $this->assertGreaterThan($this->sample_report_size, mb_strlen($data));
    }

    /**
     * Checks whether HTML representation of Flash Chart Report is adequate, determined by
     * the required SWF file URL presence in output.
     */
    public function testRunFlashChartReport()
    {
        $report = $this->rs->runReport('/reports/samples/FlashChartReport', 'html');
        $this->assertContains('fusion/charts/Bar2D.swf', $report);
    }

    /**
     * Checks whether running a report with custom options actually runs it so.
     */
    public function testRunCascadingInputReportWithCustomOptions()
    {
        $options = [
            'Country_multi_select' => ['USA', 'Canada'],
            'Cascading_state_multi_select' => ['CA', 'OR'],
            'Cascading_name_single_select' => ['Alcorn-Miller Transportation Holdings'],
        ];
        $report = $this->rs->runReport('/reports/samples/Cascading_multi_select_report', 'csv', null, null, $options);
        $this->assertContains('[Canada, USA]', $report);
        $this->assertContains('[CA, OR]', $report);
        $this->assertRegExp("/Customer\ parameter\:\,*Alcorn\-Miller\ Transportation\ Holdings/", $report);
    }

    /**
     * Checks updateReportOptions() functionality by creating new ReportOptions, running them and verifying the output.
     */
    public function testRunCascadingInputReportCreateOptions()
    {
        $options = [
            'Country_multi_select' => ['Mexico', 'USA'],
            'Cascading_state_multi_select' => ['Guerrero', 'CA', 'OR'],
            'Cascading_name_single_select' => ['Adina-Bohling Transportation Holdings'],
        ];
        $this->ros->updateReportOptions('/reports/samples/Cascading_multi_select_report', $options, 'USAAndMexicoReport', 'true');
        $report = $this->rs->runReport('/reports/samples/USAAndMexicoReport', 'csv');

        // Please note that this method works only when there are no whitespaces in the label.
        $this->ros->deleteReportOptions('/reports/samples/Cascading_multi_select_report', 'USAAndMexicoReport');

        $this->assertContains('[Mexico, USA]', $report);
        $this->assertContains('[Guerrero, CA, OR]', $report);
        $this->assertRegExp("/Customer\ parameter\:\,*Adina\-Bohling\ Transportation\ Holdings/", $report);
    }

    /**
     * Checks running a report with custom options when this report has input controls of various types.
     */
    public function testRunSalesByMonthReport()
    {
        $options = [
            'TextInput' => ['1234'],
            'CheckboxInput' => ['false'],
            'ListInput' => ['3'],
            'DateInput' => ['2012-09-08'],             // Y-M-D
            'QueryInput' => ['sally'],
        ];
        $report = $this->rs->runReport('/reports/samples/SalesByMonth', 'csv', null, null, $options);
        $this->assertRegExp("/Number\W*[0-9\s\,]*\W*List\ item\W*([0-9]+\s*)*\W*Date\W*(\"?\s*\w*\s*[0-9]{1,2}\,?\s*\w*\s*[0-9]{4}\"?)\W*Query\ item\W*sally/u", $report);

        $this->ros->updateReportOptions('/reports/samples/SalesByMonth', $options, 'SalesByMonthTestOptions', 'true');
        $savedOptions = $this->rs->getReportInputControls('/reports/samples/SalesByMonthTestOptions');

        try {
            $this->ros->deleteReportOptions('/reports/samples/SalesByMonth', 'SalesByMonthTestOptions');
        } catch (Exception $e) {
            $this->res->deleteResources('/reports/samples/SalesByMonthTestOptions');
        }

        $this->assertSame(1234, (int) $savedOptions[4]->value);
        $this->assertSame('false', $savedOptions[3]->value);
        $this->assertSame('false', $savedOptions[2]->options[0]['selected']);
        $this->assertSame('false', $savedOptions[2]->options[1]['selected']);
        $this->assertSame('true', $savedOptions[2]->options[2]['selected']);
        $this->assertSame('2012-09-08', $savedOptions[1]->value);
        $this->assertSame('true', $savedOptions[0]->options[6]['selected']);
    }
}
