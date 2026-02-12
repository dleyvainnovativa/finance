function ajaxRequest(params) {
    const token = localStorage.getItem('finance_auth_token');
    const url = new URL(params.url);

    url.searchParams.set('search', params.data.search || '');
    url.searchParams.set('page', (params.data.offset / params.data.limit) + 1);
    url.searchParams.set('limit', params.data.limit);

    const month = document.getElementById('month-filter')?.value;
    const year = document.getElementById('year-filter')?.value;
    if (month) url.searchParams.set('month', month);
    if (year) url.searchParams.set('year', year);

    if (params.data.filter) {
        url.searchParams.set('filters', JSON.stringify(params.data.filter));
    }

    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    })
    .then(res => res.json())
    .then(data => {
        params.success(data);
    })
    .catch(() => params.error());
}
window.ajaxRequest = ajaxRequest;

function responseHandler(res) {
    return {
        total: res.total,
        rows: res.data,
        footer: res.footer
    };
}
window.responseHandler = responseHandler;

function footerLabel() {
    return '<strong>Totales</strong>';
}
window.footerLabel = footerLabel;

function footerNullText() {
    return '';
}
window.footerNullText = footerNullText;

function footerSum() {
    const field = this.field;
    const footerData = $('#trial-balance-table').bootstrapTable('getFooterData');
    if (footerData && footerData.length > 0) {
        const value = footerData[0][field];
        if (value !== null && value !== undefined) {
            const number = parseFloat(value);
            return '<strong>' + number.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' }) + '</strong>';
        }
    }
    return '<strong>-</strong>';
}
window.footerSum = footerSum;

document.addEventListener('DOMContentLoaded', function () {
    const $table = $('#trial-balance-table');
    if ($table.length) {
                $table.bootstrapTable(tableOptions);
    }
    $('#month-filter, #year-filter').on('change', function () {
        $table.bootstrapTable('refresh', {
            pageNumber: 1
        });
    });
});
