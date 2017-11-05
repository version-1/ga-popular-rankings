<?php
Class GoogleAnalyticsService{

    private $analytics;
    private $view_id;
    private $result;
    private $display_count;
    private $reports;

    function __construct($keyfile,$view_id,$display_count)
    {
        $client = new Google_Client();
        $client->setApplicationName("GoogleAnalyticsService");
        $client->setAuthConfig($keyfile);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $analytics = new Google_Service_AnalyticsReporting($client);

        $this->analytics = $analytics;
        $this->view_id = $view_id;
        $this->display_count = $display_count;
    }

    function report_request() {
        // Create the DateRange object.
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("90daysAgo");
        $dateRange->setEndDate("today");

        // Create the Metrics object.
        $sessions = new Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression("ga:pageviews");
        $sessions->setAlias("pv");

        // Create the Dimension object.
        $dimention = new Google_Service_AnalyticsReporting_Dimension();
        $dimention->setName("ga:pagePathLevel4");

        // Filter
        $filter = new Google_Service_AnalyticsReporting_DimensionFilter();
        $filter->setDimensionName("ga:pagePathLevel4");
        $filter->setNot(true);
        $filter->setOperator("IN_LIST");
        $filter->setExpressions( ["/"] );

        $filters = new Google_Service_AnalyticsReporting_DimensionFilterClause();
        $filters->setFilters(array($filter));

        // OrderBy
        $orderby = new Google_Service_AnalyticsReporting_OrderBy();
        $orderby->setFieldName("ga:pageviews");
        $orderby->setOrderType("VALUE");
        $orderby->setSortOrder("DESCENDING");

        // Create the ReportRequest object.
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($this->view_id);
        $request->setDateRanges($dateRange);
        $request->setMetrics(array($sessions));
        $request->setDimensions(array($dimention));
        $request->setDimensionFilterClauses(array($filters));
        $request->setOrderBys($orderby);

        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests( array($request) );

        $this->reports = $this->analytics->reports->batchGet( $body );
    }

    function fetch_result_as_array() {
        $result = [];
        for ( $reportIndex = 0; $reportIndex < count($this->reports); $reportIndex++ ) {
            $report = $this->reports[ $reportIndex ];
            $rows = $report->getData()->getRows();

            $display_count = $this->display_count > count($rows) ? count($rows) : $this->display_count;
            for ( $rowIndex = 0; $rowIndex < $display_count; $rowIndex++) {
                $row = $rows[ $rowIndex ];
                $dimensions = $row->getDimensions();
                $metrics = $row->getMetrics();

                $result[] = [
                    'pageview' => $metrics[0]->getValues()[0],
                    'url'      => $dimensions[0]
                ];
            }
        }
        return $result;
    }
}
