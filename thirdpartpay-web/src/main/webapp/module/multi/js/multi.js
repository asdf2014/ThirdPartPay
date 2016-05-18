$(function () {

    $.ajax({
        url: "/customer/location",
        dataType: "json",
        success: function (data) {
            var colors = Highcharts.getOptions().colors,
                categories = [],
                browserData = [],
                versionsData = [],
                i,
                j,
                dataLen = data.length,
                drillDataLen,
                brightness;

            for (i = 0; i < dataLen; i += 1) {
                categories.push(data[i].drilldown.name)
            }

            // Build the data arrays
            for (i = 0; i < dataLen; i += 1) {

                // add browser data
                browserData.push({
                    name: categories[i],
                    y: data[i].y,
                    // color: data[i].color
                    color: colors[i]
                });

                // add version data
                drillDataLen = data[i].drilldown.data.length;
                for (j = 0; j < drillDataLen; j += 1) {
                    brightness = 0.2 - (j / drillDataLen) / 5;
                    versionsData.push({
                        name: data[i].drilldown.categories[j],
                        y: data[i].drilldown.data[j],
                        // color: Highcharts.Color(data[i].color).brighten(brightness).get()
                        color: Highcharts.Color(colors[i]).brighten(brightness).get()
                    });
                }
            }

            // Create the chart
            $('#container').highcharts({
                chart: {
                    type: 'pie'
                },
                title: {
                    text: 'Revenue Support Chart for Shanxi'
                },
                subtitle: {
                    text: 'Source: <a href="http://yuzhouwan.com/">yuzhouwan.com</a>'
                },
                yAxis: {
                    title: {
                        text: 'Revenue Support System'
                    }
                },
                plotOptions: {
                    pie: {
                        shadow: false,
                        center: ['50%', '50%']
                    }
                },
                tooltip: {
                    valueSuffix: '%'
                },
                series: [{
                    name: 'Browsers',
                    data: browserData,
                    size: '60%',
                    dataLabels: {
                        formatter: function () {
                            return this.y > 5 ? this.point.name : null;
                        },
                        color: '#ffffff',
                        distance: -30
                    }
                }, {
                    name: 'Versions',
                    data: versionsData,
                    size: '80%',
                    innerSize: '60%',
                    dataLabels: {
                        formatter: function () {
                            // display only if larger than 1
                            return this.y > 1 ? '<b>' + this.point.name + ':</b> ' + this.y + '%' : null;
                        }
                    }
                }]
            });
        }
    });
});