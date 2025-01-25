<?php

namespace Tests\Functional\Finances;

use Tests\Functional\Base\BaseTestCase;

class StatsTest extends BaseTestCase {

    protected $TEST_CATEGORY_ID = 1;
    protected $TEST_BUDGET_ENTRY_REGULAR = 1;
    protected $TEST_BUDGET_ENTRY_REST = 2;

    protected function setUp(): void {
        $this->login("admin", "admin");
    }

    protected function tearDown(): void {
        $this->logout();
    }

    public function testOverview() {
        $response = $this->request('GET', '/finances/stats/');

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="financeSummaryChart"', $body);
        $this->assertStringContainsString('<table id="stats_table"', $body);
    }

    public function testOverviewYear() {
        $response = $this->request('GET', '/finances/stats/' . date('Y') . '/');

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="financeSummaryChart"', $body);
        $this->assertStringContainsString('<table id="stats_year_table"', $body);
    }

    public function testMonthExpensesCategories() {
        $response = $this->request('GET', '/finances/stats/' . date('Y') . '/' . date('m') . '/0/');

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="financeDetailChart"', $body);
        $this->assertStringContainsString('<table id="stats_month_table"', $body);
    }

    public function testMonthIncomesCategories() {
        $response = $this->request('GET', '/finances/stats/' . date('Y') . '/' . date('m') . '/1/');

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="financeDetailChart"', $body);
        $this->assertStringContainsString('<table id="stats_month_table"', $body);
    }

    public function testMonthExpensesCategoryOverview() {
        $response = $this->request('GET', '/finances/stats/' . date('Y') . '/' . date('m') . '/0/' . $this->TEST_CATEGORY_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="financeDetailChart"', $body);
        $this->assertStringContainsString('<table id="stats_cat_table"', $body);
    }

    public function testMonthIncomesCategoryOverview() {
        $response = $this->request('GET', '/finances/stats/' . date('Y') . '/' . date('m') . '/1/' . $this->TEST_CATEGORY_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="financeDetailChart"', $body);
        $this->assertStringContainsString('<table id="stats_cat_table"', $body);
    }

    public function testYearExpensesCategories() {
        $response = $this->request('GET', '/finances/stats/' . date('Y') . '/categories/0');

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="financeDetailChart"', $body);
        $this->assertStringContainsString('<table id="stats_month_table"', $body);
    }

    public function testYearIncomesCategories() {
        $response = $this->request('GET', '/finances/stats/' . date('Y') . '/categories/1');

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="financeDetailChart"', $body);
        $this->assertStringContainsString('<table id="stats_month_table"', $body);
    }

    public function testYearExpensesCategoryOverview() {
        $response = $this->request('GET', '/finances/stats/' . date('Y') . '/categories/0/' . $this->TEST_CATEGORY_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="financeDetailChart"', $body);
        $this->assertStringContainsString('<table id="stats_cat_table"', $body);
    }

    public function testYearIncomesCategoryOverview() {
        $response = $this->request('GET', '/finances/stats/' . date('Y') . '/categories/1/' . $this->TEST_CATEGORY_ID);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="financeDetailChart"', $body);
        $this->assertStringContainsString('<table id="stats_cat_table"', $body);
    }

    public function testBudget1Overview() {
        $response = $this->request('GET', '/finances/stats/budget/' . $this->TEST_BUDGET_ENTRY_REGULAR);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="financeDetailChart"', $body);
        $this->assertStringContainsString('<table id="stats_budget_table"', $body);
    }

    public function testBudget2Overview() {
        $response = $this->request('GET', '/finances/stats/budget/' . $this->TEST_BUDGET_ENTRY_REST);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('<canvas id="financeDetailChart"', $body);
        $this->assertStringContainsString('<table id="stats_budget_table"', $body);
    }
}
