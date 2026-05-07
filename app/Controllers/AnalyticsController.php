<?php

class AnalyticsController extends Controller {
    private int $storeId;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireStore();
        $this->storeId = (int)$this->currentStore['id'];
    }

    public function index(): void {
        $svc  = new AnalyticsService($this->storeId);
        $data = $svc->getAnalyticsDashboard(30);
        $this->view('admin.analytics.index', compact('data'));
    }

    public function revenue(): void {
        $period = (int)$this->request->get('period', 30);
        $svc    = new AnalyticsService($this->storeId);
        $data   = $svc->revenueAnalytics($period);
        $this->view('admin.analytics.revenue', compact('data', 'period'));
    }

    public function products(): void {
        $period = (int)$this->request->get('period', 30);
        $svc    = new AnalyticsService($this->storeId);
        $data   = $svc->productAnalytics($period);
        $this->view('admin.analytics.products', compact('data', 'period'));
    }

    public function customers(): void {
        $svc  = new AnalyticsService($this->storeId);
        $data = $svc->customerAnalytics();
        $this->view('admin.analytics.customers', compact('data'));
    }

    public function traffic(): void {
        $period = (int)$this->request->get('period', 30);
        $svc    = new AnalyticsService($this->storeId);
        $data   = $svc->trafficAnalytics($period);
        $this->view('admin.analytics.traffic', compact('data', 'period'));
    }
}
