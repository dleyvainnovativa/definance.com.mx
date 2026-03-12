import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/scss/app.scss', 
                'resources/js/app.js', 
                'resources/css/theme.css', 
                'resources/css/login.css',
                'resources/js/login.js',
                'resources/js/accounts.js',
                'resources/js/import.js',
                'resources/js/entry.js',
                'resources/js/journal.js',
                'resources/js/voucher.js',
                'resources/js/trial-balance.js',
                'resources/js/income_statement.js',
                'resources/js/cash_flow.js',
                'resources/js/managed_cash_flow.js',
                'resources/js/balance_sheet.js',
                'resources/js/budget.js',
                'resources/js/average.js',
                'resources/js/budget_monthly.js',
                'resources/js/dashboard.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
