<?php

namespace App\Service\Invoice;


class InvoiceService
{
    public function getTodayInvoices($allInvoices): array
    {
        $todayInvoices = [];
        foreach ($allInvoices as $invoice) {
            if ($invoice->getPurchaseDate()->format('Y-m-d') == date('Y-m-d')) {
                array_push($todayInvoices, $invoice);
            }
        }
        return $todayInvoices;
    }

    public function getLastWeekInvoices($allInvoices): array
    {
        $lastWeekInvoices = [];
        foreach ($allInvoices as $invoice) {
            if ($invoice->getPurchaseDate()->format('Y-m-d') > date('Y-m-d', strtotime("-1 week"))) {
                array_push($lastWeekInvoices, $invoice);
            }
        }
        return $lastWeekInvoices;
    }

    public function getLastMonthInvoices($allInvoices): array
    {
        $lastMonthInvoices = [];
        foreach ($allInvoices as $invoice) {
            if ($invoice->getPurchaseDate()->format('Y-m-d') > date('Y-m-d', strtotime("-1 month"))) {
                array_push($lastMonthInvoices, $invoice);
            }
        }
        return $lastMonthInvoices;
    }

    public function getProfit($invoices): float
    {
        $profit = 0;
        foreach ($invoices as $invoice) {
            $profit += $invoice->getPrice();
        }
        return $profit;
    }
}