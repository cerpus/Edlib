import { bb, bar, zoom } from 'billboard.js';

/**
 * Content usage as a chart
 */
window.usageChart = function (target, data, url) {
    const locale = document.documentElement.getAttribute('lang') ?? navigator.languages;
    let {resolution, dataFormat, tickFormat, tooltipFormat} = data.formats;

    const chart = bb.generate({
        bindto: target,
        title: {
            text: data.texts.title,
            padding: {
                bottom: 20,
            },
        },
        data: {
            type: bar(),
            labels: true,
            xFormat: dataFormat,
            json: data.values,
            keys: {
                x: 'point',
                value: data.groups,
            },
            names: data.texts.groupNames,
            hide: data.defaultHiddenGroups,
            empty: {
                label: {
                    text: data.texts.emptyData,
                },
            },
            groups: [
                data.defaultHiddenGroups,
            ],
            onclick: function (d,element) {
                const itemDate = new Date(Date.parse(d.x));
                let start, end;

                switch (resolution) {
                    case 'month':
                        start = Date.UTC(itemDate.getFullYear(), itemDate.getMonth());
                        end = Date.UTC(itemDate.getFullYear(), itemDate.getMonth() + 1, 1, 0, 0, 0, -1);
                        break;
                    case 'year':
                        start = Date.UTC(itemDate.getFullYear());
                        end = Date.UTC(itemDate.getFullYear() + 1, 0, 1, 0, 0, 0, -1);
                }
                if (start && end) {
                    getData([start, end]);
                    chart.zoom([start, end]);
                }
            }
        },
        zoom: {
            enabled: url && zoom(),
            type: 'drag',
            rescale: true,
            onzoomend: getData,
            resetButton: {
                onclick: getData,
                text: data.texts.resetButton,
            },
        },
        legend: {
            show: true,
        },
        padding: {
            right: 10,
        },
        tooltip: {
            // Default tooltip uses 'style' that causes CSP violation
            contents: function (data, defaultTitleFormat, defaultValueFormat) {
                let label = `<div class="text-bg-secondary p-1"><div class="border-bottom">${format(data[0]['x'], tooltipFormat)}</div>`;
                data.forEach(v => label += `<div>${v['name']}: ${v['value']}</div>`);
                return label + '</div>';
            },
        },
        axis: {
            x: {
                type: 'timeseries',
                localtime: true,
                tick: {
                    culling: true,
                    multiline: true,
                    format: x => format(x, tickFormat),
                },
            },
            y: {
                tick: {
                    culling: true,
                    format: y => y.toFixed(0),
                },
            },
        },
        bar: {
            padding: 2,
        },
        boost: {
            // Causes CSP violation if either enabled
            useWorker: false,
            useCssRule: false,
        },
    });

    function format(value, options) {
        return new Intl.DateTimeFormat(locale, options).format(value);
    }

    function getData(range) {
        const route = new URL(url);

        if (Array.isArray(range)) {
            // Zoom range selected
            route.searchParams.set('start', range[0].valueOf());
            route.searchParams.set('end', range[1].valueOf());
        }

        chart.config('data.empty.label.text', data.texts.loading, true);
        chart.unload({
            done: () => fetch(route, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error(`Failed getting chart data: ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(function (newData) {
                    const title = chart.$.title.select('text').text().split('\n')[0];
                    ({resolution, dataFormat, tickFormat, tooltipFormat} = newData.formats);

                    if (Array.isArray(range)) {
                        chart.$.title.select('text').text(title + '\n (' + format(range[0], tooltipFormat) + ' - ' + format(range[1], tooltipFormat) + ')');
                    } else {
                        chart.$.title.select('text').text(title);
                    }

                    chart.config('data.empty.label.text', data.texts.emptyData);
                    chart.load({json: newData.values});
                })
                .catch(function (error) {
                    console.error(error);
                    chart.config('data.empty.label.text', data.texts.loadingFailed, true);
                })
        });
    }
}
